<?php

namespace App\Utils;

use App\Models\Page;
use App\Models\Language;
use App\Models\Parameter;
use App\Models\Translation;

use App\Utils\SessionUtil;
use App\Utils\PermissionUtil;
use Illuminate\Support\Facades\Cache;

class TranslationUtil
{
    static public function getAllLanguages()
    {
        $allLanguages = Language::all();
        return $allLanguages;
    }

    static public function getDefaultLanguageID(){
        return resolve('default')['language_id'];
    }

    static public function getLanguage($languageID)
    {
        return Language::find($languageID);
    }

    static public function getLanguageByCode($languageCode)
    {
        return Language::where('language_code', $languageCode)->first();
    }

    static public function getTranslationByCode($translationCode, $languageID = null, $formID = null)
    {
        $languageID = $languageID == null ? (int) SessionUtil::getLanguageID() : $languageID;
        if (is_array($translationCode)) {
            $tmp = [];
            $translationArray = Translation::whereIn('translation_code', $translationCode)->get()->toArray();
            foreach ($translationCode as $code) {
                $tmp[$code] = TranslationUtil::getTranslationInArray($translationArray, $code, $languageID, $formID);
            }
            return $tmp;
        } else {
            $translationArray = Translation::where('translation_code', $translationCode)->get()->toArray();
            return TranslationUtil::getTranslationInArray($translationArray, $translationCode, $languageID, $formID);
        }
    }

    static public function getMessageTranslation($messageCode, $languageID = null)
    {
        $languageID = $languageID == null ? (int) SessionUtil::getLanguageID() : $languageID;
        $translation = Translation::where("language_code", $messageCode)->where('translation_type', 'message')->get()->toArray();
        return TranslationUtil::getTranslationInArray($translation, $messageCode, $languageID);
    }

    static public function getPageDataWithTranslationByPageCode($pageCode)
    {
        $page = Page::where("page_code", $pageCode)->first();
        if ($page != null) {
            return TranslationUtil::getPageDataWithTranslation($page->page_id);
        } else {
            return null;
        }
    }

    static public function getPageDataWithTranslation($pageID, $languageID = null)
    {
        $languageID = $languageID == null ? (int) SessionUtil::getLanguageID() : $languageID;

        $returnTranslation = [
            "page" => null,
            "path" => null,
            "forms" => [],
            "validation" => []
        ];

        $page = Page::find($pageID);
        if ($page == null) return null;

        $getPath = function ($currentPage,&$currentPath) use (&$getPath){
            $module = $currentPage->module()->first();
            if($module != null){
                $currentPath = $module->page_code . '\\' . $currentPath;
                return $getPath($module,$currentPath);
            }else{
                return $currentPath;
            }
        };
        $pageCode = $page->page_code;
        $returnTranslation['path'] = $getPath($page,$pageCode);

        $returnTranslation["page"] = $page->toArray();
        $returnTranslation["page"]["translation"] = TranslationUtil::getTranslationInArray($page->translation->toArray(), $page->page_code, $languageID);
        $returnTranslation["page"]["permission"] = PermissionUtil::getCurrentUserPagePermission($pageID);

        $returnTranslation['validation'] = TranslationUtil::getValidationMessage($languageID);
        $returnTranslation['validation']['attributes'] = [];

        foreach ($page->forms as $form) {
            $formArray = $form->toArray();
            $formArray['fields'] = [];
            $formArray['attributes'] = [];

            $fields = $form['fields']->toArray();
            usort($fields,function($a,$b){return $a['field_order'] - $b['field_order'];});

            $fieldCodes = array_map(function ($item) {
                return $item['field_code'];
            }, $fields);
            $translation = Translation::where('translation_type','field')->whereIn("translation_code", $fieldCodes)->get()->toArray();
            foreach ($fields as $field) {
                $fieldCode = $field["field_code"];
                $fieldTranslation = TranslationUtil::getTranslationInArray($translation, $fieldCode, $languageID, $form['form_id']);
                $field['translation'] = $fieldTranslation;
                $returnTranslation['validation']['attributes'][$fieldCode] = $fieldTranslation;
                $formArray['attributes'][$fieldCode] = $fieldTranslation;

                if($field['field_type'] == 'reference_page' && $field['field_options']['reference_page']['page_id'] != $pageID){
                    $field['pageData'] = TranslationUtil::getPageDataWithTranslation($field['field_options']['reference_page']['page_id']);
                }

                $formArray["fields"][$fieldCode] = $field;
            }
            $returnTranslation["forms"][] = $formArray;
        }

        return ($returnTranslation);
    }

