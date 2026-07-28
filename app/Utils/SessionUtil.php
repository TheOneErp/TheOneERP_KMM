<?php

namespace App\Utils;

use Request;
use App\Utils\TranslationUtil;
use App\Models\Translation;

class SessionUtil
{
    static public function getLanguageID()
    {
        $languageID = Request::session()->get("language_id");
        if ($languageID == null) {
            $languageID = TranslationUtil::getDefaultLanguageID();
            Request::session()->put("language_id",$languageID);
        }
        return $languageID;
    }
    static public function getUserID()
    {
        return Request::user()->user_id;
    }
    static public function getUsername()
    {
        return Request::user()->username;
    }

    static public function saveUserData($user,$languageID)
    {
        Request::session()->put("username",$user->username);
        Request::session()->put("user_name",$user->name);
        Request::session()->put("user_id",$user->user_id);
        Request::session()->put("language_id" , $languageID);
    }
}
