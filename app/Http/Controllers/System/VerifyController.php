<?php

namespace App\Http\Controllers\System;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Verify;
use App\Models\VerifyLevel;
use App\Models\VerifyCondition;

use App\Utils\LogUtil;
use App\Utils\UserUtil;
use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\VerifyUtil;
use App\Utils\SessionUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Http\Controllers\System\SystemController as System;

class VerifyController extends SystemController{

    protected $verifyId;
    protected $verifyLevelId;
    protected $verifyConditionId;
    public function __construct(){
        $this->verifyId = PageUtil::getPageIdByPageCode("SY_VERIFIES");
        $this->verifyLevelId = PageUtil::getPageIdByPageCode("SY_VERIFY_LEVEL");
        $this->verifyConditionId = PageUtil::getPageIdByPageCode("SY_VERIFY_CONDITION");
    }

    // for verify setting
    public function verifies_list()
    {
        if(System::systemAuth($this->verifyId)){
            $show = System::showList($this->verifyId);
            $page_data = TranslationUtil::getPageDataWithTranslation($this->verifyId);
            $translateFields = [
                "module","submodule","selecting","cancel","clear","yes","no","unsave_confirm",
                "save","edit_order","save_success","page_module.min"
            ];
            $translations = TranslationUtil::getTranslationByCode($translateFields);
            // dd($translations);
            foreach($translations as $key => $field){
                $show["languages"][$key] = $field;
            }
            $fields["page_name"] = [
                "translation" =>TranslationUtil::getTranslationByCode("page_name"),
                "field_type" => "string",
                "field_code" => "page_name",
                "field_show_on_list" => true,
            ];
            foreach($page_data["forms"][0]["fields"] as $code => $field){
                if($field["field_show_on_list"] == "1"){
                    $fields[$code] = $field;
                }
            }
            $pageData = PageUtil::getModules();
            foreach($pageData as $type => $pages){
                foreach($pages as $id => $data){
                    if(strpos($data["page_code"],"SY") !== false || strpos($data["page_code"],"DT") !== false) unset($pageData[$type][$id]);
                }
            }
            // dd($pageData);
            return view($page_data["page"]["page_list_template"])
            ->with("pageModule", $pageData)
            ->with("languages",$show["languages"])
            ->with("fields",$fields)
            ->with("PAGE_ID", $this->verifyId);
        }
    }
    public function verifies_form($type, $id)
    {
        if(array_search($type, ['view', 'insert', 'update']) === false){
            abort(404);
        }
        else if(System::systemAuth($this->verifyId, $type))
        {
            $verify_page_data = TranslationUtil::getPageDataWithTranslation($this->verifyId);
            $verify_level_data = TranslationUtil::getPageDataWithTranslation($this->verifyLevelId);
            $verify_condition_data = TranslationUtil::getPageDataWithTranslation($this->verifyConditionId);
            $edit_page_data_with_parents = PageUtil::getPageDataWithParentsByPageId($id);
            if(is_null($edit_page_data_with_parents)) abort(404);

            $verifyData = Verify::where("page_id",$id)->get();
            $verifyId = $verifyData->isEmpty() ? null : $verifyData[0]->verify_id;

            $fields = [
                "level_number","level","page",'loading','processing', "add", "remove",'accessing','redirecting','error.unknown','contact_maintenance','save',"group","user","condition","confirm","cancel","unsave_confirm","and","or","filter.operator.=","filter.operator.!=","filter.operator.>","filter.operator.<","filter.operator.>=","filter.operator.<=","filter.operator.like","filter.operator.not like"
            ];
            $languages = [];
            $edit_page_name = "";
            foreach($edit_page_data_with_parents as $p){
                $edit_page_name = DataUtil::unionString($edit_page_name,$p["page"]["translation"]," / ");
            }
            $languages["edit_page_name"] = $edit_page_name;
            $translations = TranslationUtil::getTranslationByCode($fields);
            foreach($translations as $key => $field){
                $languages[$key] = $field;
            }

            $languages["field.options"] = [];
            foreach(["filter.operator.=","filter.operator.!=","filter.operator.>","filter.operator.<","filter.operator.>=","filter.operator.<=","filter.operator.like","filter.operator.not like"] as $v){
                $key = explode(".", $v)[2];
                $languages["field.options"][$key] = $languages[$v];
                unset($languages[$v]);
            }
            $editPageIndex = count($edit_page_data_with_parents)-1;
            foreach($edit_page_data_with_parents[$editPageIndex]["forms"][0]["fields"] as $key => $field){
                $languages["field.options"][$field["field_code"]] = $field["translation"];
                $verify_condition_data["forms"][0]["fields"]["field_code"]["field_options"]["options"][] = $field["field_code"];
            }

            // dd($languages);
            // $modules = PageUtil::getModules(true);
            $data = null;
            if($type !== "insert"){
                $data = $this->get_origin_data($verifyId);
                if(is_null($data)){
                    return redirect()->route('verifies_form',['type'=>'insert','id'=>$id]);
                    // abort(404);
                }
            }
            // dd($languages,$verify_condition_data);
            return view($verify_page_data["page"]["page_form_template"])
            ->with("translations", $languages)
            ->with("verify_data", $verify_page_data)
            ->with("level_data", $verify_level_data)
            ->with("condition_data", $verify_condition_data)
            ->with("type", $type)
            ->with("data", $data)
            ->with("dataId", $verifyId)
            ->with("pageId", $id)
            ->with("PAGE_ID", $this->verifyId);
        }
    }
    public function get_origin_data($id)
    {
        $verifyData = Verify::find($id);
        // dd($verifyData->verifyLevel);
        if(is_null($verifyData)){
            return null;
        }else{
            $putDataToArray = function(&$array, $datas, $fields, $ignore = []){
                foreach($fields as $field){
                    $code = $field['field_code'];
                    if(array_key_exists($code, $datas) && array_search($code, $ignore) === false){
                        $array[$code] = $datas[$code];
                    }
                }
            };
            $level_data = TranslationUtil::getPageDataWithTranslation($this->verifyLevelId);
            $condition_data = TranslationUtil::getPageDataWithTranslation($this->verifyConditionId);
            $level = [];
            foreach($verifyData->verifyLevel as $targetIndex => $verifyLevel){
                $levelIndex = $verifyLevel->verify_level;
                // $verifyTarget = is_null($verifyLevel->verifyTarget) ? [] : $verifyLevel->verifyTarget->toArray();
                $verifyViewTable = "{$level_data["page"]["page_code"]}_{$level_data["forms"][0]["form_id"]}_verify_target_id";
                $verifyTarget = DB::table($verifyViewTable)
                                ->where("verify_target_id",$verifyLevel->verify_target_id)
                                ->where("verify_target_type",$verifyLevel->verify_target_type)
                                ->first();
                // dd($verifyTarget);

                $conditions = [];
                foreach($verifyLevel->verifyCondition as $verifyCondition){
                    $putDataToArray($conditions[],$verifyCondition->toArray(),$condition_data["forms"][0]["fields"],["verify_level"]);
                }

                $levelFields = array_merge(
                    $level_data["forms"][0]["fields"],
                    ["verify_target_name"=>["field_code" => "verify_target_name"]],
                    ["verify_population_max"=>["field_code" => "verify_population_max"]],
                    ["conditions"=>["field_code" => "conditions"]]
                );
                $levelDatas = array_merge(
                    $verifyLevel->toArray(),
                    (array) $verifyTarget,
                    ["conditions" => $conditions]
                );
                $putDataToArray($level[$levelIndex-1][],$levelDatas,$levelFields,["verify_level"]);
            }
            // dd($level);
            return ["level" => $level];
        }
    }
    public function verifies_save(Request $request, $type, $id)
    {
        DB::beginTransaction();
        $result = [
            "success" => true,
            "errors" => [],
        ];
        $addError = function ($message, $messagePrefix, $id, $type, $levelIndex, $targetIndex, $conditionIndex = null) use (&$result){
            $result["success"] = false;
            if (!is_array($message)){$message = [$message];}
            foreach ($message as $singleMessage){
                $temp = ['message' => $messagePrefix . ' ' . $singleMessage, 'id' => $id, 'type' => $type, 'levelIndex' => $levelIndex, 'targetIndex' => $targetIndex];
                if($type === "condition") $temp["conditionIndex"] = $conditionIndex;
                $result["errors"][] = $temp;
            }
        };

        $verify_data = TranslationUtil::getPageDataWithTranslation($this->verifyId);
        $level_data = TranslationUtil::getPageDataWithTranslation($this->verifyLevelId);
        $condition_data = TranslationUtil::getPageDataWithTranslation($this->verifyConditionId);

        $validation_message = $verify_data["validation"];

        $msgTranslations = TranslationUtil::getTranslationByCode([
            "of", "level_number", "row_with_number", "condition"
        ]);

        if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

        // dd($data);

        $verifyIsExists = Verify::where('page_id', $id)->get()->isNotEmpty();
        $verifyToInsert = ["updated_by" => SessionUtil::getUserID(), "verify_remarks" => null];
        if(!$verifyIsExists) $verifyToInsert["created_by"] = SessionUtil::getUserID();
        $VERIFY_DATA = Verify::updateOrCreate(
            ["page_id" => $id],
            $verifyToInsert
        );
        // dd($VERIFY_DATA->verify_id);
        VerifyLevel::where("verify_id", $VERIFY_DATA->verify_id)->delete();
        $LEVEL_DATA = [];
        $CONDITION_DATA = [];
        foreach($data["level"] as $levelIndex => $levels){
            $LEVEL_DATA[] = [];
            $CONDITION_DATA[] = [];
            foreach($levels as $targetIndex => $target){
                $levelToInsert = [
                    "verify_id" => $VERIFY_DATA->verify_id,
                    "verify_level" => $levelIndex+1,
                    "updated_by" => SessionUtil::getUserID(),
                    "created_by" => SessionUtil::getUserID()
                ];
                $levelToValidate = [];
                $level_rules = [];
                $level_attributes = [];
                foreach($level_data["forms"][0]["fields"] as $field_code => $field){
                    if($field_code != "verify_level"){
                        $levelToInsert[$field_code] = $target[$field_code];
                    }
                    $levelToValidate[$field_code] = $levelToInsert[$field_code];
                    $target["data"]["verify_id"] = $VERIFY_DATA->verify_id;
                    $level_rules[$field_code] = ValidationUtil::generateFieldRuleObject($field['field_rule'], $field, $target, [
                        'dataKey' => "verify_id",
                        'update' => $type == 'update',
                        'required' => $field['field_required']
                    ]);
                    $level_attributes[$field_code] = $field['translation'];
                }
                // dd($level_rules,$levelToValidate);
                $levelValidation = ValidationUtil::validationData($levelToValidate, $level_rules, $validation_message, $level_attributes);
                $levelNumber = str_replace(":number",$levelIndex+1,$msgTranslations["level_number"]);
                $levelRow = $levelNumber.' '.str_replace(":row",$targetIndex+1,$msgTranslations["row_with_number"]);
                if(!$levelValidation["passed"]){
                    foreach($levelValidation["validator"]->errors()->toArray() as $errorField => $messages){
                        $errorId = "{$errorField}.{$levelIndex}-{$targetIndex}";
                        foreach($messages as $msg){
                            $addError($msg, $levelRow, $errorId, 'target', $levelIndex, $targetIndex);
                        }
                    }
                    $LEVEL_DATA[$levelIndex][] = (object) ["verify_level_id" => 0];
                }else{
                    $LEVEL_DATA[$levelIndex][] = VerifyLevel::create($levelToInsert);
                }

                $CONDITION_DATA[$levelIndex][$targetIndex] = [];
                foreach($target["conditions"] as $conditionIndex => $condition){
                    $conditionToInsert = [
                        "verify_level_id" => $LEVEL_DATA[$levelIndex][$targetIndex]->verify_level_id,
                        "updated_by" => SessionUtil::getUserID(),
                        "created_by" => SessionUtil::getUserID()
                    ];
                    $condition_rules = [];
                    $condition_attributes = [];
                    foreach($condition_data["forms"][0]["fields"] as $field_code => $field){
                        $conditionToInsert[$field_code] = $condition[$field_code];
                        $condition["data"]["verify_level_id"] = $LEVEL_DATA[$levelIndex][$targetIndex]->verify_level_id;
                        $condition_rules[$field_code] = ValidationUtil::generateFieldRuleObject($field['field_rule'], $field, $condition, [
                            'dataKey' => "verify_level_id",
                            'update' => $type == 'update',
                            'required' => $field['field_required']
                        ]);
                        $condition_attributes[$field_code] = $field['translation'];
                    }
                    // dd($conditionToInsert,$condition_rules);
                    $conditionValidation = ValidationUtil::validationData($conditionToInsert, $condition_rules, $validation_message, $condition_attributes);
                    if(!$conditionValidation["passed"]){
                        $conditionOf = str_replace(":a",$levelRow,str_replace(":b",$msgTranslations["condition"],$msgTranslations["of"]));
                        $conditionRow = str_replace(":row",$conditionIndex+1,$msgTranslations["row_with_number"]);
                        // dd($conditionOf,$conditionRow);
                        foreach($conditionValidation["validator"]->errors()->toArray() as $errorField => $messages){
                            $errorId = "$errorField.$conditionIndex";
                            foreach($messages as $msg){
                                $addError($msg, $conditionRow, $errorId, 'condition', $levelIndex, $targetIndex, $conditionIndex);
                            }
                        }
                    }else{
                        $CONDITION_DATA[$levelIndex][$targetIndex][] = VerifyCondition::create($conditionToInsert);
                    }
                }
            }
        }
        // dd($LEVEL_DATA,$CONDITION_DATA);
        if($result["success"]){
            VerifyUtil::resetDataVerify($id);
            DB::commit();
            return response()->json($result,200);
        }else{
            DB::rollBack();
            return response()->json($result,400);
        }
    }
    public function verifies_delete($id){
        $result = [
            "status" => true,
            "message" => null
        ];
        if(System::systemAuth($this->verifyId, "delete", false)){
            DB::beginTransaction();
            $verify_data = Verify::where("page_id",$id)->get();
            if($verify_data->isNotEmpty()){
                foreach($verify_data as $model){
                    $model->delete();
                }
            }else{
                $result["status"] = false;
                $result["message"] = TranslationUtil::getTranslationByCode("verify.error.delete_null");
            }

            if($result["status"]){
                VerifyUtil::resetDataVerify($id);
                DB::commit();
            }else{
                DB::rollBack();
            }

            return response()->json($result, $result["status"] ? 200 : 400);
        }else{
            $result["status"] = false;
            $result["message"] = TranslationUtil::getTranslationByCode("error.check_permission");
            return response()->json($result, 403);
        }
    }

