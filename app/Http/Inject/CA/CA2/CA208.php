<?php

namespace App\Http\Inject\CA\CA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
use Carbon\Carbon;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class CA208 extends InjectBase
{
	static public function bfSave(&$data,$verify = false){
        // dd($data['status']);
        $subDataFormId = 4227;
        $tmpArr = [];
        $check=false;
        if($data['status'] == 'add'){
            if( array_key_exists('subData',$data) ){
                foreach($data['subData'][$subDataFormId] as $key=>$value){
                    if($value['data']['choose'] == '1'){
                        if(!$check){
                            $check=true;
                        }
                    }
                }
            }
        }else{
            $check=true;
        }
        if(!$check){
            if($verify){
                $tmpArr[] = "請先勾選需沖帳項目，再進行沖帳。";
            }else{
                array_push($tmpArr,[
                    "text" =>"請先勾選需沖帳項目，再進行沖帳。"
                ]);
            }
        }
        return $tmpArr;
    }

	//end of bfSave
	static public function atSave(&$data,$verify = false)
	{
        $subDataFormId = 4227;
        $h_pmt_date=$data["data"]["h_pmt_date"];
        $pageId = 4248;
        $tmpArr = [];


        if($data['status'] == 'add'){
            foreach($data['subData'][$subDataFormId] as $datakey => $value){

                if($value['data']['choose'] == '1'){
                    $row = $value['data']['source_code'];
                    if (strpos($row, 'CA203') !== false) {
                        //如果未來要沖帳進貨退出單才需要寫的，預留
                    }else{
                        $HeadData = DB::table("CA202_54 as a")
                            ->where('receive_code', '=', $row)
                            ->first();
                        $SubData = DB::table("CA202_55 as b")
                            ->select(DB::raw("b.id"))
                            ->leftJoin('CA202_54 as a', 'a.id', '=', 'b.parent_id')
                            ->where('receive_code', '=', $row)
                            ->where('pay_status', '!=', "已付款")
                            ->get();
                        $CA102Data = DB::table("CA102_27 as a")
                            ->where('vendor_code', '=', $HeadData->vendor_code)
                            ->first();

                        if($HeadData->ototal > ($HeadData->amt_paid + $value['data']['paid']  + $HeadData->amt_discount + $value['data']['amt_discount'])){
                            //如果該筆沒有沖完整

                            $amt_paid = $HeadData->amt_paid + $value['data']['paid'];
                            $amt_discount = $HeadData->amt_discount + $value['data']['amt_discount'];
                            $amt_unpaid = $HeadData->ototal - ($HeadData->amt_paid + $value['data']['paid']) - ($HeadData->amt_discount + $value['data']['amt_discount']);
                   

                            DB::table("CA202_54")
                                ->where('receive_code', '=', $row)
                                ->update([
                                    'h_pmt_date' => $h_pmt_date,
                                    'amt_paid' => $amt_paid,     
                                    'amt_discount' => $amt_discount,
                                    'amt_unpaid' => $amt_unpaid,
                                ]);

                   
                        }else{
                            //如果該筆已沖完整

                            $amt_paid = $HeadData->amt_paid + $value['data']['paid'] ;
                            $amt_discount = $HeadData->amt_discount + $value['data']['amt_discount'];
                            $amt_unpaid = $HeadData->ototal - ($HeadData->amt_paid + $value['data']['paid'] ) - ($HeadData->amt_discount + $value['data']['amt_discount']);
                        

                            DB::table("CA202_54")
                                ->where('receive_code', '=', $row)
                                ->update([
                                    'h_pmt_date' => $h_pmt_date,
                                    'amt_paid' => $amt_paid,     
                                    'amt_discount' => $amt_discount,
                                    'amt_unpaid' => $amt_unpaid,
                                ]);

                            foreach($SubData as $key1=>$row1 ){
                                DB::table("CA202_55 as a")
                                    ->leftJoin('CA202_54 as b', 'b.id', '=', 'a.parent_id')
                                    ->where('a.id', '=', $row1->id)
                                    ->update([
                                        'pay_status' => "已付款",
                                        'b_pmt_date' => $h_pmt_date,
                                    ]);
                            }
                            
            
                        }


                    }
                }
            }
        }
        return $tmpArr;
	}
	static public function bfDelete(&$data,$verify = false)
	{
       
	}
	static public function atDelete(&$data,$verify = false)
	{
        if($verify){
			$page_id = 4248;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
		}
        $subDataFormId = 4227;
       
        if( array_key_exists('subData',$data) ){
            foreach($data['subData'][$subDataFormId] as $datakey => $value){
                if($value['data']['choose'] == '1'){
                    $row = $value['data']['source_code'];
                    if (strpos($row, 'CA203') !== false) {
                        //如果未來要沖帳進貨退出單才需要寫的，預留
                    }else{
                        $HeadData = DB::table("CA202_54 as a")
                            ->where('receive_code', '=', $row)
                            ->first();
                        $id = $HeadData->id;
                        $SubData = DB::table("CA202_55 as b")
                            ->select(DB::raw("b.id"))
                            ->leftJoin('CA202_54 as a', 'a.id', '=', 'b.parent_id')
                            ->where('b.parent_id', '=', $id)
                            ->get();
                        $checkData = DB::table("CA208_4227 as a")
                            ->where('source_code', '=', $row)
                            ->where('choose', '=', '1')
                            ->count();
                        $CA208data = DB::table("CA208_4227 as b")
                            ->leftJoin('CA208_4226 as a', 'a.id', '=', 'b.parent_id')
                            ->where('source_code', '=', $row)
                            ->where('choose', '=', '1')
                            ->orderby('a.id','desc')
                            ->first();
                        $CA102Data = DB::table("CA102_27 as a")
                            ->where('vendor_code', '=', $HeadData->vendor_code)
                            ->first();

                        $amt_paid = $HeadData->amt_paid - $value['data']['paid'];
                        $amt_discount = $HeadData->amt_discount - $value['data']['amt_discount'];
                        $amt_unpaid = $HeadData->ototal - ($HeadData->amt_paid - $value['data']['paid']) - ($HeadData->amt_discount - $value['data']['amt_discount']);
                       


                        DB::table("CA202_54")
                            ->where('receive_code', '=', $row)
                            ->update([
                                'h_pmt_date' => null,
                                'amt_paid' => $amt_paid,     
                                'amt_discount' => $amt_discount,
                                'amt_unpaid' => $amt_unpaid,
                            ]);

                            foreach($SubData as $key1=>$row1 ){
                                DB::table("CA202_55 as a")
                                    ->leftJoin('CA202_54 as b', 'b.id', '=', 'a.parent_id')
                                    ->where('a.id', '=', $row1->id)
                                    ->update([
                                        'pay_status' => "未付款",
                                        'b_pmt_date' => null,
                                    ]);
                            }

                     

                    }
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
        //dd($dataset['data']);
		$pageId = $pageData['page']['page_id'];
		if( array_key_exists('docu_number',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'docu_number',$dataset['data']['docu_number']);
			$dataset['data']['docu_number'] = $number;
		}

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

	}
	static public function afterFailedExecuteVerify(&$data, &$result){}
	//從255退回
	static public function afterLastestReturnVerify(&$data, &$result){

	}
	//退回前
    static public function beforeReturnVerify(&$data, &$result){}
	//退回後
	static public function afterReturnVerify(&$data, &$result){}

	static public function afterLastestInitVerify(&$data, &$result){

		}
	}

