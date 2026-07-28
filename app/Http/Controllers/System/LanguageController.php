<?php
namespace App\Http\Controllers\System;

use App\Models\Language;
use App\Models\Translation;

use App\Http\Controllers\Controller;
class LanguageController extends Controller{

    public static function getAllLanguages(bool $toJson = false){
        // dd($toJson);
        $data = $toJson ? Language::all()->toJson() : Language::all();
        return $data;
    }
    public static function getAllTranslation(bool $toJson = false){
        $data = $toJson ? Translation::all()->toJson() : Translation::all();
        return $data;
    }

}
