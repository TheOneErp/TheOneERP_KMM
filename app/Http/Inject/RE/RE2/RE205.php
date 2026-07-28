<?php

namespace App\Http\Inject\RE\RE2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\DataUtil;

use App\Utils\VerifyUtil;
use App\Utils\PageUtil;

use Carbon\Carbon;

class RE205 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
        if( !array_key_exists('status',$data) ){
            $page_id = 6261;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['id']);
            $data['status'] = 'add';
        }else{
            $data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        $tmpArr = [];
        if($data['status'] != 'add'){
            $olddata = DB::table('RE205_6238')
                     ->select('id','contract_id')
                     ->where('id', $data['data']['id'])
                     ->first();
            //合約對應的房屋有被引用時需跳警告
            $used = CommonController::UsedHouse('RE205_6238',$data['data']['contract_id'],$olddata->contract_id);
            if($used == 1){
                array_push($tmpArr,[
                    "text" => '警告：該合約代碼對應的房屋已被引用，無法修改'
                ]);
            }

            //將房屋租賃合約.[合約狀態 ]更新為「承租中；[押金情況 ]更新為「已收；[退租日期 ]更新為null.[日期 ];[退租金額 ]更新為null
            $contract = DB::table('RE202_6231')
                         ->select('*')
                         ->where('contract_id', $olddata->contract_id);
            if (VerifyUtil::pageVerifyConfirmation(6258)) {
                $contract = $contract->where("data_options", "LIKE", '%"verify":{%"level":255%');
            }
            $contract = $contract->first();
            if(!empty($contract)){
                // DB::table('RE202_53')
                //         ->where('contract_id', $olddata->contract_id)
                //         ->update([
                //             'contract_st' => '承租中',
                //             'deposit_sta' => '已收',
                //             'cancel_date' => null,
                //             'bdeposit' => null,
                //             'data_options' => null,
                //         ]);
                $t = "RE202_6231";
                if( is_null($contract->data_options) ){
                    $dataOptions = [
                        'deletable' => true,
                        'editable' => true,
                        'cloneable'=>true
                    ];
                }else{
                    $dataOptions = DataUtil::convertToArray(json_decode($contract->data_options));
                    $dataOptions["deletable"] = true;
                    $dataOptions["editable"] = true;
                    $dataOptions["cloneable"] = true;
                }
                DB::table($t)->where('id', $contract->id)->update([
                    'contract_st' => '承租中',
                    'deposit_sta' => '已收',
                    'cancel_date' => null,
                    'bdeposit' => null,
                    "data_options" => json_encode($dataOptions)
                ]);
            }
            //將房屋租賃資料.[房屋狀態 ]更新為「承租中」。
            $house = DB::table('RE201_6230')
                         ->select('house_id','hstatus')
                         ->where('house_id', $contract->house_id);
            if (VerifyUtil::pageVerifyConfirmation(6257)) {
                $house = $house->where("data_options", "LIKE", '%"verify":{%"level":255%');
            }
            $house = $house->first();
            if(!empty($contract)){
                DB::table('RE201_6230')
                        ->where('house_id', $contract->house_id)
                        ->update([
                            'hstatus' => '承租中',
                        ]);
            }
        }
        return $tmpArr;
    }

    static public function atSave(&$data,$verify = false){
        if( $verify ){
            $page_id = 6261;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['data']['id']);
            $data['status'] = 'add';
        }else{
            $data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }

        //將房屋租賃合約.[合約狀態 ]更新為「中斷；[押金情況 ]更新為「已退；[退租日期 ]更新為本表單 .[日期 ];[退租金額 ]更新為本表單 .[退押金額 ]
        $contract = DB::table('RE202_6231')
                     ->select('*')
                     ->where('contract_id', $data['data']['contract_id']);
        if (VerifyUtil::pageVerifyConfirmation(6258)) {
            $contract = $contract->where("data_options", "LIKE", '%"verify":{%"level":255%');
        }
        $contract = $contract->first();
        if(!empty($contract)){
            // DB::table('RE202_53')
            //         ->where('contract_id', $data['data']['contract_id'])
            //         ->update([
            //             'contract_st' => '中斷',
            //             'deposit_sta' => '已退',
            //             'cancel_date' => $data['data']['date'],
            //             'bdeposit' => $data['data']['bdeposit'],
			// 			'data_options' => '{"editable":false,"deletable":false}',
            //         ]);
            $t = "RE202_6231";
            if( is_null($contract->data_options) ){
                $dataOptions = [
                    'deletable' => false,
                    'editable' => false,
                ];
            }else{
                $dataOptions = DataUtil::convertToArray(json_decode($contract->data_options));
                $dataOptions["deletable"] = false;
                $dataOptions["editable"] = false;
            }
            DB::table($t)->where('id', $contract->id)->update([
                'contract_st' => '中斷',
                'deposit_sta' => '已退',
                'cancel_date' => $data['data']['date'],
                'bdeposit' => (int)$data['data']['bdeposit'],
                "data_options" => json_encode($dataOptions)
            ]);
        }
        //將房屋租賃資料.[房屋狀態 ]更新為「待租中」。
        $house = DB::table('RE201_6230')
                     ->select('house_id','hstatus')
                     ->where('house_id', $contract->house_id);
        if (VerifyUtil::pageVerifyConfirmation(6257)) {
            $house = $house->where("data_options", "LIKE", '%"verify":{%"level":255%');
        }
        $house = $house->first();
        if(!empty($contract)){
            DB::table('RE201_6230')
                    ->where('house_id', $contract->house_id)
                    ->update([
                        'hstatus' => '待租中',
                    ]);
        }
    }

    static public function bfDelete(&$data,$verify = false){
        // dd($data);
        if($verify){
            $page_id = 6261;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['id']);
            $warm = [];
        }else{
            $warm = '';
        }
