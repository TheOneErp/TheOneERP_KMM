<?php

namespace App\Http\Inject\RE\RE2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;

use App\Utils\VerifyUtil;
use App\Utils\PageUtil;

class RE204 extends InjectBase
{
	static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 6260;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
		$subDataFormId = 6237;
		$tmpArr = [];
		if( $data['status'] == 'update' ){
			$getOldData = DB::table('RE204_6236 as a')->leftJoin('RE204_6237 as b', 'a.id', '=', 'b.parent_id')->where( 'docu_number','=', $data['data']['docu_number'] )->lockForUpdate()->get();
			if( $getOldData ){
				foreach($getOldData as $key=>$value){
					$amount = $value->amount; //繳費
					if( $value->item == "租金" ){
						self::changeRE202('RE202_6232',$value->rdate,$value->r_a_no,$amount,$value->remarks,'sub' );
					}else{
						self::changeRE202('RE202_6233',$value->rdate,$value->r_a_no,$amount,$value->remarks,'sub' );
					}
				}
			}
		}

		$Re202 = DB::table('RE202_6232')->get();
		$Re204 = DB::table('RE202_6233')->get();
		if( array_key_exists('subData',$data) ){
			foreach($data['subData'][$subDataFormId] as $key=>$value){
				if( $value['data']['item'] == "租金" ){
					$pamount = $Re202->where('rdate', $value['data']['rdate'])->where('id', $value['data']['r_a_no'])->pluck('pamount')->first();
				}else{
					$pamount = $Re204->where('rdate', $value['data']['rdate'])->where('id', $value['data']['r_a_no'])->pluck('pamount')->first();
				}
				$rent_rin = $value['data']['rent_rin']; //應繳
				$amount = $value['data']['amount']; //繳費
				if( !is_null($value['data']['rent_rin']) ){
					if( $amount > (float)round($rent_rin - $pamount,2) ){
						if($verify){
							$tmpArr[] = '警告：繳費金額大於需繳金額，請確認';
						}else{
							array_push($tmpArr,[
								"text" => "警告：繳費金額大於需繳金額，請確認"
							]);
						}
					}
				}
			}

			/* $errorMsg["errors"] = $tmpArr;
			if( !empty($errorMsg["errors"]) ){
				response()->json($errorMsg)->send();
				DB::rollback();
				die();
			}else{
				DB::commit();
			} */
		}
		return $tmpArr;
	}
	static public function atSave(&$data,$verify = false){
		if( $verify ){
			$page_id = 6260;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
		$subDataFormId = 6237;
		if( array_key_exists('subData',$data) ){
			foreach($data['subData'][$subDataFormId] as $key=>$value){
				$amount = $value['data']['amount']; //繳費
                // dd($value);
                if(!isset($value['data']['remarks'])){
                    $remarks=null;
                }else{
                    $remarks=$value['data']['remarks'];
                }
				if( $value['data']['item'] == "租金" ){
					self::changeRE202('RE202_6232',$value['data']['rdate'],$value['data']['r_a_no'],$amount,$remarks,'addition' );
				}else{
					self::changeRE202('RE202_6233',$value['data']['rdate'],$value['data']['r_a_no'],$amount,$remarks,'addition' );
				}
			}
		}

	}

	static public function atDelete(&$data,$verify = false){
		if($verify){
			$page_id = 6260;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
		}
		$subDataFormId = 6237;
		if( array_key_exists('subData',$data) ){
			foreach($data['subData'][$subDataFormId] as $key=>$value){
				$rent_rin = $value['data']['rent_rin']; //應繳
				$pamount = $value['data']['pamount']; //已繳
				$amount = $value['data']['amount']; //繳費
				$getId = DB::table('RE202_6231 as a')->where( 'contract_id','=', $value['data']['contract_id'] )->pluck('id')->first();
				if( $value['data']['item'] == "租金" ){
					self::changeRE202('RE202_6232',$value['data']['rdate'],$value['data']['r_a_no'],$amount,null,'sub' );
				}else{
					self::changeRE202('RE202_6233',$value['data']['rdate'],$value['data']['r_a_no'],$amount,null,'sub' );
				}
			}
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
		if( array_key_exists('docu_number',$data['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'docu_number',$data['data']['docu_number']);
			$data['data']['docu_number'] = $number;
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
		self::atDelete($data,true);
    }
    //退回前
      static public function beforeReturnVerify(&$data, &$result){}
    //退回後
    static public function afterReturnVerify(&$data, &$result){}
    //255重置
    static public function afterLastestInitVerify(&$data, &$result){
		self::atDelete($data,true);
    }

	//更動繳費金額
	static public function changeRE202($table,$rdate,$r_a_no,$amount,$remarks,$operation ){
//		DB::beginTransaction();
    	try {
			$getData = DB::table($table)->where('rdate', $rdate)->where('id', $r_a_no)->lockForUpdate()->first();
			if( $operation == 'addition' ){
				$pamount = (float)$getData->pamount + (float)$amount;
				if( $pamount == 0 ){
					$pamount = null;
				}
			}else{
				$pamount = (float)$getData->pamount - (float)$amount;
				if( $pamount == 0 ){
					$pamount = null;
                    $remarks = null;
				}
			}
			if( $table == 'RE202_6232' ){
				$remarkCol = 'pnotes';
			}else{
				$remarkCol = 'remarks';
			}
			DB::table($table)
			->where('rdate', $rdate)
			->where('id', $r_a_no)
			->update([
				'pamount' => $pamount ,
				$remarkCol => $remarks
			]);
	 	}catch (\Throwable $th) {
//			DB::rollback();
		}
//		DB::commit();
	}
}
