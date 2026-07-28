<?php

namespace App\Http\Inject\EA\EA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class EA202 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 62;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        $tmpArr = [];
        if($data['status'] == 'update'){
            $olddata = DB::table('EA202_59')->select('*')->where('transfer_code', $data['data']['transfer_code'])->get();
            $oldbodydata = DB::table('EA202_60')->select('*')->where('parent_id', $olddata[0]->id)->get();
            $totalnum = 0;
            foreach($oldbodydata as $key => $val){
                $totalnum = $val->body_num*$val->body_rate;
                $inoldnum = DB::table('EA204_79')
                        ->where('product_code', $val->product_code)
                        ->where('depot_code', $olddata[0]->transfer_depot_code)
                        ->pluck('num');
                $innewnum = $inoldnum[0] - $totalnum;
                DB::table('EA204_79')
                        ->where('product_code', $val->product_code)
                        ->where('depot_code', $olddata[0]->transfer_depot_code)
                        ->update(['num' => $innewnum]);
                $outoldnum = DB::table('EA204_79')
                        ->where('product_code', $val->product_code)
                        ->where('depot_code', $val->body_depot_code)
                        ->pluck('num');
                if(count($outoldnum)>0){
                    $outnewnum = $outoldnum[0] + $totalnum;
                    DB::table('EA204_79')
                            ->where('product_code', $val->product_code)
                            ->where('depot_code', $val->body_depot_code)
                            ->update(['num' => $outnewnum]);
                }
            }
        }
        if( $verify ){
			$tmpArr = self::atSave($data,true);
		}
		return $tmpArr;        
    }
    static public function atSave(&$data,$verify = false)
	{
		if( !$verify ){
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        $totalnum = 0;
        $errorarr = [];
        foreach($data['subData'][60] as $key => $val){
            $totalnum = $data['subData'][60][$key]['data']['body_num']*$data['subData'][60][$key]['data']['body_rate']; //數量*換算率
            $depotdata = DB::table('EA204_79')
                 ->select('product_code','depot_code','depot_name','num')
                 ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                 ->where('depot_code', $data['subData'][60][$key]['data']['body_depot_code'])
                 ->get();
            if(count($depotdata)>0){
                //表身數量*換算率大於庫存時，跳出提示
                if($totalnum > $depotdata[0]->num){
                    if($verify){
                        $errorarr[] = '警告：產品代碼為 "'.$data['subData'][60][$key]['data']['product_code'].'" ，分倉庫存不足，請確認';
                    }else{
                        array_push($errorarr,[
                            "text"=>'警告：產品代碼為 "'.$data['subData'][60][$key]['data']['product_code'].'" ，分倉庫存不足，請確認'
                        ]);
                    }	
                }else{
                    $inoldnum = DB::table('EA204_79')
                            ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                            ->where('depot_code', $data['data']['transfer_depot_code'])
                            ->pluck('num');
                    $innewnum = $inoldnum[0] + $totalnum;
                    DB::table('EA204_79')
                            ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                            ->where('depot_code', $data['data']['transfer_depot_code'])
                            ->update(['num' => $innewnum]);
                    $outoldnum = DB::table('EA204_79')
                            ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                            ->where('depot_code', $data['subData'][60][$key]['data']['body_depot_code'])
                            ->pluck('num');
                    $outnewnum = $outoldnum[0] - $totalnum;
                    DB::table('EA204_79')
                            ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                            ->where('depot_code', $data['subData'][60][$key]['data']['body_depot_code'])
                            ->update(['num' => $outnewnum]);
                }
            }
        }
        return $errorarr;
    }
    static public function bfDelete(&$data,$verify = false)
	{
		// dd($data);
		if($verify){
			$page_id = 62;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$warm = [];
		}else{
			$warm = '';
        }
        $totalnum = 0;
        foreach($data['subData'][60] as $key => $val){
            $totalnum = $data['subData'][60][$key]['data']['body_num']*$data['subData'][60][$key]['data']['body_rate']; //數量*換算率
            $depotdata = DB::table('EA204_79')
                 ->select('product_code','depot_code','depot_name','num')
                 ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                 ->where('depot_code', $data['data']['transfer_depot_code'])
                 ->get();
            //表身數量*換算率大於庫存時，跳出提示
            if($totalnum > $depotdata[0]->num){
                if($verify){
                    $warm[] = "警告：產品代碼為{$data['subData'][60][$key]['data']['product_code']}，分倉庫存不足，故無法刪除";
                }else{
                    $warm = $warm . "警告：產品代碼為{$data['subData'][60][$key]['data']['product_code']}，分倉庫存不足，故無法刪除 \n";
                }
            }
        }
        return $warm;
    }
    static public function atDelete(&$data,$verify = false)
	{
		if($verify){
			$page_id = 62;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
        }
        $totalnum = 0;
        foreach($data['subData'][60] as $key => $val){
            $totalnum = $data['subData'][60][$key]['data']['body_num']*$data['subData'][60][$key]['data']['body_rate']; //數量*換算率
            $inoldnum = DB::table('EA204_79')
                    ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                    ->where('depot_code', $data['data']['transfer_depot_code'])
                    ->pluck('num');
            $innewnum = $inoldnum[0] - $totalnum;
            DB::table('EA204_79')
                    ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                    ->where('depot_code', $data['data']['transfer_depot_code'])
                    ->update(['num' => $innewnum]);
            $outoldnum = DB::table('EA204_79')
                    ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][60][$key]['data']['body_depot_code'])
                    ->pluck('num');
            $outnewnum = $outoldnum[0] + $totalnum;
            DB::table('EA204_79')
                    ->where('product_code', $data['subData'][60][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][60][$key]['data']['body_depot_code'])
                    ->update(['num' => $outnewnum]);
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
		if( array_key_exists('transfer_code',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'transfer_code',$dataset["data"]["transfer_code"]);
        	$dataset['data']['transfer_code'] = $number;
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
	//執行審核前 $result = array() boolen message//
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
