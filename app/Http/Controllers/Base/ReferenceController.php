<?php

namespace App\Http\Controllers\Base;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Controller;

use Carbon\Carbon;

use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\VerifyUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;

class ReferenceController extends Controller
{
    protected function getReferenceData(Request $request, $field_id){
        $data = [];
        if (isset($request->filters) && ValidationUtil::isJSONString($request->filters)) {
            $data = json_decode($request->filters);
        }
        // dd($data);
        /**
         * 解析table name
         * @param string    $tableName => 資料庫帶回的reference設定之table_name
         *
         * @return array
         */
        function parseTableName(String $tableName){
            $nameSplit = explode("_", $tableName);
            $r = [
                "page_code" => $nameSplit[0],
                "table_name" => $tableName,
                "form_id" => $nameSplit[1]
            ];
            $r["page_code"] = $nameSplit[0];
            if($r["page_code"] === "SY"){
                $r["page_code"] = "";
                $tableName = "";
                for($i = 0; $i < sizeof($nameSplit)-1; $i++){
                    $r["page_code"] = DataUtil::unionString($r["page_code"], $nameSplit[$i], "_");
                    if($i > 0){
                        $tableName = DataUtil::unionString($tableName, $nameSplit[$i], "_");
                    }
                }
                $r['table_name'] = strtolower($tableName);
                $r['form_id'] = $nameSplit[sizeof($nameSplit)-1];
            }
            return $r;
        }
        function referenceError($result, $error_type){
            $errorPrefix = "reference.error.";
            $msg = TranslationUtil::getTranslationByCode($errorPrefix.$error_type);
            if($error_type !== "no_front") $msg .= TranslationUtil::getTranslationByCode("contact_maintenance");
            array_push($result["errors"],[
                "type" => $error_type,
                "message" => $msg
            ]);
            $result["status"] = false;
            return response()->json($result, 400);
        }
        $fieldData = Field::find($field_id);
        $formData = Form::find($fieldData->form_id);
        $pageData = Page::find($formData->page_id);
        $fieldOptions = $fieldData->field_options;
        $referenceSetting = $fieldOptions['reference'];
        // dd($referenceSetting);
        $result = [
            "status" => true,
            "datas" => [],
            "errors" => [],
            "fields" => [],
        ];

        $q = DB::table("");
        // 整理表單，串join
        $pageDatas = [];
        $nativeSqlEnabled = $referenceSetting["sql"]["native"]["enabled"];
        if($nativeSqlEnabled){
            try {
                $q = DB::table("{$pageData->page_code}_{$fieldData["form_id"]}_{$fieldData->field_code}");
                $q->get();
            } catch (\Throwable $th) {
                if($this->DEBUG_MODE){
                    dd($th, $q->toSql());
                }
                return referenceError($result, "no_view");
            }
        }else{
            try {
                $formCount = 0;
                $joinForm = function($q, $nameParsed, $pageDatas) use (&$referenceSetting, &$joinForm) {
                    $page = $pageDatas[$nameParsed["page_code"]];
                    $form = $page["forms"][array_search($nameParsed["form_id"],array_column($page["forms"],'form_id'))];
                    $formParent = $form["form_parent"];
                    $parentTable = "{$nameParsed["page_code"]}_{$formParent}";
                    if(!is_null($formParent)){
                        if(array_search($parentTable,array_column($referenceSetting["tables"],"table_name")) === false){
                            array_push($referenceSetting["tables"],$parentTable);
                            $parentName = parseTableName($parentTable);
                            $q = $joinForm($q, $parentName, $pageDatas);
                        }
                    }
                    $q->leftjoin($nameParsed["table_name"],"{$nameParsed["table_name"]}.parent_id","=","$parentTable.id");
                    return $q;
                };
                $allTables = $referenceSetting["tables"];
                foreach($allTables as $table){
                    $nameParsed = parseTableName($table["table_name"]);
                    // dd($nameParsed);
                    if(!isset($pageDatas[$nameParsed['page_code']])){
                        $pageDatas[$nameParsed['page_code']] = TranslationUtil::getPageDataWithTranslationByPageCode($nameParsed['page_code']);
                        if($formCount === 0){
                            $tn = $nameParsed["table_name"];
                            $q = DB::table($tn);
                        }else{
                            $join = $table["join"];
                            $q->leftjoin($nameParsed["table_name"],"{$nameParsed["table_name"]}.{$join["left_column"]}",$join["comparison_operator"],"{$join["right_table"]}.{$join["right_column"]}");
                        }
                    }else{
                        $q = $joinForm($q, $nameParsed, $pageDatas);
                    }
                    $formCount++;
                }
                $q->get();
            } catch (\Throwable $th) {
                if($this->DEBUG_MODE){
                    dd($th, $q->toSql());
                }
                return referenceError($result, "join_error");
            }
        }

        // 整理欄位
        try {
            $allTranslations = TranslationUtil::getTranslationByCode(array_column($referenceSetting["fields"],"field_code"));
            foreach ($referenceSetting["fields"] as $field){
                if(!$nativeSqlEnabled){
                    $nameParsed = parseTableName($field["table_name"]);
                    if(!isset($pageDatas[$nameParsed['page_code']])){
                        $pageDatas[$nameParsed['page_code']] = PageUtil::getPageDataByPageCode($nameParsed['page_code']);
                    }
                    $page = $pageDatas[$nameParsed["page_code"]];
                    $form = $page["forms"][array_search($nameParsed["form_id"],array_column($page["forms"],'form_id'))];
                    $fieldData = $form["fields"][$field["field_code"]];
                    $q->addSelect("{$nameParsed["table_name"]}.{$field["field_code"]}");
                    /* hidden root */
                    if($nameParsed["table_name"] == "users"){
                        $q->where("username","<>","root");
                    }
                }else{
                    $q->addSelect($field["field_code"]);
                }
                $result["fields"][$field["field_code"]] = [
                    "translation" => $allTranslations[$field["field_code"]],
                    "show" => $field["show"],
                    "target" => $field["target"],
                ];
            }
            $q->get();
        } catch (\Throwable $th) {
            if($this->DEBUG_MODE){
                dd($th, $q->toSql());
            }
            return referenceError($result, "field_error");
        }

        /* 整理篩選資料格式 */
        $filter = [];
        if(!empty($data)){
            foreach($data as $expression){
                $expression = (Array) $expression;
                if(!$nativeSqlEnabled){
                    $fieldIndex = array_search($expression["field"],array_column($referenceSetting["fields"],"field_code"));
                    $tableName = parseTableName($referenceSetting["fields"][$fieldIndex]["table_name"])["table_name"];
                    if($fieldIndex !== false){
                        $expression["field"] = $nativeSqlEnabled ? $referenceSetting["fields"][$fieldIndex]["field_code"] : "{$tableName}.{$expression["field"]}";
                    }
                }
                array_push($filter, [
                    "group" => $expression["group"],
                    "logical_operator" => $expression["condition"],
                    "field_code" => $expression["field"],
                    "comparison_operator" => $expression["operator"],
                    "value" => $expression["value"]
                ]);
            }
        }

        /* 串一般Where */
        if(!$nativeSqlEnabled){
            try {
                $expressions = [];
                foreach($referenceSetting["sql"]["expression"]["where"] as $expression){
                    if(!empty($expression["column"])){
                        $t = explode(".",$expression["column"])[0];
                        $c = explode(".",$expression["column"])[1];
                        $nameParsed = parseTableName($t);
                        array_push($expressions,[
                            "group" => $expression["group"],
                            "logical_operator" => $expression["logical_operator"],
                            "field_code" => "{$nameParsed['table_name']}.{$c}",
                            "comparison_operator" => $expression["comparison_operator"],
                            "value" => $expression["operand"]
                        ]);
                    }
                }
                // dump($expressions);
                $q->where(function($q) use ($expressions){
                    $q = DatabaseUtil::groupExpression($q, $expressions);
                });
                $q->get();
            } catch (\Throwable $th) {
                if($this->DEBUG_MODE){
                    dd($th, $q->toSql());
                }
                return referenceError($result, "where_error");
            }
        }

        /* 串前置篩選(front_field) */
        if($referenceSetting["front_field"]["enabled"]){
            $filterIndex = array_search(null,array_column($filter,"group"));
            if($filterIndex !== false){
                $q->where(function($q) use (&$filter,&$filterIndex,$referenceSetting,$nativeSqlEnabled){
                    while ($filterIndex !== false) {
                        $front_field = $filter[$filterIndex];
                        if(!empty($referenceSetting["front_field"]["target"])){
                            $target = $referenceSetting["front_field"]["target"];
                            $front_field["field_code"] = $nativeSqlEnabled ? $target : $front_field["field_code"];
                        }
                        $q->where($front_field["field_code"],$front_field["comparison_operator"],$front_field["value"]);

                        unset($filter[$filterIndex]);
                        $filter = array_values($filter);
                        $filterIndex = array_search(null,array_column($filter,"group"));
                    }
                });
                try {
                    $q->get();
                } catch (\Throwable $th) {
                    if($this->DEBUG_MODE){
                        dd($th, $q->toSql());
                    }
                    return referenceError($result, "front_error");
                }
            }else{
                return referenceError($result, "no_front");
            }
        }

        // 篩選若有"所有欄位"
        $allColumn = array_search("*",array_column($filter,"field_code"));
        $allColFilter = [];
        while ($allColumn !== false) {
            $temp = [];
            $colCondition = $filter[$allColumn];
            $mainLogical = $colCondition["logical_operator"];
            $colCondition["logical_operator"] = "or";
            foreach($referenceSetting["fields"] as $f){
                $colCondition["field_code"] = "{$f["table_name"]}.{$f["field_code"]}";
                array_push($temp,$colCondition);
            }
            array_push($allColFilter,[$mainLogical,$temp]);
            unset($filter[$allColumn]);
            $filter = array_values($filter);
            $allColumn = array_search("*",array_column($filter,"field_code"));
        }

        // 串篩選資料
        if(sizeof($filter) > 0 || sizeof($allColFilter) > 0){
            $q->where(function($q) use ($filter, $allColFilter){
                $q = DatabaseUtil::groupExpression($q, $filter, "and");
                foreach($allColFilter as $f){
                    $q = DatabaseUtil::groupExpression($q, $f[1], $f[0]);
                }
            });
            try {
                $q->get();
            } catch (\Throwable $th) {
                if($this->DEBUG_MODE){
                    dd($th, $q->toSql());
                }
                return referenceError($result, "filter_error");
            }
        }

        try {
            DatabaseUtil::whereDataIsVerified($q);
            // dd($q->toSql(), $q->getBindings());
            foreach($q->get() as $row){
                array_push($result["datas"], $row);
            }
            return response()->json($result,200);
        } catch (\Throwable $th) {
            if($this->DEBUG_MODE){
                dd($th, $q->toSql());
            }
            return referenceError($result, "reference_error");
        }
    }
}
?>
