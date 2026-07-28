<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

use App\Models\Translation;

use App\Utils\PermissionUtil;
use App\Utils\DataUtil;
use App\Utils\SessionUtil;
use App\Utils\TranslationUtil;


class ShareUserData
{
    public function handle($request, Closure $next, $guard = null)
    {
        // User data
        if (Auth::check()) {
            $user = Auth::user();
            $userID = $user->user_id;

            // User data
            if (Cache::store('file')->has("userData_" . $userID)) {
                $userData = Cache::store('file')->get("userData_" . $userID);
            } else {
                $groups = $user->groups();
                $userLanguageID = SessionUtil::getLanguageID();

                $userData = [
                    'language_id' => $userLanguageID,
                    'groups' => $groups->toArray(),
                ];
                Cache::store('file')->put("userData_" . $userID, $userData, now()->addMinutes(30));
            }
            $userData['user'] = $user;

            // User permission
            if (Cache::store('file')->has("user_permission_" . $userID)) {
                $userPermissions = Cache::store('file')->get("user_permission_" . $userID);
            }else{
                $userPermissions = PermissionUtil::fetchUserPermissionFromDatabase($userID);
                Cache::store('file')->put("user_permission_" . $userID, $userPermissions, now()->addMinutes(30));
            }

            app()->singleton('userdata', function () use ($userData) {
                return $userData;
            });
            app()->singleton('user_permission', function () use ($userPermissions) {
                return $userPermissions;
            });

            gc_collect_cycles();
        }

        $languageID = isset($userLanguageID) ? $userLanguageID : TranslationUtil::getDefaultLanguageID();

        if (Cache::has("commonTranslations_" . $languageID)) {
            $commonTranslations = Cache::get("commonTranslations_" . $languageID);
        } else {
            $messageTranslations = DataUtil::convertToArray(Translation::where("translation_type", "message")->get()->toArray());
            $messageCodes = [];
            $commonTranslations = [];

            foreach ($messageTranslations as $message) {
                $messageCode = $message['translation_code'];
                if (!in_array($messageCode, $messageCodes)) $messageCodes[] = $messageCode;
            }
            foreach ($messageCodes as $messageCode) {
                $commonTranslations[$messageCode] = TranslationUtil::getTranslationInArray($messageTranslations, $messageCode, $languageID);
            }
            Cache::forever("commonTranslations_" . $languageID, $commonTranslations);
        }

        $validationTranslations = TranslationUtil::getValidationMessage($languageID);

        View::share("commonTranslations", $commonTranslations);

        app()->singleton('commonTranslations', function () use ($commonTranslations) {
            return $commonTranslations;
        });

        app()->singleton('validationTranslations', function () use ($validationTranslations) {
            return $validationTranslations;
        });

        return $next($request);
    }
}
