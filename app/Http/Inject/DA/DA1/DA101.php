<?php

namespace App\Http\Inject\DA\DA1;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class DA101 extends InjectBase
{
    static public function bfDelete(&$data,$verify = false){
        if($verify){
			$page_id = 46;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
        }
        $code = $data['data']['station_code'];
        $results = DB::select(
        "DECLARE @tablename NVARCHAR(50)
        DECLARE @cloumnname NVARCHAR(50)
        DECLARE @store TINYINT

        SET @tablename=''
        SET @cloumnname='station_code'

        BEGIN

        SELECT DISTINCT SUBSTRING(so.name,1,CHARINDEX('_',so.name)-1) as 't',so.name FROM sysobjects so
        INNER JOIN syscolumns sc ON so.id =sc.id
        INNER JOIN systypes st ON st.xtype=sc.xtype
        WHERE (so.type='U'AND st.name <> 'sysname')
        AND
        so.name LIKE '%'+@tablename+'%'
        AND
        sc.name LIKE '%'+@cloumnname+'%'
        
        order by 't'

        END"
        );
        //dd($results);
        $pagecode = '';
        foreach($results as $key => $val){
            if($val->t == 'DA201'){
                $indata = DB::table($val->name)
                        ->select('*')
                        ->where('station_code', $code)
                        ->get();
                if(count($indata) > 0){
                    $translations = TranslationUtil::getTranslationByCode($val->t);
                    $pagecode = $pagecode."「".$translations."」";
                }
            }
        }
        return array(
            'pagecode' => $pagecode,
            'code' => $code
        );
    }
    // View
    static public function beforeView(&$id, &$pageData)
    {
    }
    static public function afterView(&$id, &$data, &$pageData)
    {
    }

    // Save
    static public function beforeSave(&$data, &$pageData)
    {
    }
    static public function beforeDatasetValidation(&$dataset, &$schema, &$rules, &$pageData)
    {
    }
    static public function afterDatasetValidationSuccess(&$dataset, &$schema, &$rules, &$validationResult, &$pageData)
    {
    }
    static public function afterDatasetValidationFail(&$dataset, &$schema, &$rules, &$validationResult, &$pageData)
    {
    }
    static public function beforeDatasetInsert(&$dataset, &$schema, &$insertData, &$pageData)
    {
    }
    static public function afterDatasetInsert(&$dataset, &$schema, &$insertData, &$pageData)
    {
    }
    static public function beforeDatasetUpdate(&$dataset, &$schema, &$updateData, &$pageData)
    {
    }
    static public function afterDatasetUpdate(&$dataset, &$schema, &$updateData, &$pageData)
    {
    }
    static public function afterSuccessSave(&$data, &$pageData)
    {
    }
    static public function afterFailSave(&$data, &$pageData)
    {
    }

    // Delete
    static public function beforeDelete(&$data, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
            $res = self::bfDelete($data);
            if($res['pagecode'] != ''){
                response()->json(['status' => false , 'message' => '工站代碼：'.$res['code'].'  已被引用於：'.$res['pagecode'].'，故無法刪除'])->send();
                die();
            }
        }
    }
    static public function afterDeleteSuccess(&$data, &$pageData)
    {
    }
    static public function afterDeleteFail(&$data, &$pageData)
    {
    }

    // Filter
    static public function beforeFilter(&$requestData, &$pageData)
    {
    }
    static public function afterFilter(&$requestData, &$filterResult, &$pageData)
    {
    }
    // List
    static public function beforeList(&$pageData)
    {
    }
    // Verify
    static public function beforeExecuteVerify(&$data, &$result){}
    static public function afterSuccessExecuteVerify(&$data, &$result){}
    static public function afterLastestExecuteVerify(&$data, &$result){}
    static public function afterFailedExecuteVerify(&$data, &$result){}
    static public function beforeReturnVerify(&$data, &$result){}
    static public function afterReturnVerify(&$data, &$result){}
    static public function afterLastestReturnVerify(&$data, &$result){
        $tmpArr = self::bfDelete($data,true);
        if($tmpArr['pagecode'] != ''){           
            $result["messages"] = ['工站代碼：'.$tmpArr['code'].'  已被引用於：'.$tmpArr['pagecode'].'，故無法退回'];
            $result["success"] = false;
        }
    }
    static public function beforeInitVerify(&$data, &$result){}
    static public function afterInitVerify(&$data, &$result){}
    //255重置
	static public function afterLastestInitVerify(&$data, &$result){
		$tmpArr = self::bfDelete($data,true);
        if($tmpArr['pagecode'] != ''){           
            $result["messages"] = ['工站代碼：'.$tmpArr['code'].'  已被引用於：'.$tmpArr['pagecode'].'，故無法重置'];
            $result["success"] = false;
        }
	} 
}
