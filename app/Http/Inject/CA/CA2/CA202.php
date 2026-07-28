<?php

namespace App\Http\Inject\CA\CA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class CA202 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 60;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        $tmpArr = [];
        if($data['status'] == 'update'){
            $oldbodydata = DB::table('CA202_55')
                ->select('*')
                ->where('parent_id', $data['data']['id'])
                ->get();
            $totalnum = 0;
            $totalquantity = 0;
            $newnum = 0;
            foreach($oldbodydata as $key => $val){
                //庫存先將原本的舊資料扣除
				$totalnum = $val->body_num*$val->body_rate;
				CommonController::updateDepot($val->product_code,$val->body_depot_code,"subtraction",$totalnum);

                //表身明細採購NO不為空白時，扣除採購單已交量
                if($val->purchase_no != null){
                    $oldquantity = DB::table('CA201_43')
                            ->select('body_rate','body_quantity')
                            ->where('id', $val->purchase_no)
                            ->get();
                    $newquantity = $oldquantity[0]->body_quantity - ($totalnum / $oldquantity[0]->body_rate);
                    DB::table('CA201_43')
                            ->where('id', $val->purchase_no)
                            ->update([
                                'body_quantity' => $newquantity,
                            ]);
                }
            }
            //先刪除廠商購買價
            DB::table('CA103_83')
                    ->where('receive_code', $data['data']['receive_code'])
                    ->delete();

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
        $totalquantity = 0;
        $tmpArr = [];
        foreach($data['subData']['55'] as $datakey => $dataval){
            $totalnum = $data['subData']['55'][$datakey]['data']['body_num']*$data['subData']['55'][$datakey]['data']['body_rate']; //數量*換算率
            //表身採購單號跟採購NO不為空時，需回寫採購單的已交量
            if(($data['subData']['55'][$datakey]['data']['purchase_code'] != null && $data['subData']['55'][$datakey]['data']['purchase_no'] != null) || ($data['subData']['55'][$datakey]['data']['purchase_code'] != '' && $data['subData']['55'][$datakey]['data']['purchase_no'] != '')){
                $bodypurchase = DB::table('CA201_43 as a')
                        ->leftJoin('CA201_42 as b', 'b.id', '=', 'a.parent_id')
                        ->select('a.id','a.body_num','a.body_quantity','a.body_rate')
                        ->where('a.id', $data['subData']['55'][$datakey]['data']['purchase_no']);
                if (VerifyUtil::pageVerifyConfirmation(55)) {
                    $bodypurchase = $bodypurchase->where("b.data_options", "LIKE", '%"verify":{%"level":255%');
                }
                $bodypurchase = $bodypurchase->get();
//                dd(count($bodypurchase));
                if( count($bodypurchase)<=0 ){
                    if($verify){
                        $tmpArr[] = '警告：採購單號 "'.$data['subData']['55'][$datakey]['data']['purchase_code'].'"  的採購NO"'.$data['subData']['55'][$datakey]['data']['purchase_no'].'"，目前無法使用，請確認';
                    }else{
                        array_push($tmpArr,[
                            "text"=>'警告：採購單號 "'.$data['subData']['55'][$datakey]['data']['purchase_code'].'"  的採購NO"'.$data['subData']['55'][$datakey]['data']['purchase_no'].'"，目前無法使用，請確認'
                        ]);
                    }
                }else{
                    $totalquantity = $bodypurchase[0]->body_quantity + ($totalnum / $bodypurchase[0]->body_rate);
//                    dd($totalquantity);
                    //當已交量>數量時跳出提示
                    if($totalquantity > $bodypurchase[0]->body_num){
                        if($verify){
                            $tmpArr[] = '警告：採購單號 "'.$data['subData']['55'][$datakey]['data']['purchase_code'].'"  的採購NO"'.$data['subData']['55'][$datakey]['data']['purchase_no'].'"，數量大於採購單的未交數量，請確認';
                        }else{
                            array_push($tmpArr,[
                                "text"=>'警告：採購單號 "'.$data['subData']['55'][$datakey]['data']['purchase_code'].'"  的採購NO"'.$data['subData']['55'][$datakey]['data']['purchase_no'].'"，數量大於採購單的未交數量，請確認'
                            ]);
                        }
                    }else{
                        DB::table('CA201_43')
                            ->where('id', $data['subData']['55'][$datakey]['data']['purchase_no'])
                            ->update([
                                'body_quantity' => $totalquantity,
                            ]);
                    }
                }
            }
            //庫存需增加數量
			CommonController::updateDepot($data['subData']['55'][$datakey]['data']['product_code'],$data['subData']['55'][$datakey]['data']['body_depot_code'],"addition",$totalnum);

            //回寫到廠商購買價
            $dataOptions = [
              'varify' => [
                  'level' => 255
              ]
            ];
            DB::table('CA103_83')
                ->insert([
                    'vendor_code' => $data['data']['vendor_code'],
                    'vendor_name' => $data['data']['vendor_name'],
                    'product_code' => $data['subData']['55'][$datakey]['data']['product_code'],
                    'product_name' => $data['subData']['55'][$datakey]['data']['product_name'],
                    'unit_code' => $data['subData']['55'][$datakey]['data']['unit_code'],
                    'unit_name' => $data['subData']['55'][$datakey]['data']['unit_name'],
                    'body_price' => $data['subData']['55'][$datakey]['data']['body_price'],
                    'body_rate' => $data['subData']['55'][$datakey]['data']['body_rate'],
                    'receive_day' => $data['data']['receive_day'],
                    'source_type' => 'input',
                    'receive_code' => $data['data']['receive_code'],
                    'data_options' => json_encode($dataOptions),
                ]);
        }
        return $tmpArr;
    }
    static public function bfDelete(&$data,$verify = false)
	{
		// dd($data);
		if($verify){
			$page_id = 60;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$warm = [];
		}else{
			$warm = '';
        }
        $totalnum = 0;
        $cost_goods = DB::table('AA202_30')->where('product_kind', "費用")->get();
        // dd($cost_goods);
        foreach($data['subData'][55] as $key => $val){
			if( $val['data']['batch'] ){
                if($verify){
                    $warm[] = '此進貨單已有批號管理';
                }else{
                    $warm = $warm.'此進貨單已有批號管理。';
                }
				break;
			}
            $totalnum = $data['subData'][55][$key]['data']['body_num'] * $data['subData'][55][$key]['data']['body_rate'];
            if( !$cost_goods->contains('product_code',$data['subData'][55][$key]['data']['product_code']) ){
                $depotnum = DB::table('EA204_79')
                ->where('product_code', $data['subData'][55][$key]['data']['product_code'])
                ->where('depot_code', $data['subData'][55][$key]['data']['body_depot_code'])
                ->pluck('num');
                if($depotnum[0] && $totalnum > $depotnum[0]){
                    if($verify){
                        $warm[] = "警告：產品代碼為 {$data['subData'][55][$key]['data']['product_code']} ，分倉庫存不足。";
                    }else{
                        $warm = $warm."警告：產品代碼為 {$data['subData'][55][$key]['data']['product_code']} ，分倉庫存不足。\n";
                    }
                }
            }



            $receivedata = DB::table('CA203_64 as a')
                    ->leftJoin('CA203_63 as b', 'b.id', '=', 'a.parent_id')
                    ->select('a.receive_code','a.receive_no')
                    ->where('a.receive_no', $data['subData'][55][$key]['data']['id']);

            $receivedata = $receivedata->get();
            if(count($receivedata)>0){
                if($verify){
                    $warm[] = "警告：產品代碼為 {$data['subData'][55][$key]['data']['product_code']} ，已有進貨退回紀錄。";
                }else{
                    $warm = $warm."警告：產品代碼為 {$data['subData'][55][$key]['data']['product_code']} ，已有進貨退回紀錄。\n";
                }
            }

            if($data['subData'][55][$key]['data']['purchase_no'] != null){
                $oldquantity = DB::table('CA201_43')
                        ->select('body_rate','body_quantity')
                        ->where('id', $data['subData'][55][$key]['data']['purchase_no'])
                        ->get();
                if( empty($oldquantity) ){
                    if($verify){
                        $warm[] = "警告：採購單號為{$data['subData'][55][$key]['data']['purchase_code']}，的採購NO{$data['subData'][55][$key]['data']['purchase_no']}，目前無法使用";
                    }else{
                        $warm = $warm."警告：採購單號為{$data['subData'][55][$key]['data']['purchase_code']}，的採購NO{$data['subData'][55][$key]['data']['purchase_no']}，目前無法使用。\n";
                    }
                }else{
                    $newquantity = $oldquantity[0]->body_quantity - ($totalnum / $oldquantity[0]->body_rate);
                    if($newquantity<0){
                        if($verify){
                            $warm[] = "警告：採購單號為{$data['subData'][55][$key]['data']['purchase_code']}，的採購NO{$data['subData'][55][$key]['data']['purchase_no']}數量大於採購單的已交數量";
                        }else{
                            $warm = $warm."警告：採購單號為{$data['subData'][55][$key]['data']['purchase_code']}，的採購NO{$data['subData'][55][$key]['data']['purchase_no']}數量大於採購單的已交數量。\n";
                        }
                    }
                }
            }
        }
        return $warm;
    }
    static public function atDelete(&$data,$verify = false)
	{
		if($verify){
			$page_id = 60;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
		}
        $totalnum = 0;
        $totalquantity = 0;
        foreach($data['subData'][55] as $key => $val){
            //庫存先將原本的舊資料扣除
            $totalnum = $data['subData'][55][$key]['data']['body_num'] * $data['subData'][55][$key]['data']['body_rate'];
			CommonController::updateDepot($data['subData'][55][$key]['data']['product_code'],$data['subData'][55][$key]['data']['body_depot_code'],"subtraction",$totalnum);
            //表身明細採購NO不為空白時，扣除採購單已交量
            if($data['subData'][55][$key]['data']['purchase_no'] != null){
                $oldquantity = DB::table('CA201_43')
                        ->select('body_rate','body_quantity')
                        ->where('id', $data['subData'][55][$key]['data']['purchase_no'])
                        ->get();
                $newquantity = $oldquantity[0]->body_quantity - ($totalnum / $oldquantity[0]->body_rate);
                DB::table('CA201_43')
                        ->where('id', $data['subData'][55][$key]['data']['purchase_no'])
                        ->update([
                            'body_quantity' => $newquantity,
                        ]);
            }
        }
        //刪除廠商購買價
        DB::table('CA103_83')
                ->where('receive_code', $data['data']['receive_code'])
                ->delete();
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
        if(array_key_exists("receive_code",$dataset["data"])){
            $pageId = $pageData['page']['page_id'];
			if( array_key_exists('receive_code',$dataset['data']) ){
				$number = CommonController::generateDocumentNumber($pageId,'receive_code',$dataset["data"]["receive_code"]);
            	$dataset['data']['receive_code'] = $number;
			}
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
