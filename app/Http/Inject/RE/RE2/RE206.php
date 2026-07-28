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

class RE206 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
        if( !array_key_exists('status',$data) ){
            $page_id = 6262;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['id']);
            $data['status'] = 'add';
        }else{
            $data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        $tmpArr = [];
        if($data['status'] != 'add'){
            $olddata = DB::table('RE206_6239')
                     ->select('id','contract_id','new_contract_id')
                     ->where('id', $data['data']['id'])
                     ->first();
            //合約對應的房屋有被引用時需跳警告
            $used = CommonController::UsedHouse('RE206_6239',$data['data']['contract_id'],$olddata->contract_id);
//            dd($used);
            if($used == 1){
                array_push($tmpArr,[
                    "text" => '警告：該合約代碼對應的房屋已被引用，無法修改'
                ]);
            }

            //將原房屋租賃合約.[合約狀態 ]更新為「承租中；[押金情況 ]更新為「已收；[退租日期 ]更新為null;[退租金額 ]更新為null;將續約對應合約編號寫入備註
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
                //             'new_contract_id' => null,
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
                    $dataOptions['cloneable']=true;
                }

                DB::table($t)->where('id', $contract->id)->update([
                    'contract_st' => '承租中',
                    'deposit_sta' => '已收',
                    'cancel_date' => null,
                    'bdeposit' => null,
                    'new_contract_id' => null,
                    "data_options" => json_encode($dataOptions)
                ]);
                //將房屋租賃資料.[房屋狀態 ]更新為「承租中」。
                $house = DB::table('RE201_6230')
                         ->select('house_id','hstatus')
                         ->where('house_id', $contract->house_id);
                if (VerifyUtil::pageVerifyConfirmation(6257)) {
                    $house = $house->where("data_options", "LIKE", '%"verify":{%"level":255%');
                }
                $house = $house->first();
                DB::table('RE201_6230')
                        ->where('house_id', $contract->house_id)
                        ->update([
                            'hstatus' => '承租中',
                        ]);
            }
            //將新合約刪除
            DB::table('RE202_6231')
                        ->where('contract_id', $olddata->new_contract_id)
                        ->delete();
        }
        return $tmpArr;
    }

    static public function atSave(&$data,$verify = false){
        if( $verify ){
            $page_id = 6262;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['data']['id']);
            $data['status'] = 'add';
        }else{
            $data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }

        //將房屋租賃合約.[合約狀態 ]更新為「租約到期；[押金情況 ]更新為「已退；[退租日期 ]更新為本表單 .[日期 ];[退租金額 ]更新為本表單 .[退押金額 ]
        $contract = DB::table('RE202_6231')
                     ->select('*')
                     ->where('contract_id', $data['data']['contract_id']);
        if (VerifyUtil::pageVerifyConfirmation(6258)) {
            $contract = $contract->where("data_options", "LIKE", '%"verify":{%"level":255%');
        }
        $contract = $contract->first();
