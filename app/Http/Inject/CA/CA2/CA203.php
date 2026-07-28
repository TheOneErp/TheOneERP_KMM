<?php

namespace App\Http\Inject\CA\CA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class CA203 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 64;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        $tmpArr = [];
        if($data['status'] == 'update'){
            $oldbodydata = DB::table('CA203_64')
                ->select('*')
                ->where('parent_id', $data['data']['id'])
                ->get();
            $totalnum = 0;
            $totalquantity = 0;
            foreach($oldbodydata as $key => $val){
                //庫存先將原本的舊資料加回來
                $totalnum = $val->body_num*$val->body_rate;
				CommonController::updateDepot($val->product_code,$val->body_depot_code,"addition",$totalnum);
                $oldnum = DB::table('EA204_79')
                        ->where('product_code',$val->product_code)
                        ->where('depot_code', $val->body_depot_code)
                        ->pluck('num');
                // if(count($oldnum)>0){
//                    $newnum = $oldnum[0] + $totalnum;
//                    DB::table('EA204_79')
//                            ->where('product_code',$val->product_code)
//                            ->where('depot_code', $val->body_depot_code)
//                            ->update(['num' => $newnum]);
                    //表身明細採購NO不為空白時，加回採購單已交量
                    if($val->purchase_no != null){
                        $oldquantity = DB::table('CA201_43')
                                ->select('body_rate','body_quantity')
                                ->where('id', $val->purchase_no)
                                ->get();
                        $newquantity = $oldquantity[0]->body_quantity + ($totalnum / $oldquantity[0]->body_rate);
                        DB::table('CA201_43')
                                ->where('id', $val->purchase_no)
                                ->update([
                                    'body_quantity' => $newquantity,
                                ]);
                    }
                // }
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
        $totalquantity = 0;
        $tmpArr = [];
        $receiveDatas = [];
        foreach($data['subData']['64'] as $datakey => $dataval){
            $totalnum = $data['subData']['64'][$datakey]['data']['body_num']*$data['subData']['64'][$datakey]['data']['body_rate']; //數量*換算率
            $depotdata = DB::table('EA204_79')
                 ->select('product_code','depot_code','depot_name','num')
                 ->where('product_code', $data['subData']['64'][$datakey]['data']['product_code'])
                 ->where('depot_code', $data['subData']['64'][$datakey]['data']['body_depot_code'])
                 ->get();

            // if(count($depotdata)>0){
                //表身數量*換算率大於庫存時，跳出提示
                if($data['subData']['64'][$datakey]['data']['body_depot_code']!=null){
                if($totalnum > $depotdata[0]->num){
                    if($verify){
                        $tmpArr[] = '警告：產品代碼為 "'.$data['subData']['64'][$datakey]['data']['product_code'].'" ，分倉庫存不足，請確認';
                    }else{
                        array_push($tmpArr,[
                            "text"=>'警告：產品代碼為 "'.$data['subData']['64'][$datakey]['data']['product_code'].'" ，分倉庫存不足，請確認'
                        ]);
                    }
                }
            }
                if($data['subData']['64'][$datakey]['data']['receive_no'] != null){
                    $receiveno = DB::table('CA203_64 as a')
                            ->leftJoin('CA203_63 as b', 'b.id', '=', 'a.parent_id')
                            ->select('a.body_num','a.body_rate')
                            ->where('a.receive_no', $data['subData']['64'][$datakey]['data']['receive_no']);
                    if (VerifyUtil::pageVerifyConfirmation(64)) {
                        $receiveno = $receiveno->where("b.data_options", "LIKE", '%"verify":{%"level":255%');
                    }
                    $receiveno = $receiveno->get();
                    $receivedata = DB::table('CA202_55 as a')
                            ->leftJoin('CA202_54 as b', 'b.id', '=', 'a.parent_id')
                            ->select('a.body_num','a.body_rate')
                            ->where('a.id', $data['subData']['64'][$datakey]['data']['receive_no']);
                    if (VerifyUtil::pageVerifyConfirmation(60)) {
                        $receivedata = $receivedata->where("b.data_options", "LIKE", '%"verify":{%"level":255%');
                    }
                    $receivedata = $receivedata->get();
                    //檢查是否有其他筆進貨NO對應的進貨退出單
                    if( count($receivedata)<=0 ){
                        if($verify){
                            $tmpArr[] = '警告：進貨單號 "'.$data['subData']['64'][$datakey]['data']['receive_code'].'"  的進貨NO"'.$data['subData']['64'][$datakey]['data']['receive_no'].'"，目前無法使用，請確認';
                        }else{
                            array_push($tmpArr,[
                                "text"=>'警告：進貨單號 "'.$data['subData']['64'][$datakey]['data']['receive_code'].'"  的進貨NO"'.$data['subData']['64'][$datakey]['data']['receive_no'].'"，目前無法使用，請確認'
                            ]);
                        }
                    }else{
                        if(count($receiveno)>0){
                            $totalno = 0;
                            foreach($receiveno as $nokey => $noval){
                                // dd($receivedata);
                                $totalno = $totalno + ($noval->body_num * $noval->body_rate / $receivedata[0]->body_rate);
                                if( array_key_exists($data['subData']['64'][$datakey]['data']['receive_no'],$receiveDatas) ){
                                    $receiveDatas[$data['subData']['64'][$datakey]['data']['receive_no']] = (float)$receiveDatas[$data['subData']['64'][$datakey]['data']['receive_no']] + (float)($totalnum/$receivedata[0]->body_rate);
                                }else{
                                    $receiveDatas[$data['subData']['64'][$datakey]['data']['receive_no']] = $totalno;
                                }
                            }
                        }
                    }

                }
                //通過驗證後，庫存須扣除該產品數量
				CommonController::updateDepot($data['subData']['64'][$datakey]['data']['product_code'],$data['subData']['64'][$datakey]['data']['body_depot_code'],"subtraction",$totalnum);

                //dd($data['subData']['64'][$datakey]['data']['purchase_no']);
                if($data['subData']['64'][$datakey]['data']['purchase_no'] != null){
                    $oldquantity = DB::table('CA201_43 as a')
                            ->leftJoin('CA201_42 as b', 'b.id', '=', 'a.parent_id')
                            ->select('a.body_num','a.body_rate','a.body_quantity')
                            ->where('a.id', $data['subData']['64'][$datakey]['data']['purchase_no']);
                    if (VerifyUtil::pageVerifyConfirmation(55)) {
                        $oldquantity = $oldquantity->where("b.data_options", "LIKE", '%"verify":{%"level":255%');
                    }
                    $oldquantity = $oldquantity->get();
                    if( count($oldquantity)<=0 ){
                        if($verify){
                            $tmpArr[] = '警告：採購單號 "'.$data['subData']['64'][$datakey]['data']['purchase_code'].'"  的採購NO"'.$data['subData']['64'][$datakey]['data']['purchase_no'].'"，目前無法使用，請確認';
                        }else{
                            array_push($tmpArr,[
                                "text"=>'警告：採購單號 "'.$data['subData']['64'][$datakey]['data']['purchase_code'].'"  的採購NO"'.$data['subData']['64'][$datakey]['data']['purchase_no'].'"，目前無法使用，請確認'
                            ]);
                        }
                    }else{
                        $newquantity = $oldquantity[0]->body_quantity - ($totalnum / $oldquantity[0]->body_rate);
                        if($newquantity<0){
                            if($verify){
                                $tmpArr[] = '警告：採購單號 "'.$data['subData']['64'][$datakey]['data']['purchase_code'].'"  的採購NO"'.$data['subData']['64'][$datakey]['data']['purchase_no'].'"，數量大於採購單的已交數量，請確認';
                            }else{
                                array_push($tmpArr,[
                                    "text"=>'警告：採購單號 "'.$data['subData']['64'][$datakey]['data']['purchase_code'].'"  的採購NO"'.$data['subData']['64'][$datakey]['data']['purchase_no'].'"，數量大於採購單的已交數量，請確認'
                                ]);
                            }
                        }else{
                            DB::table('CA201_43')
                                    ->where('id', $data['subData']['64'][$datakey]['data']['purchase_no'])
                                    ->update(['body_quantity' => $newquantity]);
                        }
                    }
                }
            // }
            foreach($receiveDatas as $key=>$value){
                $receive = DB::table('CA202_55 as a')
                    ->leftJoin('CA202_54 as b', 'b.id', '=', 'a.parent_id')
                    ->where('a.id','=',$key);
                if (VerifyUtil::pageVerifyConfirmation(60)) {
                    $receive = $receive->where("b.data_options", "LIKE", '%"verify":{%"level":255%');
                }
                $receive = $receive->first();
                $receive_num = $receive->body_num;
                if((float)$receive_num < (float)$value){
                    if($verify){
                        $tmpArr[] = '警告：進貨NO為 "'.$data['subData']['64'][$datakey]['data']['receive_no'].'" ，總進貨退出數量大於進貨單的數量，請確認';
                    }else{
                        array_push($tmpArr,[
                            "text"=>'警告：進貨NO為 "'.$data['subData']['64'][$datakey]['data']['receive_no'].'" ，總進貨退出數量大於進貨單的數量，請確認'
                        ]);
                    }
                }
            }
        }

        return $tmpArr;
    }
    static public function bfDelete(&$data,$verify = false)
	{
		// dd($data);
		if($verify){
			$page_id = 64;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$warm = [];
		}else{
			$warm = '';
        }
        foreach($data['subData'][64] as $key => $val){
            if($data['subData'][64][$key]['data']['purchase_no'] != null){
                $totalnum = $data['subData'][64][$key]['data']['body_num']*$data['subData'][64][$key]['data']['body_rate'];
                $oldquantity = DB::table('CA201_43')
                        ->select('body_num','body_rate','body_quantity')
                        ->where('id', $data['subData'][64][$key]['data']['purchase_no'])
                        ->get();
                $newquantity = $oldquantity[0]->body_quantity + ($totalnum / $oldquantity[0]->body_rate);
                if($newquantity > $oldquantity[0]->body_num){
                    if($verify){
                        $warm[] = "警告：採購單號為{$data['subData'][64][$key]['data']['purchase_code']}，的採購NO{$data['subData'][64][$key]['data']['purchase_no']}數量大於採購單的未交數量，故無法刪除";
                    }else{
                        $warm = $warm."警告：採購單號為{$data['subData'][64][$key]['data']['purchase_code']}，的採購NO{$data['subData'][64][$key]['data']['purchase_no']}數量大於採購單的未交數量，故無法刪除。\n";
                    }
                }
            }
        }
        return $warm;
    }
    static public function atDelete(&$data,$verify = false)
	{

		if($verify){
			$page_id = 64;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
		}
        $totalnum = 0;
        $totalquantity = 0;
        foreach($data['subData'][64] as $key => $val){
            //庫存先將原本的舊資料加回來
            $totalnum = $data['subData'][64][$key]['data']['body_num']*$data['subData'][64][$key]['data']['body_rate'];
			CommonController::updateDepot($data['subData'][64][$key]['data']['product_code'],$data['subData'][64][$key]['data']['body_depot_code'],"addition",$totalnum);
            //表身明細採購NO不為空白時，加回採購單已交量
            if($data['subData'][64][$key]['data']['purchase_no'] != null){
                $oldquantity = DB::table('CA201_43')
                        ->select('body_rate','body_quantity')
                        ->where('id', $data['subData'][64][$key]['data']['purchase_no'])
                        ->get();
                $newquantity = $oldquantity[0]->body_quantity + ($totalnum / $oldquantity[0]->body_rate);
                DB::table('CA201_43')
                        ->where('id', $data['subData'][64][$key]['data']['purchase_no'])
                        ->update([
                            'body_quantity' => $newquantity,
                        ]);
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
		if( array_key_exists('abort_code',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'abort_code',$dataset["data"]["abort_code"]);
        	$dataset['data']['abort_code'] = $number;
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
		if( array_key_exists('ship_code',$data['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'ship_code',$data['data']['ship_code']);
			$data['data']['ship_code'] = $number;
		}
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
			DB::beginTransaction();
			$tmpArr = self::atSave($data);
//            dd($tmpArr);
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
