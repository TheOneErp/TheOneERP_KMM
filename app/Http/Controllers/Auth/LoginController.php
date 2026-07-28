<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\Language;
use App\Models\Translation;
use App\Utils\SessionUtil;
use Illuminate\Http\Request;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = "/";

    public function username()
    {
        return 'username';
    }

    protected function authenticated($request, $user)
    {
        $languageID = $request->input('languages','1');
        if($user->user_disabled == '0'){
            SessionUtil::saveUserData($user,$languageID);
        }else{
            $message = Translation::where('language_id', $languageID)
                                    ->where('translation_type', 'message')
                                    ->where('translation_code', 'user_is_disabled')
                                    ->pluck('translation')->first();
            if(is_null($message)){
                $message = Translation::where('language_id', 1)
                                        ->where('translation_type', 'message')
                                        ->where('translation_code', 'user_is_disabled')
                                        ->pluck('translation')->first();
            }
            $this->logout($request);
            return back()->withInput(['languages' => $languageID])->withErrors([$message]);
        }
    }

    public function getLoginTranslation(){
        $languages = Language::all()->toArray();
        $translation = Translation::where('translation_type','field')
                                    ->whereIn('translation_code',["username","password","language","login","rememberme"])
                                    ->get();
        $loginLanguages = [];
        foreach($languages as $lan){
            $loginLanguages[$lan["language_id"]] = [];
            foreach($translation->where("language_id", $lan["language_id"]) as $text){
                $loginLanguages[$lan["language_id"]][$text->translation_code] = $text->translation;
            }
        }
        $data = [
            "languages" => $languages,
            "translation" => $loginLanguages
        ];
        return response()->json($data,200);
    }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

}