//        //判斷房屋是否為承租中，承租中就不可刪除
//        $house = DB::table('RE201_46 as a')
//                    ->select('a.house_id','a.hstatus','b.contract_id')
//                    ->leftJoin('RE202_53 as b', 'a.house_id', '=', 'b.house_id')
//                    ->where('b.contract_id', $data['data']['contract_id'])
//                    ->first();
//        if($house->hstatus == '承租中'){
//            $warm = $warm.'房屋代碼為 "'.$house->house_id.'" ，房屋狀態已為'.$house->hstatus.'。
//            ';
//        }
        //合約對應的房屋有被引用時需跳警告
        $used = CommonController::UsedHouse('RE205_6238',$data['data']['contract_id'],null);
        if($used == 1){
            if($verify){
                $warm[] = "該合約代碼對應的房屋已被引用。 \n";
            }else{
                $warm = $warm."該合約代碼對應的房屋已被引用。 \n";
            }
        }
        return $warm;
    }

    static public function atDelete(&$data,$verify = false){
        if($verify){
            $page_id = 6261;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['data']['id']);
        }
        //將房屋租賃合約.[合約狀態 ]更新為「承租中；[押金情況 ]更新為「已收；[退租日期 ]更新為null.[日期 ];[退租金額 ]更新為null
        $contract = DB::table('RE202_6231')
                     ->select('*')
                     ->where('contract_id', $data['data']['contract_id']);
        if (VerifyUtil::pageVerifyConfirmation(6258)) {
            $contract = $contract->where("data_options", "LIKE", '%"verify":{%"level":255%');
        }
        $contract = $contract->first();
        if(!empty($contract)){
            // DB::table('RE202_53')
            //         ->where('contract_id', $data['data']['contract_id'])
            //         ->update([
            //             'contract_st' => '承租中',
            //             'deposit_sta' => '已收',
            //             'cancel_date' => null,
            //             'bdeposit' => null,
			// 			'data_options' => null,
            //         ]);
            $t = "RE202_6231";
            if( is_null($contract->data_options) ){
                $dataOptions = [
                    'deletable' => true,
                    'editable' => true,
                ];
            }else{
                $dataOptions = DataUtil::convertToArray(json_decode($contract->data_options));
                $dataOptions["deletable"] = true;
                $dataOptions["editable"] = true;
            }
            DB::table($t)->where('id', $contract->id)->update([
                'contract_st' => '承租中',
                'deposit_sta' => '已收',
                'cancel_date' => null,
                'bdeposit' => null,
                "data_options" => json_encode($dataOptions)
            ]);
        }
        //將房屋租賃資料.[房屋狀態 ]更新為「承租中」。
        $house = DB::table('RE201_6230')
                     ->select('house_id','hstatus')
                     ->where('house_id', $contract->house_id);
        if (VerifyUtil::pageVerifyConfirmation(6257)) {
            $house = $house->where("data_options", "LIKE", '%"verify":{%"level":255%');
        }
        $house = $house->first();
        if(!empty($contract)){
            DB::table('RE201_6230')
                    ->where('house_id', $contract->house_id)
                    ->update([
                        'hstatus' => '承租中',
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
            DB::beginTransaction();
            $warm = self::bfDelete($data);
            if($warm != ''){
            //            $translations = TranslationUtil::getTranslationByCode('CA202.error.deletefailed');
                response()->json(['status' => false , 'message' => '警告：'.$warm.'故無法刪除'])->send();
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