    static public function getAllPageDataWithTranslation($returnArray = true, $languageID = null){
        $languageID = is_null($languageID) ? (int) SessionUtil::getLanguageID() : $languageID;
        $defaultLanguage = (int) Parameter::where('parameter_code','default_language')->get()[0]->parameter_value;
        $allPages = PageUtil::getAllPageData(false);
        $allTranslations = collect([]);
        $result = collect([]);

        foreach(Translation::all() as $translation){
            $tempTranslationData = [];
            if(in_array($translation->translation_type,["page","field"])){
                foreach($translation->toArray() as $key => $value) {
                    $tempTranslationData[$key] = $value;
                }
                $allTranslations->push($tempTranslationData);
            }
        }

        $findTranslation = function ($code, $type, $form_id = null) use ($allTranslations, $languageID, $defaultLanguage) {
            $result = null;
            $toFindLanguages = [$languageID,$defaultLanguage];
            $i = 0;
            do {
                $trans = $allTranslations
                    ->where('language_id',$toFindLanguages[$i])
                    ->where('translation_type',$type)
                    ->where('translation_code',$code)
                    ->where('form_id',$form_id);
                if($trans->isNotEmpty()){
                    $result = $trans->pluck('translation')->first();
                }
                $i++;
                if($i >= sizeof($toFindLanguages)-1 && !is_null($form_id)){
                    $i = 0;
                    $form_id = null;
                }
            } while (is_null($result) && $i <= sizeof($toFindLanguages) - 1);
            if(is_null($result)) $result = $code;

            return $result;
        };

        foreach($allPages as $page){
            // dd($page);
            $page["translation"] = $findTranslation($page["page_code"],'page');
            foreach($page["forms"] as &$form){
                foreach($form["fields"] as &$field){
                    $field["translation"] = $findTranslation($field["field_code"],"field",$form["form_id"]);
                }
            }
            $result->push($page);
        }

        $result = $returnArray ? $result->toArray() : $result;
        // dd($result);
        return $result;
    }

    static public function getValidationMessage($languageID = null)
    { // TODO : 或許需要處理一下效能問題 (Unique code foreach)
        if(Cache::has('validationMessage_'.$languageID)){
            return Cache::get('validationMessage_'.$languageID);
        }
        $validationArray = [];
        $languageID = $languageID == null ? (int) SessionUtil::getLanguageID() : $languageID;
        $translation = Translation::where('translation_type', 'rule')->get()->toArray();
        foreach ($translation as $item) {
            $validationCode = $item["translation_code"];
            if (strpos($item["translation_code"], '.') !== false) {
                $validationType = explode(".", $item["translation_code"])[0];
                $validationCode = explode(".", $item["translation_code"])[1];
                if (!isset($validationArray[$validationType])) {
                    $validationArray[$validationType] = [];
                } else if (!is_array($validationArray[$validationType])) {
                    continue;
                }
                $validationArray[$validationType][$validationCode] = TranslationUtil::getTranslationInArray($translation, $item["translation_code"], $languageID);
            } else {
                $validationArray[$validationCode] = TranslationUtil::getTranslationInArray($translation, $validationCode, $languageID);
            }
        }
        Cache::forever('validationMessage_'.$languageID,$validationArray);
        return ($validationArray);
    }

    static public function getTranslationInArray($translationArray, $translationCode, $languageID = null, $formID = null)
    {
        $defaultLanguage = TranslationUtil::getDefaultLanguageID();

        $getTranslationByDefaultLanguage = function ($translationArray) use ($defaultLanguage) {
            $data = array_filter($translationArray, function ($item) use ($defaultLanguage) {
                return $item['language_id'] == $defaultLanguage;
            });
            if (count($data) > 0) return array_pop($data);
            return null;
        };

        $codeArray = array_filter($translationArray, function ($item) use ($translationCode) { // Filter by translation code
            return $item['translation_code'] == $translationCode;
        });
        if (count($codeArray) == 0) return $translationCode; // If not found , return code .

        $formSpecifiedArray = array_filter($codeArray, function ($item) use ($formID) { // Filter by form id
            return $item['form_id'] == $formID;
        });

        if (count($formSpecifiedArray) == 0) {
            $languageArray = array_filter($codeArray, function ($item) use ($languageID) { // find specific
                return $item['language_id'] == $languageID && $item['form_id'] == null;
            });
            if (count($languageArray) == 0)
                if($getTranslationByDefaultLanguage($codeArray) != null)
                    return $getTranslationByDefaultLanguage($codeArray)['translation']; // if can't found specified language return translation with default language
                else
                    return array_pop($codeArray)['translation'];
            return array_pop($languageArray)['translation']; // found owo
        } else {
            $languageArray = array_filter($formSpecifiedArray, function ($item) use ($languageID) { // Filter by language code
                return $item['language_id'] == $languageID;
            });
            if (count($languageArray) == 0)
                if ($getTranslationByDefaultLanguage($formSpecifiedArray) != null)
                    return $getTranslationByDefaultLanguage($formSpecifiedArray)['translation']; // if not found specified language id , return defualt translation with default language and specified form id
                else if($getTranslationByDefaultLanguage($codeArray) != null)
                    return $getTranslationByDefaultLanguage($codeArray)['translation'];
                else
                    return array_pop($codeArray)['translation'];
            return array_pop($languageArray)['translation']; // found owo
        }
    }

    static public function translationExists($translationCode, $languageId = null, $form_id = null){
        $translation = Translation::where('translation_code', $translationCode);
        if(!is_null($languageId)) $translation->where('language_id',$languageId);
        if(!is_null($form_id)) $translation->where('form_id',$form_id);
        return $translation->get()->isNotEmpty();
    }
}
