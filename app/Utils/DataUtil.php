<?php

namespace App\Utils;

use App\Utils\ValidationUtil;

class DataUtil
{
    public static function convertToArray($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }
        if(is_array($data))
            foreach($data as &$subData){
                $subData = DataUtil::convertToArray($subData);
            }
        return $data;
    }

    public static function arrayToKeyValueArray($data,$key)
    {
        $tmp = [];
        foreach($data as $item){
            $tmp[$item[$key]] = $item;
        }
        return $tmp;
    }

    public static function arraySearch($array,$function){
        $data = array_filter($array,$function);
        return count($data) > 0 ? array_pop($data) : null;
    }

    /**
     * @param string        $string    原始字串
     * @param string|array  $union     欲串接之字串，可以是Array
     * @param string        $delimiter 分界符
     *
     * @return string "{$string}{$delimiter}{$union}"
     */
    public static function unionString(string $string, $union, string $delimiter = ","){
        if(is_array($union)){
            foreach($union as $u){
                $string = DataUtil::unionString($string, $u, $delimiter);
            }
        }else{
            if(is_string($string) && ($string === "0" || !empty($string))){
                $string .= $delimiter;
            }
            $string .= $union;
        }

        return $string;
    }

    public static function isCollection($object){
        return is_a($object,'Illuminate\Database\Eloquent\Collection') || is_a($object,'Illuminate\Support\Collection');
    }

    public static function parseExcelColumn(string $column){
        $result = null;
        if(preg_match("/^[A-Z]+$/",$column) === 1){
            $result = 0;
            for ($i = 1; $i <= strlen($column); $i++) {
                $ASCII = (int) ord($column);
                // dump(($ASCII - 64), pow(26, strlen($column[0]) - $i));
                $result += (($ASCII - 64) * pow(26, strlen($column) - $i)) - 1;
            }
            $result += strlen($column) - 1;
        };

        return $result;
    }

    public static function parseExcelCell(string $cell){
        $result = [];
        $rowPatt = "/[0-9]+$/";
        $columnPatt = "/^[A-Z]+/";
        preg_match($columnPatt,$cell,$column);
        preg_match($rowPatt,$cell,$row);
        // dd($column,$row);
        if(sizeof($column) == 1 && sizeof($row) == 1){
            $result = [
                "row" => (int) $row[0] - 1,
                "column" => DataUtil::parseExcelColumn($column[0])
            ];
        }

        return $result;
    }

    public static function decodeJSONToArray(string $json){
        $result = [];
        if(ValidationUtil::isJSONString($json)){
            $result = DataUtil::convertToArray(json_decode($json));
        }

        return $result;
    }
}
