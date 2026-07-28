<?php

namespace App\Http\Inject\AA\AA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class AA204 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
        if( !array_key_exists('status',$data) ){
			$page_id = 2245;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
        $tmpArr = [];
        return $tmpArr;
    }
    static public function atSave(&$data,$verify = false){

    }
    static public function bfDelete(&$data,$verify = false){

        if($verify){
			$page_id = 2245;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
        }
        $code = $data['data']['product_code'];
        $name = $data['data']['combi_code'];
        $pagecode = '';

        $t = 'BA201';
        $indata = DB::table("BA201_41")
        ->select('*')
        ->where('product_code', $code)
        ->where('combi_code', $name)
        ->get();
        if(count($indata) > 0){
            $translations = TranslationUtil::getTranslationByCode($t);
            $pagecode = $pagecode."「".$translations."」";
        }

        $t = 'BA202';
        $indata = DB::table("BA202_53")
        ->select('*')
        ->where('product_code', $code)
        ->where('combi_code', $name)
        ->get();
        if(count($indata) > 0){
            $translations = TranslationUtil::getTranslationByCode($t);
            $pagecode = $pagecode."「".$translations."」";
        }

        $t = 'BA203';
        $indata = DB::table("BA203_62")
        ->select('*')
        ->where('product_code', $code)
        ->where('combi_code', $name)
        ->get();
        if(count($indata) > 0){
            $translations = TranslationUtil::getTranslationByCode($t);
            $pagecode = $pagecode."「".$translations."」";
        }

        return array(
            'pagecode' => $pagecode,
            'code' => $code,
            'name' => $name
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
        $pageId = $pageData['page']['page_id'];
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
			DB::beginTransaction();
			$tmpArr = self::bfSave($data);
			$errorMsg["errors"] = $tmpArr;
			if( !empty($errorMsg["errors"]) ){
				response()->json($errorMsg)->send();
				DB::rollback();
				die();
			}else{
				DB::commit();
			}
		}
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
        $pageId = $pageData['page']['page_id'];
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
			self::atSave($data);
        }
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
                response()->json(['status' => false , 'message' => '產品代碼：'.$res['code'].'  中的  組合名稱：'.$res['name'].'  已被引用於：'.$res['pagecode'].'，故無法刪除'])->send();
                die();
            }
        }

        //dd($data);
    }
    static public function afterDeleteSuccess(&$data, &$pageData)
    {
        //dd($data);
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
    static public function afterLastestExecuteVerify(&$data, &$result){
        self::atSave($data,true);
    }
    static public function afterFailedExecuteVerify(&$data, &$result){}
    static public function beforeReturnVerify(&$data, &$result){}
    static public function afterReturnVerify(&$data, &$result){}
    static public function afterLastestReturnVerify(&$data, &$result){
        $result["messages"] = ["此產品資料已被審核過，故無法退回"];
        $result["success"] = false;
    }
    static public function beforeInitVerify(&$data, &$result){}
    static public function afterInitVerify(&$data, &$result){}
    //255重置
	static public function afterLastestInitVerify(&$data, &$result){
		$result["messages"] = ["此產品資料已被審核過，故無法重置"];
        $result["success"] = false;
	}
}
