<?php

namespace App\Utils;

use App\Utils\SessionUtil;

class ModelUtil
{
    function insertMultipleDataWithModel($model,$dataArray){
        foreach($dataArray as $data){
            ModelUtil::insertDataWithModel($model,$data);
        }
        return true;
    }
    function insertDataWithModel($model,$data){
            $data = $model::create($data);

    }

    function updateDataWithModel(){

    }
}
