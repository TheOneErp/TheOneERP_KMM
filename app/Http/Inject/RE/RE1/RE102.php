<?php

namespace App\Http\Inject\RE\RE1;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;

use App\Utils\VerifyUtil;
use App\Utils\PageUtil;

use Carbon\Carbon;

class RE102 extends InjectBase
{
    static public function bfDelete(&$data,$verify = false){
        // dd($data);
        if($verify){
            $page_id = 6255;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['id']);
        }
        $code = $data['data']['payby_name'];
        $delmessage = CommonController::AreUsedfordelete($code,'RE102','rent_payby');

        return $delmessage;

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
            DB::beginTransaction();
            $warm = self::bfDelete($data);
            $code = $data['data']['payby_name'];
            if($warm != ''){
            //            $translations = TranslationUtil::getTranslationByCode('CA202.error.deletefailed');
                response()->json(['status' => false , 'message' => '支付方式名稱：'.$code.'  已被引用於：'.$warm.'，故無法刪除'])->send();
                DB::rollback();
                die();
            }else{
                DB::commit();
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
    //執行審核前 $result = array() boolen message//提示訊息
    static public function beforeExecuteVerify(&$data, &$result){}
    //每層成功
    static public function afterSuccessExecuteVerify(&$data, &$result){}
    //255觸發
    static public function afterLastestExecuteVerify(&$data, &$result){
    }
    static public function afterFailedExecuteVerify(&$data, &$result){}
    //從255退回
    static public function afterLastestReturnVerify(&$data, &$result){
        $tmpArr = self::bfDelete($data,true);
        $code = $data['data']['payby_name'];
        if( !empty($tmpArr) ){
            $result["messages"] = ['支付方式名稱：'.$code.'  已被引用於：'.$tmpArr.'，故無法退回'];
            $result["success"] = false;
        }

    }
    //退回前
    static public function beforeReturnVerify(&$data, &$result){}
    //退回後
    static public function afterReturnVerify(&$data, &$result){}
    //255重置
    static public function afterLastestInitVerify(&$data, &$result){
        $tmpArr = self::bfDelete($data,true);
        $code = $data['data']['payby_name'];
        if( !empty($tmpArr) ){
            $result["messages"] = ['支付方式名稱：'.$code.'  已被引用於：'.$tmpArr.'，故無法退回'];
            $result["success"] = false;
        }
    }
}