    // for verification of data
    public function dataVerify(String $type, int $pageId, int $dataId, $userId = null){
        DB::beginTransaction();
        $userId = (int) (is_null($userId) ? SessionUtil::getUserID() : $userId);
        $tableNames = PageUtil::getTableNameByPageId($pageId);
        $result = [
            "success" => true,
            "messages" => [],
        ];
        $errorMsgCode = [
            "verify.error.level", "verify.error.had_verified", "verify.error.highest", "verify.error.no_permission", "error.unknown", "error.data_wrong", "contact_maintenance"
        ];
        $errorMessages = TranslationUtil::getTranslationByCode($errorMsgCode);
        $callVerify = "{$type}DataVerify";

        if(isset($tableNames[0]) && VerifyUtil::pageVerifyConfirmation($pageId)){
            VerifyUtil::$callVerify($pageId, $dataId, $userId, $tableNames[0], $errorMessages, $result);
        }else{
            $result["success"] = false;
            $result["messages"][] = $errorMessages["error.unknown"].$errorMessages["contact_maintenance"];
        }

        // dd($result);
        $dbCall = $result["success"] ? "commit" : "rollback";
        $status = $result["success"] ? 200 : 400;
        DB::{$dbCall}();
        // dd($result,$dataOptions);
        return response()->json($result, $status);
    }
}
