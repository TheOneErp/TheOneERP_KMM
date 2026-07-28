<?php

namespace App\Utils;

class DatabaseUtil
{
    public static function groupExpression ($q, $expressions, $group_logical = "or", $keys = null) {
        // TODO: 檢查欄位是否存在於table
        if(is_null($keys)){
            $keys = ["group","logical_operator","field_code","comparison_operator","value"];
        }
        $expressionsForNext = [];
        if(sizeof($expressions) > 0){
            $group = $expressions[0][$keys[0]];
            foreach ($expressions as $key => $expression){
                if($expression[$keys[0]] !== $group){
                    array_push($expressionsForNext,$expression);
                    unset($expressions[$key]);
                }
            }
            $groupWhere = DatabaseUtil::whereType($group_logical);
            $q->{$groupWhere}(function($q) use ($expressions, $keys){
                foreach($expressions as $expression){
                    $logical = DatabaseUtil::whereType($expression[$keys[1]]);
                    $column = $expression[$keys[2]];
                    $comparison = strtoupper($expression[$keys[3]]);
                    $value = $expression[$keys[4]];
                    if(strpos($comparison,"LIKE") !== false && substr($value,0,1) !== "%") $value = "%$value";
                    if(strpos($comparison,"LIKE") !== false && substr($value,-1) !== "%") $value = "$value%";
                    if(!empty($column)) $q->{$logical}($column,$comparison,$value);
                }
            });
            if(sizeof($expressionsForNext) > 0){
                $q = DatabaseUtil::groupExpression($q,$expressionsForNext,$expressionsForNext[0][$keys[1]]);
            }
        }
        return $q;
    }

    public static function deleteWithRelationship($data, $relationship = null){
        $deleteData = function($data) use ($relationship){
            if(is_null($relationship) && method_exists($data,"delete")){
                $data->delete();
            }else if(method_exists($data,$relationship)){
                DatabaseUtil::deleteWithRelationship($data->{$relationship});
            }
        };

        if(!is_null($data)){
            if(DataUtil::isCollection($data)){
                foreach($data as $toDelete){
                    $deleteData($toDelete);
                }
            }else{
                $deleteData($data);
            }
        }
    }

    public static function whereType(string $logical){
        $correspond = [
            "and" => "where",
            "or" => "orWhere"
        ];
        return isset($correspond[strtolower($logical)]) ? $correspond[strtolower($logical)] : null;
    }

    public static function whereDataIsVerified($query){
        $checkVerification = function($q, $tableName){
            $tnSplit = explode('_',$tableName);
            if(sizeof($tnSplit) === 2){
                $pageCode = $tnSplit[0];
                $formId = $tnSplit[1];
                $pageData = PageUtil::getPageDataByPageCode($pageCode);
                if($formId == $pageData["forms"][0]["form_id"]){
                    $temp = $q;
                    $hasDataOption = true;
                    try{
                        $temp->where("{$tableName}.data_options","LIKE",'%"verify":{%"level":255,%');
                        $temp->get();
                    }catch(\Exception $th){
                        // throw $th;
                        $hasDataOption = false;
                    }
                    if($hasDataOption){
                        $q->where("{$tableName}.data_options","LIKE",'%"verify":{%"level":255,%');
                    }
                }
            }
        };

        $query->where(function($q) use ($query, $checkVerification){
            $checkVerification($q, $q->from);
            if(isset($query->joins)){
                foreach($query->joins as $join){
                    $checkVerification($q, $join->table);
                }
            }
        });
    }
}
