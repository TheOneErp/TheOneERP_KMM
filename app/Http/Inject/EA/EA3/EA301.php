<?php

namespace App\Http\Inject\EA\EA3;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class EA301 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
        $tmpArr = [];
		if( !array_key_exists('status',$data) ){
			$page_id = 6270;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        if($data['status'] == 'update'){
            $oldheaddata = DB::table('EA301_6246')->select('*')->where('id', $data['data']['id'])->get();
            $oldbodydata = DB::table('EA301_6247')->select('*')->where('parent_id', $data['data']['id'])->get();
            $oldbodydata2 = DB::table('EA301_6248')->select('*')->where('parent_id', $data['data']['id'])->get();
            $totalnum = 0;
            $totalnum2 = 0;
            foreach($oldbodydata as $key => $val){
                $totalnum = $val->body_num*$val->body_rate;
                $oldnum = DB::table('EA204_79')
                        ->where('product_code',$val->product_code)
                        ->where('depot_code',$val->depot_code)
                        ->pluck('num');
                $newnum = $oldnum[0] + $totalnum;
                DB::table('EA204_79')
                        ->where('product_code',$val->product_code)
                        ->where('depot_code',$val->depot_code)
                        ->update(['num' => $newnum]);
            }
            foreach($oldbodydata2 as $key => $val){
                $totalnum2 = $val->body_num*$val->body_rate;
                $oldnum = DB::table('EA204_79')
                        ->where('product_code',$val->product_code)
                        ->where('depot_code',$val->depot_code)
                        ->pluck('num');
                $newnum = $oldnum[0] - $totalnum2;
                DB::table('EA204_79')
                        ->where('product_code',$val->product_code)
                        ->where('depot_code',$val->depot_code)
                        ->update(['num' => $newnum]);
            }
        }
        if( $verify ){
			self::atSave($data,true);
		}


    }
    static public function atSave(&$data,$verify = false)
	{
		if( !$verify ){
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
        $totalnum = 0;
        $totalnum2 = 0;
        $newnum = 0;
        $tmpArr = [];
        foreach($data['subData'][6248] as $key => $val){

            $totalnum2 = $data['subData'][6248][$key]['data']['body_num']*$data['subData'][6248][$key]['data']['body_rate'];
            $oldnum = DB::table('EA204_79')
                    ->where('product_code',$data['subData'][6248][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][6248][$key]['data']['depot_code'])
                    ->pluck('num');
            $newnum = $oldnum[0] + $totalnum2;
            //dd($newnum);
            DB::table('EA204_79')
                    ->where('product_code',$data['subData'][6248][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][6248][$key]['data']['depot_code'])
                    ->update(['num' => $newnum]);
        }
        foreach($data['subData'][6247] as $key => $val){
            //dd($data['data']);
            $totalnum = $data['subData'][6247][$key]['data']['body_num']*$data['subData'][6247][$key]['data']['body_rate'];
            $oldnum = DB::table('EA204_79')
                    ->where('product_code',$data['subData'][6247][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][6247][$key]['data']['depot_code'])
                    ->pluck('num');
            $newnum = $oldnum[0] - $totalnum;

            //dd($oldnum[0]);
            if($totalnum > $oldnum[0]){
                if($verify){
                    $tmpArr[] = '產品：'.$data['subData'][6247][$key]['data']['product_code'].'  庫存不足';
                }else{
                    array_push($tmpArr,[
                        "text" => '產品：'.$data['subData'][6247][$key]['data']['product_code'].'  庫存不足'
                    ]);
                }
                //$error= '產品：'.$data['subData'][5240][$key]['data']['product_code'].'  庫存不足';
            }else{
                $olddepot = DB::table('EA204_79')
                ->where('product_code', $data['subData'][6247][$key]['data']['product_code'])
                ->where('depot_code', $data['subData'][6247][$key]['data']['depot_code'])
                ->pluck('num');
                $newdepot = $olddepot[0] - $totalnum;
                    DB::table('EA204_79')
                    ->where('product_code',$data['subData'][6247][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][6247][$key]['data']['depot_code'])
                    ->update(['num' => $newnum]);
            }

        }

        return $tmpArr;
    }
    static public function atDelete(&$data,$verify = false)
	{

		if($verify){
			$page_id = 6270;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
		}
        $totalnum = 0;
        foreach($data['subData'][6247] as $key => $val){
            $totalnum = $data['subData'][6247][$key]['data']['body_num']*$data['subData'][6247][$key]['data']['body_rate'];
            $oldnum = DB::table('EA204_79')
                    ->where('product_code',$data['subData'][6247][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][6247][$key]['data']['depot_code'])
                    ->pluck('num');
            $newnum = $oldnum[0] + $totalnum;
            DB::table('EA204_79')
                    ->where('product_code',$data['subData'][6247][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][6247][$key]['data']['depot_code'])
                    ->update(['num' => $newnum]);
        }
        $totalnum2 = 0;
        foreach($data['subData'][6248] as $key => $val){
            $totalnum2 = $data['subData'][6248][$key]['data']['body_num']*$data['subData'][6248][$key]['data']['body_rate'];
            $oldnum = DB::table('EA204_79')
                    ->where('product_code',$data['subData'][6248][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][6248][$key]['data']['depot_code'])
                    ->pluck('num');
            $newnum = $oldnum[0] - $totalnum2;
            DB::table('EA204_79')
                    ->where('product_code',$data['subData'][6248][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][6248][$key]['data']['depot_code'])
                    ->update(['num' => $newnum]);
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
            //dd($tmpArr);
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
		if( array_key_exists('docu_number',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'docu_number',$dataset["data"]["docu_number"]);
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
    static public function bfDelete(&$data,$verify = false)
	{
		// dd($data);
		if($verify){
			$page_id = 6270;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$warm = [];
		}else{
			$warm = '';
        }
        $totalnum = 0;
        foreach($data['subData'][6248] as $key => $val){
            //dd($data['data']);
            $totalnum = $data['subData'][6248][$key]['data']['body_num']*$data['subData'][6248][$key]['data']['body_rate'];
            $oldnum = DB::table('EA204_79')
                    ->where('product_code',$data['subData'][6248][$key]['data']['product_code'])
                    ->where('depot_code', $data['subData'][6248][$key]['data']['depot_code'])
                    ->pluck('num');
            $newnum = $oldnum[0] - $totalnum;
            if($newnum < 0){
                if($verify){
                    $warm[] = "警告：產品代碼為{$data['subData'][6248][$key]['data']['product_code']}，分倉庫存不足，故無法刪除";
                }else{
                    $warm = $warm . "警告：產品代碼為{$data['subData'][6248][$key]['data']['product_code']}，分倉庫存不足，故無法刪除 \n";
                }
            }

        }
        return $warm;
    }
    static public function beforeDelete(&$data, &$pageData)
    {

        $pageId = $pageData['page']['page_id'];
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
			DB::beginTransaction();
			$warm = self::bfDelete($data);
			if($warm != ''){
	//            $translations = TranslationUtil::getTranslationByCode('CA202.error.deletefailed');
				response()->json(['status' => false , 'message' => $warm])->send();
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
			DB::beginTransaction();
			self::atDelete($data);
			DB::commit();
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
}
