<?php

namespace App\Http\Inject\RE\RE2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;

use App\Utils\VerifyUtil;
use App\Utils\PageUtil;

use Carbon\Carbon;

class RE203 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
        if( !array_key_exists('status',$data) ){
            $page_id = 6259;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['id']);
            $data['status'] = 'add';
        }else{
            $data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
    }

    static public function atSave(&$data,$verify = false){
        if( $verify ){
            $page_id = 6259;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['data']['id']);
            $data['status'] = 'add';
        }else{
            $data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }

        $subDataFormId = 6235;
		if( array_key_exists('subData',$data) ){
			$getContractData = DB::table('RE202_6231 as a')->select(DB::raw("contract_id,docu_number,RE203_bodyno,b.parent_id"))->leftJoin('RE202_6233 as b', 'a.id', '=', 'b.parent_id');
            if (VerifyUtil::pageVerifyConfirmation(6258)) {
                $getContractData = $getContractData->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
            }
            $getContractData = $getContractData->get();
			if($data['status'] == 'update'){
				$idArr = [];
				foreach($data['subData'][$subDataFormId] as $datakey => $dataval){
					//需要被新增的
					$getRE202_6233 = $getContractData->where('contract_id', '=',  $dataval['data']['contract_id'] )->where('docu_number', '=',  $data['data']['docu_number'] )->pluck('RE203_bodyno')->all();

					$parent_id = $getContractData->where('contract_id', '=',  $dataval['data']['contract_id'] )->where('docu_number', '=',  $data['data']['docu_number'] )->pluck('parent_id')->first();
					if( !empty($getRE202_6233) && !in_array($dataval['data']['id'], $getRE202_6233)){
						DB::table('RE202_6233')
						->insert([
							'RE203_bodyno' =>$dataval['data']['id'],
							'parent_id' =>$parent_id,
							'rdate' => $dataval['data']['rdate'] ,//應收日期
							'item_id' => $dataval['data']['item_id'] ,//項目代碼
							'item_name' => $dataval['data']['item_name'] ,//項目名稱
							'rent_rout' => $dataval['data']['rent_rout'] ,//應收未稅
							'rtax' => $dataval['data']['rtax'] ,//稅
							'rent_rin' => $dataval['data']['rent_rin'] ,//應收含稅
							'pamount' => 0,
							'docu_number' => $data['data']['docu_number'],
							'login_date' => $data['data']['undertakerday'],
							'created_by'=> session("user_id"),
							'updated_by'=> session("user_id"),
                            'remarks' => $dataval['data']['remarks']
						]);
					}else{//需要被修改的
						DB::table('RE202_6233')
						->where('parent_id', $parent_id)
						->where('RE203_bodyno', $dataval['data']['id'])
						->update([
							'rdate' => $dataval['data']['rdate'] ,//應收日期
							'item_id' => $dataval['data']['item_id'] ,//項目代碼
							'item_name' => $dataval['data']['item_name'] ,//項目名稱
							'rent_rout' => $dataval['data']['rent_rout'] ,//應收未稅
							'rtax' => $dataval['data']['rtax'] ,//稅
							'rent_rin' => $dataval['data']['rent_rin'] ,//應收含稅
							'login_date' => $data['data']['undertakerday'],
							'pamount' => 0,
							'updated_by'=> session("user_id"),
                            'remarks' => $dataval['data']['remarks']
						]);
					}
					//整理需要被刪除的
					$idArr[] = $dataval['data']['id'];
				}
				//需要刪除的
				if( !empty($idArr) ){
					DB::table('RE202_6233')->where('docu_number','=',$data['data']['docu_number'])->whereNotIn('RE203_bodyno', $idArr)->delete();
				}
			}else{
				foreach($data['subData'][$subDataFormId] as $datakey => $dataval){
					$getContractID = $getContractData->where('contract_id','=',$dataval['data']['contract_id'])->pluck('parent_id')->first();
					DB::table('RE202_6233')
					->insert([
						'RE203_bodyno' =>$dataval['data']['id'],
						'parent_id' =>$getContractID,
						'rdate' => $dataval['data']['rdate'] ,//應收日期
						'item_id' => $dataval['data']['item_id'] ,//項目代碼
						'item_name' => $dataval['data']['item_name'] ,//項目名稱
						'rent_rout' => $dataval['data']['rent_rout'] ,//應收未稅
						'rtax' => $dataval['data']['rtax'] ,//稅
						'rent_rin' => $dataval['data']['rent_rin'] ,//應收含稅
						'pamount' => 0,
						'docu_number' => $data['data']['docu_number'],
						'login_date' => $data['data']['undertakerday'],
						'created_by'=> session("user_id"),
						'updated_by'=> session("user_id"),
                        'remarks' => $dataval['data']['remarks']
					]);
				}
			}

		}
    }

    static public function bfDelete(&$data,$verify = false){
        // dd($data);
        if($verify){
            $page_id = 6259;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['id']);
            $warm = [];
        }else{
            $warm = '';
        }
        $getDetailData = DB::table('RE202_6233')->where('docu_number','=',$data['data']['docu_number'])->where('pamount','>','0')->get();
        if( $getDetailData->count() != 0 ){
            if($verify){
                $warm[] = "警告：本表單已有繳費紀錄。 \n";
            }else{
                $warm = $warm."警告：本表單已有繳費紀錄。 \n";
            }
        }

        return $warm;
    }

    static public function atDelete(&$data,$verify = false){
        if($verify){
            $page_id = 6259;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['data']['id']);
        }
        DB::table('RE202_6233')->where('docu_number', '=',  $data['data']['docu_number'] )->delete();
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
        if( array_key_exists('docu_number',$data['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'docu_number',$data['data']['docu_number']);
			$data['data']['docu_number'] = $number;
		}
        if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
            self::bfSave($data);
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
        self::bfSave($data,true);
        self::atSave($data);
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
