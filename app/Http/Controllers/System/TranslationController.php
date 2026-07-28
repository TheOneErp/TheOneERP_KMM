<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Controllers\System\SystemController as System;

use App\Models\Language;
use App\Models\Translation;

use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\ValidationUtil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TranslationController extends Controller
{
    protected $pageId;
    public function __construct(){
        $this->pageId = PageUtil::getPageIdByPageCode("SY_TRANSLATION");
    }
    public function list(){
        if(System::systemAuth($this->pageId)){
            $languages = Language::all()->toArray();
            return view('system.list.translation')
            ->with("languages", $languages);
        }
    }

    public function query(Request $request)
    {
        $query = Translation::where("form_id", null)->whereIn("translation_type", ['message', 'rule', 'var']);
        if (isset($request->filter)) {
            $filter = json_decode($request->filter);
            if (!empty($filter->type)) {
                $query->where('translation_type', $filter->type);
            }
            if (!empty($filter->code)) {
                $query->where('translation_code', 'like', '%' . $filter->code . '%');
            }
        }
        return $query->get()->toArray();
    }

    public function save(Request $request)
    {
        if(System::systemAuth($this->pageId, ["insert","update"], false)){
            DB::beginTransaction();

            if (ValidationUtil::isJSONString($request->getContent()))
                $data = DataUtil::convertToArray(json_decode($request->getContent()));
            else
                abort(400);

            $updatedLanguage = [];

            $addTranslation = function ($data) {
                DB::table('translation')->insert([
                    'language_id' => $data['language_id'],
                    'translation_type' => $data['translation_type'],
                    'translation_code' => $data['translation_code'],
                    'translation' => $data['translation']
                ]);
            };
            $updateTranslation = function ($id, $data) {
                DB::table('translation')->where('translation_id', $id)->update([
                    'translation' => $data['translation']
                ]);
            };
            $getTranlsationData = function ($data) {
                return ([
                    'language_id' => $data['language_id'],
                    'translation_type' => $data['translation_type'],
                    'translation_code' => $data['translation_code'],
                    'translation' => $data['translation']
                ]);
            };

            foreach ($data as $translation) {
                if (!in_array($translation['language_id'], $updatedLanguage)) $updatedLanguage[] = $translation['language_id'];
                if ($translation['status'] == 'add') {
                    $translationInDatabase = DataUtil::convertToArray(DB::table('translation')
                        ->where('translation_code', $translation['translation_code'])
                        ->where('translation_type', $translation['translation_type'])
                        ->where('language_id', $translation['language_id'])
                        ->get()->toArray());
                    if (count($translationInDatabase) > 0) {
                        $updateTranslation($translationInDatabase[0]['translation_id'], $getTranlsationData($translation));
                    } else {
                        $addTranslation($translation);
                    }
                }
                if ($translation['status'] == 'update') {
                    $updateTranslation($translation['id'], $getTranlsationData($translation));
                }
            }

            DB::commit();

            foreach ($updatedLanguage as $language) {
                Cache::forget('commonTranslations_' . $language);
            }

            return response()->json([
                'status' => true
            ], 200);
        }else{
            return response()->json([
                'status' => false
            ], 403);
        }

    }
}
