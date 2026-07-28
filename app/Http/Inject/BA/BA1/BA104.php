<?php

namespace App\Http\Inject\BA\BA1;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
use App\Http\Inject\EA\EA2\EA201;
use Carbon\Carbon;

//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class BA104 extends InjectBase
{
	static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 6264;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
		$subData = [];
		$deopData = [];
		$tmpArr = [];


		//修改
		if($data['status'] == 'update'){
			$oldBA104data = DB::table('BA104_6241')->where('id', $data['data']['id'])->first();
            $oldBA102data = DB::table('BA102_37')->where('client_code', $oldBA104data->client_code)->first();
			DB::table('BA102_37')
				->where('client_code', $oldBA104data->client_code)
				->update([
					'cnt_balance' => $oldBA102data->cnt_balance - $oldBA104data->cnt_amt,
				]);



        }



		if( $verify ){
			if( empty($tmpArr) ){
				$tmpArr = self::atSave($data,true);
			}
		}
		return $tmpArr;
	}

	static public function atSave(&$data,$verify = false)
	{

		$deopData = [];
        $oldBA102data = DB::table('BA102_37')->where('client_code', $data['data']['client_code'])->first();
			DB::table('BA102_37')
				->where('client_code', $data['data']['client_code'])
				->update([
					'cnt_balance' => $oldBA102data->cnt_balance + $data['data']['cnt_amt'],
				]);
        $tmpArr = [];

		return $tmpArr;
	}

	static public function bfDelete(&$data,$verify = false)
	{
		// dd($data);

        $totalnum = 0;
		$subDataFormId = 53;
		$kegSubId = 72;
		$shipBack = DB::table('BA203_62')->get();
        $warm = "";

		return $warm;
	}

	static public function atDelete(&$data,$verify = false)
	{
        $oldBA102data = DB::table('BA102_37')->where('client_code', $data['data']['client_code'])->first();
			DB::table('BA102_37')
				->where('client_code', $data['data']['client_code'])
				->update([
					'cnt_balance' => $oldBA102data->cnt_balance - $data['data']['cnt_amt'],
				]);


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
		if( array_key_exists('ship_code',$data['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'ship_code',$data['data']['ship_code']);
			$data['data']['ship_code'] = $number;
		}
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
    {}
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
			// self::atSave($data);
			DB::beginTransaction();
			$tmpArr = self::atSave($data);
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
