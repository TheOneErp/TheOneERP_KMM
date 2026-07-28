<?php

namespace App\Http\Inject\RE\RE2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;

use App\Utils\VerifyUtil;
use App\Utils\PageUtil;

use Carbon\Carbon;

class RE202 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
      if( !array_key_exists('status',$data) ){
        $page_id = 6258;
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        // Get data
        $data = PageUtil::getData($pageData, $data['id']);
        $data['status'] = 'add';
      }else{
        $data['status'] = $data['status'] == "" ? "update" : $data['status'];
      }
      $tmpArr = [];
      $lease_fdate = new Carbon($data['data']['lease_fdate']);
      $lease_tdate = new Carbon($data['data']['lease_tdate']);
      // dd($lease_fdate->gt($lease_tdate));
      if( $lease_fdate->gt($lease_tdate) ){
        if($verify){
          $tmpArr[] = '警告：租約起日不可大於租約迄日，請確認';
        }else{
          array_push($tmpArr,[
            "text" => "警告：租約起日不可大於租約迄日，請確認"
          ]);
        }
        return $tmpArr;
      }

      if( $data['status'] == "update" ){
        $old = DB::table('RE202_6231')->where('id',$data['data']['id'])->first();
        $old_house_id = $old->house_id;
        //更新租賃租資料
        DB::table('RE201_6230')->where('house_id', $old_house_id)
        ->update( [
          'hstatus' => '待租中'
        ]);
      }


      return $tmpArr;

    }

    static public function atSave(&$data,$verify = false){
      if( $verify ){
        $page_id = 6258;
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        // Get data
        $data = PageUtil::getData($pageData, $data['data']['id']);
        $data['status'] = 'add';
      }else{
        $data['status'] = $data['status'] == "" ? "update" : $data['status'];
      }

      //更新租賃租資料
      DB::table('RE201_6230')->where('house_id', $data['data']['house_id'])
      ->update( [
        'lease_fdate' => $data['data']['lease_fdate'],
        'lease_tdate' => $data['data']['lease_tdate'],
        'hstatus' => '承租中'
      ]);


    }

    static public function bfDelete(&$data,$verify = false){
      // dd($data);
      if($verify){
        $page_id = 6258;
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        // Get data
        $data = PageUtil::getData($pageData, $data['id']);
        $warm = [];
      }else{
        $warm = '';
      }

      // $warm = '';
      $getDetailData = DB::table('RE202_6232')->where('parent_id','=',$data['data']['id'])->whereNotNull('pamount')->lockForUpdate()->get();
      if( $getDetailData->count() != 0 ){
        if($verify){
          $warm[] = "警告：本表單租金明細已有繳費紀錄。";
        }else{
          $warm = $warm."警告：本表單租金明細已有繳費紀錄。 \n";
        }

      }
      $getDetailData = DB::table('RE202_6233')->where('parent_id','=',$data['data']['id'])->whereNotNull('rdate')->lockForUpdate()->get();
      if( $getDetailData->count() != 0 ){
        if($verify){
          $warm[] = "警告：本表單已有應收費用明細紀錄。";
        }else{
          $warm = $warm."警告：本表單已有應收費用明細紀錄。 \n";
        }

      }
      $getDetailData = DB::table('RE205_6238')->where('contract_id','=',$data['data']['contract_id'])->lockForUpdate()->get();
      if( $getDetailData->count() != 0 ){
        if($verify){
          $warm[] = "警告：本表單有中斷紀錄。";
        }else{
          $warm = $warm."警告：本表單有中斷紀錄。 \n";
        }
      }
      $getDetailData = DB::table('RE206_6239')->where('contract_id','=',$data['data']['contract_id'])->lockForUpdate()->get();
      if( $getDetailData->count() != 0 ){
        if($verify){
          $warm[] = "警告：本表單有到期紀錄。";
        }else{
          $warm = $warm."警告：本表單有到期紀錄。 \n";
        }
      }

      return $warm;

    }
    static public function atDelete(&$data,$verify = false){

      if($verify){
        $page_id = 6258;
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        // Get data
        $data = PageUtil::getData($pageData, $data['data']['id']);
      }
      //更新租賃租資料
      DB::table('RE201_6230')->where('house_id', $data['data']['house_id'])
      ->update( [
        'lease_fdate' => null,
        'lease_tdate' => null,
        'hstatus' => '待租中'
      ]);
      //舊合約
      $contract = DB::table('RE202_6231')
        ->select('*')
        ->where('new_contract_id', $data['data']['contract_id'])
        ->first();
      if(!empty($contract)){
          //續約單號刪除
          DB::table('RE202_6231')->where('new_contract_id', $data['data']['contract_id'])
          ->update( [
              'new_contract_id' => null
          ]);
          //租賃到期轉否並刪續約單號
          DB::table('RE206_6239')
            ->where('contract_id', $contract->contract_id)
            ->update([
                'lease_renew' => '否',
                'new_contract_id' => null,
            ]);
      }
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
      // $data['status'] = $data['status'] == "" ? "update" : $data['status'];
      $pageId = $pageData['page']['page_id'];
      if( array_key_exists('contract_id',$data['data']) ){
        $number = CommonController::generateDocumentNumber($pageId,'contract_id',$data['data']['contract_id']);
			  $data['data']['contract_id'] = $number;
      }
      // if( $data['status'] == "add" ){
        if( is_null($data['data']['contract_st']) ){
          $data['data']['contract_st'] = "承租中";
        }
        if( is_null($data['data']['deposit_sta']) ){
          $data['data']['deposit_sta'] = "已收";
        }
      // }
      if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) { //沒有開審核
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
        /* $pageId = $pageData['page']['page_id'];
        if( array_key_exists('contract_id',$dataset['data']) ){
          $number = CommonController::generateDocumentNumber($pageId,'contract_id',$dataset['data']['contract_id']);
          $dataset['data']['contract_id'] = $number;
        } */
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
        DB::beginTransaction();
        $warm = self::bfDelete($data);
        if($warm != ''){
    //            $translations = TranslationUtil::getTranslationByCode('CA202.error.deletefailed');
          response()->json(['status' => false , 'message' => $warm.'故無法刪除'])->send();
          DB::rollback();
          die();
        }else{
          DB::commit();
        }
      }
    }
    static public function afterDeleteSuccess(&$data, &$pageData)
    {
      $pageId = $pageData['page']['page_id'];
      if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
        self::atDelete($data);
      }
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
      $tmpArr = self::bfSave($data,true);
      if( !empty($tmpArr) ){
        $result["messages"] = $tmpArr;
        $result["success"] = false;
      }else{
        self::atSave($data);
      }
    }
    static public function afterFailedExecuteVerify(&$data, &$result){}
    //從255退回
    static public function afterLastestReturnVerify(&$data, &$result){
      $tmpArr = self::bfDelete($data,true);
      if( !empty($tmpArr) ){
        $result["messages"] = $tmpArr;
        $result["success"] = false;
      }else{
        self::atDelete($data,true);
      }

    }
    //退回前
      static public function beforeReturnVerify(&$data, &$result){}
    //退回後
    static public function afterReturnVerify(&$data, &$result){}
    //255重置
    static public function afterLastestInitVerify(&$data, &$result){

      $tmpArr = self::bfDelete($data,true);
      if( !empty($tmpArr) ){
        $result["messages"] = $tmpArr;
        $result["success"] = false;
      }else{
        self::atDelete($data,true);
      }
    }

}