//        dd($contract);
        if(!empty($contract)){
            // DB::table('RE202_53')
            //         ->where('contract_id', $data['data']['contract_id'])
            //         ->update([
            //             'contract_st' => '租約到期',
            //             'deposit_sta' => '已退',
            //             'cancel_date' => $data['data']['date'],
            //             'bdeposit' => $data['data']['bdeposit'],
			// 			'data_options' => '{"editable":false,"deletable":false,"cloneable":false}',
            //         ]);
            $t = "RE202_6231";
            if( is_null($contract->data_options) ){
                $dataOptions = [
                    'deletable' => false,
                    'editable' => false,
                    'cloneable'=>false
                ];
            }else{
                $dataOptions = DataUtil::convertToArray(json_decode($contract->data_options));
                $dataOptions["deletable"] = false;
                $dataOptions["editable"] = false;
                $dataOptions["cloneable"] = false;
            }
            DB::table($t)->where('id', $contract->id)->update([
                'contract_st' => '租約到期',
                'deposit_sta' => '已退',
                'cancel_date' => $data['data']['date'],
                'bdeposit' => (int)$data['data']['bdeposit'],
                "data_options" => json_encode($dataOptions)
            ]);
            //選擇續約時，複製一新的合約
            if($data['data']['lease_renew'] == '是'){
                $pageId = 6258;

                $number = CommonController::generateDocumentNumber($pageId,'contract_id');
                $datetime = new \DateTime;

                DB::table('RE202_6231')->insert(
                [
                    'contract_id' => $number,
                    'contract_st' => null,
                    'parent_id' => -1,
                    'created_by' => $data['data']['created_by'],
                    'created_at' => $datetime->format('Y-m-d H:i:s'),
                    'updated_by' => $data['data']['updated_by'],
                    'updated_at' => $datetime->format('Y-m-d H:i:s'),
                    'undertaker' => session("username"),
                    'undertakername' => session("user_name"),
                    'undertakerday' => date("Y-m-d"),
                    'lease_fdate' => $contract->lease_fdate,
                    'lease_tdate' => $contract->lease_tdate,
                    'coll_date' => $contract->coll_date,
                    'rperiod' => $contract->rperiod,
                    'month' => $contract->month,
                    'deposit' => $contract->deposit,
                    'deposit_sta' => $contract->deposit_sta,
                    'cancel_date' => null,
                    'bdeposit' => null,
                    'notariza' => $contract->notariza,
                    'tax' => $contract->tax,
                    'taxrate' => $contract->taxrate,
                    'rent_payby' => $contract->rent_payby,
                    'remarks' => $contract->remarks,
                    'lessee' => $contract->lessee,
                    'l_tel' => $contract->l_tel,
                    'l_id_card' => $contract->l_id_card,
                    'l_phone' => $contract->l_phone,
                    'l_birthday' => $contract->l_birthday,
                    'l_c_address' => $contract->l_c_address,
                    'l_r_address' => $contract->l_r_address,
                    'gua_name' => $contract->gua_name,
                    'g_tel' => $contract->g_tel,
                    'g_id_card' => $contract->g_id_card,
                    'g_phone' => $contract->g_phone,
                    'g_birthday' => $contract->g_birthday,
                    'g_c_address' => $contract->g_c_address,
                    'g_r_address' => $contract->g_r_address,
                    'house_id' => $contract->house_id,
                    'house_name' => $contract->house_name,
                    'mlease_mprice' => $contract->mlease_mprice,
                    'melectricity' => $contract->melectricity,
                    'mwater' => $contract->mwater,
                    'msewage' => $contract->msewage,
                    'mother' => $contract->mother,
                    'bank_id' => $contract->bank_id,
                    // 'sign_in'=> session("username"),
                ]);
                //將續約對應合約編號寫入續約單號
                DB::table('RE202_6231')
                    ->where('contract_id', $data['data']['contract_id'])
                    ->update([
                        'new_contract_id' => $number,
                    ]);
                //寫入續約編號
                DB::table('RE206_6239')
                    ->where('id', $data['data']['id'])
                    ->update([
                        'new_contract_id' => $number,
                    ]);
            }else{
                //寫入續約編號
                DB::table('RE206_6239')
                    ->where('id', $data['data']['id'])
                    ->update([
                        'new_contract_id' => null,
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
    }

    static public function bfDelete(&$data,$verify = false){
        // dd($data);
        if($verify){
            $page_id = 6262;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['id']);
            $warm = [];
        }else{
            $warm = '';
        }
        //合約對應的房屋有被引用時需跳警告
        $used = CommonController::UsedHouse('RE206_6239',$data['data']['contract_id'],null);
        if($used == 1){
            if($verify){
                $warm[] = "警告：該合約代碼對應的房屋已被引用 \n";
            }else{
                $warm = $warm."警告：該合約代碼對應的房屋已被引用 \n";
            }
        }
        return $warm;
    }

    static public function atDelete(&$data,$verify = false){
        if($verify){
            $page_id = 6262;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['data']['id']);
        }
        //將原房屋租賃合約.[合約狀態 ]更新為「承租中；[押金情況 ]更新為「已收；[退租日期 ]更新為null;[退租金額 ]更新為null;將續約對應合約編號寫入備註
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
            //             'new_contract_id' => null,
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
                'new_contract_id' => null,
                "data_options" => json_encode($dataOptions)
            ]);
        }
        //將新合約刪除
        DB::table('RE202_6231')
                    ->where('contract_id', $data['data']['new_contract_id'])
                    ->delete();
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
