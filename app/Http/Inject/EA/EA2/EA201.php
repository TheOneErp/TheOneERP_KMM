<?php

namespace App\Http\Inject\EA\EA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class EA201 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 58;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        if($data['status'] == 'update'){
            $oldheaddata = DB::table('EA201_50')->select('*')->where('id', $data['data']['id'])->get();
            $oldbodydata = DB::table('EA201_51')->select('*')->where('parent_id', $data['data']['id'])->get();
            $totalnum = 0;
            foreach($oldbodydata as $key => $val){
                $totalnum = $val->body_num*$val->body_rate;
                $oldnum = DB::table('EA204_79')
                        ->where('product_code',$val->product_code)
                        ->where('depot_code',$oldheaddata[0]->depot_code)
                        ->pluck('num');
                $newnum = $oldnum[0] - $totalnum;
                DB::table('EA204_79')
                        ->where('product_code',$val->product_code)
                        ->where('depot_code',$oldheaddata[0]->depot_code)
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
        foreach($data['subData'][51] as $key => $val){
            $totalnum = $data['subData'][51][$key]['data']['body_num']*$data['subData'][51][$key]['data']['body_rate'];
            $oldnum = DB::table('EA204_79')
                    ->where('product_code',$data['subData'][51][$key]['data']['product_code'])
                    ->where('depot_code', $data['data']['depot_code'])
                    ->pluck('num');
            $newnum = $oldnum[0] + $totalnum;
            //dd($newnum);
            DB::table('EA204_79')
                    ->where('product_code',$data['subData'][51][$key]['data']['product_code'])
                    ->where('depot_code', $data['data']['depot_code'])
                    ->update(['num' => $newnum]);
        }
    }
    static public function atDelete(&$data,$verify = false)
	{
		
		if($verify){
			$page_id = 58;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
		}
        $totalnum = 0;
        foreach($data['subData'][51] as $key => $val){
            $totalnum = $data['subData'][51][$key]['data']['body_num']*$data['subData'][51][$key]['data']['body_rate'];
            $oldnum = DB::table('EA204_79')
                    ->where('product_code',$data['subData'][51][$key]['data']['product_code'])
                    ->where('depot_code', $data['data']['depot_code'])
                    ->pluck('num');
            $newnum = $oldnum[0] - $totalnum;
            DB::table('EA204_79')
                    ->where('product_code',$data['subData'][51][$key]['data']['product_code'])
                    ->where('depot_code', $data['data']['depot_code'])
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
			self::bfSave($data);
            DB::commit();
        }   
    }
    static public function beforeDatasetValidation(&$dataset, &$schema, &$rules, &$pageData)
    {
    }
    static public function afterDatasetValidationSuccess(&$dataset, &$schema, &$rules, &$validationResult, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
		if( array_key_exists('adjust_code',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'adjust_code',$dataset["data"]["adjust_code"]);
        	$dataset['data']['adjust_code'] = $number;
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
