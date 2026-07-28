<?php

namespace App\Http\Inject\EA\EA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class EA206 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 1070;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        $tmpArr = [];
		$subDataFormId = 1070;
        if($data['status'] == 'update'){
            $oldbodydata = DB::table('EA206_1070')->select('*')->where('parent_id', $data['data']['id'])->get();
            foreach($oldbodydata as $key => $val){
                $oldnum = DB::table('EA204_79')->where('product_code',$val->product_code)->where('depot_code',$val->depot_code)->pluck('num');
                if(count($oldnum)>0){
                    $newnum = $oldnum[0] - $val->body_num;
                    DB::table('EA204_79')
						->where('product_code',$val->product_code)
						->where('depot_code',$val->depot_code)
						->update(['num' => $newnum]);
                }
                $kegoldnum = DB::table('EA205_80')->where('keg',$val->keg)->lockForUpdate()->first();
                if(!empty($kegoldnum)){
                    if($kegoldnum->product_code == $val->product_code || $kegoldnum->product_code == null){
                        $kegnewnum = $kegoldnum->num - $val->body_num;
                        if($kegnewnum == 0){
                            DB::table('EA205_80')
                                    ->where('keg',$val->keg)
                                    ->update([
                                        'product_code' => '',
                                        'product_name' => '',
                                        'num' => $kegnewnum,
                                        'unit_name' => '',
                                        'unit_code' => '',
                                    ]);
                        }else if( $kegnewnum < 0 ){
							
                            if($verify){
                                $tmpArr[] = '警告：桶號為"'.$val->keg.'" ，數量不足，請確認';
                            }else{
                                array_push($tmpArr,[
                                    "text" => '警告：桶號為"'.$val->keg.'" ，數量不足，請確認'
                                ]);
                            }	
						}else{
                            $aa202data = DB::table('AA202_30')
                                     ->select('*')
                                     ->where('product_code', $val->product_code)
                                     ->get();
                            DB::table('EA205_80')
                                    ->where('keg',$val->keg)
                                    ->update([
                                        'product_code' => $val->product_code,
                                        'product_name' => $val->product_name,
                                        'num' => $kegnewnum,
                                        'unit_name' => $aa202data[0]->unit_name,
                                        'unit_code' => $aa202data[0]->unit_code,
                                    ]);
                        }
                    }else{
                        if($verify){
                            $tmpArr[] = '警告：桶號為"'.$val->keg.'" ，已放置其他產品，請確認';
                        }else{
                            array_push($tmpArr,[
                                "text" => '警告：桶號為"'.$val->keg.'" ，已放置其他產品，請確認'
                            ]);
                        }	
                        
                    }
                }
            }
            //刪除庫存調整單資料
            $adjustdata = DB::table('EA201_50')
                     ->where('source_code', $data['data']['keg_code'])
                     ->pluck('id');
            if(count($adjustdata)>0){
                DB::table('EA201_51')->where('parent_id', $adjustdata[0])->delete();
                DB::table('EA201_50')->where('source_code', $data['data']['keg_code'])->delete();
            } 
        }
		//為了檢察有負數會超過的狀況
		$kegTemp = [];
		foreach($data['subData'][$subDataFormId] as $key=>$value){
			$kegCode = $value['data']['keg'];
			if( array_key_exists($kegCode,$kegTemp) ){
				$productNum = is_null($value['data']['body_num'])?0:$value['data']['body_num'];
				$kegTemp[$kegCode]['body_num'] = (float)$productNum + (float)$kegTemp[$kegCode]['body_num']; 
			}else{
				$kegTemp[$kegCode] = array(
					'body_num'=>$value['data']['body_num'],
				);
			}
		}
		if( !empty($kegTemp) ){
			foreach($kegTemp as $kegKey=>$kegValue){
				$KegData = DB::table('EA205_80')->where('keg','=',$kegKey)->lockForUpdate()->first();
				if( $kegKey ){
					$KegNum = $KegData->num;
					if( (float)$kegValue['body_num'] + (float)$KegNum < 0 ){
                        if($verify){
                            $tmpArr[] = '警告：桶號為"'.$kegKey.'" ，桶號庫存不足，請確認';
                        }else{
                            array_push($tmpArr,[
                                "text" => '警告：桶號為"'.$kegKey.'" ，桶號庫存不足，請確認'
                            ]);
                        }	
					}
				}
			}
        }
        if( $verify ){
            if( empty($tmpArr) ){
                self::atSave($data,true);
            }
        }
        return $tmpArr;
    }
    static public function atSave(&$data,$verify = false)
	{
		if( !$verify ){
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        $samedepot = [];
        foreach($data['subData'][1070] as $key => $val){
            //增加或減少分倉數量
            $oldnum = DB::table('EA204_79')
                    ->where('product_code',$data['subData'][1070][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][1070][$key]['data']['depot_code'])
                    ->pluck('num');
//            dd(count($oldnum));
            if(count($oldnum)>0){
                $newnum = $oldnum[0] + $data['subData'][1070][$key]['data']['body_num'];
                DB::table('EA204_79')
                        ->where('product_code',$data['subData'][1070][$key]['data']['product_code'])
                        ->where('depot_code', $data['subData'][1070][$key]['data']['depot_code'])
                        ->update(['num' => $newnum]);
            }
            
            //增加或減少桶號庫存數量
            $kegoldnums = DB::table('EA205_80')->where('keg',$data['subData'][1070][$key]['data']['keg'])->lockForUpdate()->first();
            $kegoldnum =$kegoldnums->num;
            $kegnewnum = $kegoldnum + $data['subData'][1070][$key]['data']['body_num'];
//            dd($kegnewnum);
            if($kegnewnum == 0){
                DB::table('EA205_80')
                        ->where('keg',$data['subData'][1070][$key]['data']['keg'])
                        ->update([
                            'product_code' => '',
                            'product_name' => '',
                            'num' => $kegnewnum,
                            'unit_name' => '',
                            'unit_code' => '',
                        ]);
            }else{
                $aa202data = DB::table('AA202_30')
                         ->select('*')
                         ->where('product_code', $data['subData'][1070][$key]['data']['product_code'])
                         ->get();
                DB::table('EA205_80')
                        ->where('keg',$data['subData'][1070][$key]['data']['keg'])
                        ->update([
                            'product_code' => $data['subData'][1070][$key]['data']['product_code'],
                            'product_name' => $data['subData'][1070][$key]['data']['product_name'],
                            'num' => $kegnewnum,
                            'unit_name' => $aa202data[0]->unit_name,
                            'unit_code' => $aa202data[0]->unit_code,
                        ]);
            }
            
            //整理相同倉庫資料
            if( array_key_exists($data['subData'][1070][$key]['data']['depot_code'],$samedepot) ){	
                array_push($samedepot[$data['subData'][1070][$key]['data']['depot_code']],$data['subData'][1070][$key]['data']);

            }else{
                $samedepot[$data['subData'][1070][$key]['data']['depot_code']] = [];
                array_push($samedepot[$data['subData'][1070][$key]['data']['depot_code']],$data['subData'][1070][$key]['data']);
            }
            
        }
        //轉出庫存調整單
        if( $verify ){
            $dataOptions = [
                'deletable' => false,
                'editable' => false,
                'cloneable' => false,
                'verify'=>[
                    'level'=>255
                ]
            ];
        }else{
            $dataOptions = [
                'deletable' => false,
                'editable' => false,
                'cloneable' => false,
            ];
        }        
        foreach($samedepot as $depotkey => $depotval){
            $pageId = '58';
            $number = CommonController::generateDocumentNumber($pageId,'adjust_code');
            DB::table('EA201_50')
                ->insert([
                    'adjust_code' => $number,
                    'undertakerday' => $data['data']['undertakerday'],
                    'undertaker' => 'SYSTEM',
                    'undertakername' => 'SYSTEM',
                    'depot_code' => $depotkey,
                    'depot_name' => $samedepot[$depotkey][0]['depot_name'],
                    'remarks' => $data['data']['remarks'],
                    'source_code' => $data['data']['keg_code'],
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                    'data_options' => json_encode($dataOptions)
                    // 'data_options' => '{"editable":false,"deletable":false,"cloneable":false}',
                ]);
            $headdata = DB::table('EA201_50')
                     ->where('adjust_code', $number)
                     ->pluck('id');
            foreach($samedepot[$depotkey] as $itemkey => $itemval){
                //var_dump($samedepot[$depotkey][0]['product_code']);
                DB::table('EA201_51')
                    ->insert([
                        'parent_id' => $headdata[0],
                        'product_code' => $samedepot[$depotkey][$itemkey]['product_code'],
                        'product_name' => $samedepot[$depotkey][$itemkey]['product_name'],
                        'body_num' => $samedepot[$depotkey][$itemkey]['body_num'],
                        'unit_code' => $samedepot[$depotkey][$itemkey]['unit_code'],
                        'unit_name' => $samedepot[$depotkey][$itemkey]['unit_name'],
                        'body_rate' => '1',
                        'body_remarks' => $samedepot[$depotkey][$itemkey]['body_remarks'],
                    ]);
            }
        }
    }
    static public function bfDelete(&$data,$verify = false)
	{
		if($verify){
			$page_id = 1070;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$warm = [];
		}else{
			$warm = '';
        }
        foreach($data['subData'][1070] as $key => $val){
            $kegoldnum = DB::table('EA205_80')
                        ->select('*')
                        ->where('keg',$data['subData'][1070][$key]['data']['keg'])
                        ->get();
            if(($kegoldnum[0]->product_code != '' || $kegoldnum[0]->product_code != null) && $kegoldnum[0]->product_code != $data['subData'][1070][$key]['data']['product_code']){
                // response()->json(['status' => false , 'message' => '桶號：'.$data['subData'][1070][$key]['data']['keg'].'已放置其他產品，此筆將留作紀錄無法刪除'])->send();
                // DB::rollback(); 
                // die();
                if($verify){
                    $warm[] = "桶號：{$data['subData'][1070][$key]['data']['keg']}已放置其他產品，此筆將留作紀錄無法刪除";
                }else{
                    $warm = $warm . "桶號：{$data['subData'][1070][$key]['data']['keg']}已放置其他產品，此筆將留作紀錄無法刪除\n";
                }
            }
        }
        return $warm;
    }
    static public function atDelete(&$data,$verify = false)
	{
		if($verify){
			$page_id = 1070;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
            $data = PageUtil::getData($pageData, $data['data']['id']);
        }
        foreach($data['subData'][1070] as $key => $val){
            //刪除分倉數量
            $oldnum = DB::table('EA204_79')
                    ->where('product_code',$data['subData'][1070][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][1070][$key]['data']['depot_code'])
                    ->pluck('num');
            if(count($oldnum)>0){
                $newnum = $oldnum[0] - $data['subData'][1070][$key]['data']['body_num'];
                DB::table('EA204_79')
                        ->where('product_code',$data['subData'][1070][$key]['data']['product_code'])
                        ->where('depot_code', $data['subData'][1070][$key]['data']['depot_code'])
                        ->update(['num' => $newnum]);
            }
            
            //刪除桶號數量
            $kegoldnum = DB::table('EA205_80')
                    ->where('keg',$data['subData'][1070][$key]['data']['keg'])
                    ->pluck('num');
            $kegnewnum = $kegoldnum[0] - $data['subData'][1070][$key]['data']['body_num'];
            if($kegnewnum == 0){
                DB::table('EA205_80')
                        ->where('keg',$data['subData'][1070][$key]['data']['keg'])
                        ->update([
                            'product_code' => '',
                            'product_name' => '',
                            'num' => $kegnewnum,
                            'unit_name' => '',
                            'unit_code' => '',
                        ]);
            }else{
                $aa202data = DB::table('AA202_30')
                         ->select('*')
                         ->where('product_code', $data['subData'][1070][$key]['data']['product_code'])
                         ->get();
                DB::table('EA205_80')
                        ->where('keg',$data['subData'][1070][$key]['data']['keg'])
                        ->update([
                            'product_code' => $data['subData'][1070][$key]['data']['product_code'],
                            'product_name' => $data['subData'][1070][$key]['data']['product_name'],
                            'num' => $kegnewnum,
                            'unit_name' => $aa202data[0]->unit_name,
                            'unit_code' => $aa202data[0]->unit_code,
                        ]);
            }
        }
        //刪除庫存調整單資料
        $adjustdata = DB::table('EA201_50')
                     ->where('source_code', $data['data']['keg_code'])
                     ->pluck('id');
        if(count($adjustdata)>0){
            DB::table('EA201_51')->where('parent_id', $adjustdata[0])->delete();
            DB::table('EA201_50')->where('source_code', $data['data']['keg_code'])->delete();
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
    {
    }
    static public function afterDatasetValidationSuccess(&$dataset, &$schema, &$rules, &$validationResult, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
		if( array_key_exists('keg_code',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'keg_code',$dataset["data"]["keg_code"]);
        	$dataset['data']['keg_code'] = $number;
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
