<?php
namespace App\Http\Controllers\System ;

use App\Utils\UserUtil;
use App\Utils\PermissionUtil;

use App\Models\Page;
use App\Models\Field;
use App\Models\Translation;

use App\Http\Controllers\Controller;

Class SystemController extends Controller{
    public static function showList($page_id){
        $page_setting = Page::find((int) $page_id);
        $form_id = $page_setting->forms()->get()->pluck('form_id')->first();
        $fields = Field::getShowlist((int)$form_id)->get();
        $field_name = $fields->pluck('field_code')->toArray();
        // array_push($field_name,$newButton);

        $allOfTranslation = Translation::all();
        $defaultFieldLan = $allOfTranslation->whereIn('translation_code',$field_name)->where('language_id', 1);
        $transFieldLan = $allOfTranslation->whereIn('translation_code',$field_name)->where('language_id',session('language_id'));

        $languages = [];
        foreach ($defaultFieldLan as $item){
            $translation = $transFieldLan->where("translation_code",$item->translation_code)->pluck('translation')->first();
            $languages[$item->translation_code] = is_null($translation) ? $item->translation : $translation;
        }
        $languages["page_name"] = $allOfTranslation->where('language_id',session('language_id'))->where('translation_code',$page_setting->page_code)->pluck('translation')->first();
        if(is_null($languages["page_name"])){
            $languages["page_name"] = $allOfTranslation->where('language_id',1)->where('translation_code',$page_setting->page_code)->pluck('translation')->first();
        }

        // $user = User::orderBy('user_id','asc')->paginate(10);
        $list = $page_setting->page_list_template;
        $result = [
            "list" => $list,
            "languages" => $languages,
            "fields" => $fields
        ];
		return $result;
    }

    /**
     * @param int           $page_id  欲驗證權限之頁面ID
     * @param string|array  $permission_type    欲驗證之特定權限類型，null則不限定
     * @param bool          $abortWhenDenied    驗證不通過時是否直接abort
     *
     * @return bool|abort(403)
     */
    public static function systemAuth($page_id, $permission_type = null, $abortWhenDenied = true){
        $pageData = Page::find($page_id);
        $module = explode("_",$pageData->page_code)[0];
        $pass = false;
        $comparePermission = function($current, $all) use (&$pass){
            $parsedPermissionType = [
                "view" => "read",
                "write" => "insert",
                "add" => "insert",
                "rewrite" => "update",
                "edit" => "update",
                "remove" => "delete"
            ];
            if(isset($parsedPermissionType[$current])) $current = $parsedPermissionType[$current];
            $current = "permission_$current";
            if(isset($all[$current]) && $all[$current] === true) $pass = true;
        };

        if(UserUtil::isAdmin()){
            if($module === "DT"){
                $pass = UserUtil::isRoot() ? true : false;
            }else{
                $pass = true;
            }
        }else{
            $permission = PermissionUtil::getCurrentUserPagePermission($page_id);
            if(is_null($permission_type)){
                foreach($permission as $t => $p){
                    if($p===true && $t!='permission_allow_rw_all') $pass = true;
                }
            }else{
                if(is_array($permission_type)){
                    foreach($permission_type as $p){
                        if(!$pass) $comparePermission($p, $permission);
                    }
                }else if(is_string($permission_type)){
                    $comparePermission($permission_type, $permission);
                }
            }
        }
        if($pass){
            return $pass;
        }else if($abortWhenDenied){
            abort(403);
        }else{
            return $pass;
        }
    }
}
