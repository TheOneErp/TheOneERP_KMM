<?php

namespace App\Http\Inject\BA\BA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
use Carbon\Carbon;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class BA208 extends InjectBase
{
	static public function bfSave(&$data,$verify = false){
        // dd($data['status']);
        $subDataFormId = 4225;
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
        $subDataFormId = 4225;
        $h_pmt_date=$data["data"]["h_pmt_date"];
        $pageId = 4247;
        $tmpArr = [];


        if($data['status'] == 'add'){
            foreach($data['subData'][$subDataFormId] as $datakey => $value){

                if($value['data']['choose'] == '1'){
                    $row = $value['data']['source_code'];
                    if (strpos($row, 'BA203') !== false) {
                        //如果未來要沖帳出貨退回單才需要寫的，預留
                    }else{
                        $HeadData = DB::table("BA202_52 as a")
                            ->where('ship_code', '=', $row)
                            ->first();
                        $SubData = DB::table("BA202_53 as b")
                            ->select(DB::raw("b.id"))
                            ->leftJoin('BA202_52 as a', 'a.id', '=', 'b.parent_id')
                            ->where('ship_code', '=', $row)
                            ->where('payment_status', '!=', "已收款")
                            ->get();
  

                        if($HeadData->ototal > ($HeadData->amt_recd + $value['data']['paid'] + $HeadData->amt_discount + $value['data']['amt_discount'])){
                            //如果該筆沒有沖完整

                            $amt_recd = $HeadData->amt_recd + $value['data']['paid'];
                            $amt_discount = $HeadData->amt_discount + $value['data']['amt_discount'];
                            $amt_outstanding = $HeadData->ototal - ($HeadData->amt_recd + $value['data']['paid'] ) - ($HeadData->amt_discount + $value['data']['amt_discount']);
                

                            DB::table("BA202_52")
                                ->where('ship_code', '=', $row)
                                ->update([
                                    'h_pmt_date' => $h_pmt_date,
                                    'amt_recd' => $amt_recd,     
                                    'amt_discount' => $amt_discount,
                                    'amt_outstanding' => $amt_outstanding,
                                ]);

                        }else{
                            //如果該筆已沖完整

                            $amt_recd = $HeadData->amt_recd + $value['data']['paid'];
                            $amt_discount = $HeadData->amt_discount + $value['data']['amt_discount'];
                            $amt_outstanding = $HeadData->ototal - ($HeadData->amt_recd + $value['data']['paid'] ) - ($HeadData->amt_discount + $value['data']['amt_discount']);
          
                            DB::table("BA202_52")
                                ->where('ship_code', '=', $row)
                                ->update([
                                    'h_pmt_date' => $h_pmt_date,
                                    'amt_recd' => $amt_recd,     
                                    'amt_discount' => $amt_discount,
                                    'amt_outstanding' => $amt_outstanding,
                                ]);

                            foreach($SubData as $key1=>$row1 ){
                                DB::table("BA202_53 as a")
                                    ->leftJoin('BA202_52 as b', 'b.id', '=', 'a.parent_id')
                                    ->where('a.id', '=', $row1->id)
                                    ->update([
                                        'payment_status' => "已收款",
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
			$page_id = 4247;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
		}
        $subDataFormId = 4225;
        $date=$data["data"]["h_pmt_date"];
        if( array_key_exists('subData',$data) ){
            foreach($data['subData'][$subDataFormId] as $datakey => $value){
                if($value['data']['choose'] == '1'){
                    $row = $value['data']['source_code'];
                  
                    if (strpos($row, 'BA203') !== false) {
                        //如果未來要沖帳進貨退出單才需要寫的，預留
                    }else{
                        $HeadData = DB::table("BA202_52 as a")
                            ->where('ship_code', '=', $row)
                            ->first();
                        $id = $HeadData->id;
                        $SubData = DB::table("BA202_53 as b")
                            ->select(DB::raw("b.id"))
                            ->leftJoin('BA202_52 as a', 'a.id', '=', 'b.parent_id')
                            ->where('b.parent_id', '=', $id )
                            ->get();
                        $checkData = DB::table("BA208_4225 as a")
                            ->where('source_code', '=', $row)
                            ->where('choose', '=', '1')
                            ->count();
                        $BA208data = DB::table("BA208_4225 as b")
                            ->leftJoin('BA208_4224 as a', 'a.id', '=', 'b.parent_id')
                            ->where('source_code', '=', $row)
                            ->where('choose', '=', '1')
                            ->orderby('a.id','desc')
                            ->first();
                        $BA102Data = DB::table("BA102_37 as a")
                            ->where('client_code', '=', $HeadData->client_code)
                            ->first();

                        $amt_recd = $HeadData->amt_recd - $value['data']['paid'] ;
                        $amt_discount = $HeadData->amt_discount - $value['data']['amt_discount'];
                        $amt_outstanding = $HeadData->ototal - ($HeadData->amt_recd - $value['data']['paid']) - ($HeadData->amt_discount - $value['data']['amt_discount']);
    

                        if($checkData == 0){
                            $h_pmt_date = null;
                            $amt_outstanding = 0;
                            if($amt_discount == 0){
                                $amt_discount = null;
                            }
                        }else{
                            $h_pmt_date = $BA208data->h_pmt_date;
                        }

                        DB::table("BA202_52")
                            ->where('ship_code', '=', $row)
                            ->update([
                                'h_pmt_date' => null,
                                'amt_recd' => $amt_recd,     
                                'amt_discount' => $amt_discount,
                                'amt_outstanding' => $amt_outstanding,
                            ]);

                            foreach($SubData as $key1=>$row1 ){
                                DB::table("BA202_53 as a")
                                    ->leftJoin('BA202_52 as b', 'b.id', '=', 'a.parent_id')
                                    ->where('a.id', '=', $row1->id)
                                    ->update([
                                        'payment_status' => "未收款",
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

