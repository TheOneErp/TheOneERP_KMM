<?php

namespace App\Http\Inject\BA\BA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class BA203 extends InjectBase
{
	static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 63;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
		$subDataFormId = 62;
		if($data['status'] == 'update'){
			//判斷是否為合約客戶，是的話要把金額扣回去
            $oldBA203data = DB::table('BA203_61')->where('id', $data['data']['id'])->first();
            $oldBA102data = DB::table('BA102_37')->where('client_code', $oldBA203data->client_code)->first();
            if($oldBA102data->yn_cnt_cust){
                DB::table('BA102_37')
				->where('client_code', $oldBA203data->client_code)
				->update([
					'cnt_balance' => $oldBA102data->cnt_balance - $oldBA203data->ototal,
				]);
            }
			$customs = DB::table('BA201_41')->get();
			$back_id = $data['data']['id'];
            $oldbodydata = DB::table('BA203_62')->select('*')->where('parent_id', $back_id)->get();
            $totalnum = 0;
            $totalquantity = 0;
            foreach($oldbodydata as $key => $val){
                //庫存先將原本的舊資料減回來
                $totalnum = $val->body_num*$val->body_rate;
                $combi =$val->combi_code;
				CommonController::updateDepot($val->product_code,$val->body_depot_code,"subtraction",$totalnum,$combi);
                //表身明細訂單NO不為空白時，加回客戶訂單已交量
                if( !is_null($val->order_no) ) {
                    $oldquantity = $customs->where('id', $val->order_no)->first();
                    $newquantity = $oldquantity->body_quantity + ($totalnum / $oldquantity->body_rate);
                    DB::table('BA201_41')->where('id', $val->order_no)
						->update([
							'body_quantity' => $newquantity,
						]);
                }
            }
		}

		$customs = DB::table('BA201_40');
		if (VerifyUtil::pageVerifyConfirmation(53)) {
			$customs = $customs->where("BA201_40.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		$customs = $customs->select(DB::raw('BA201_40.*,BA201_41.id as cId,BA201_41.*'))->leftJoin('BA201_41','BA201_40.id','=','BA201_41.parent_id')->get();
		$ships = DB::table('BA202_52');
		if (VerifyUtil::pageVerifyConfirmation(59)) {
			$ships = $ships->where("BA202_52.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		$ships = $ships->select(DB::raw('BA202_52.*,BA202_53.id as cId,BA202_53.*'))->leftJoin('BA202_53','BA202_52.id','=','BA202_53.parent_id')->get();
		$tmpArr = [];
		if( array_key_exists('subData',$data) ){
			$subData = [];
			$shipData = [];
			foreach($data['subData'][$subDataFormId] as $key=>$value){
				//訂單單號整理
				$client_code = $value['data']['client_order_code'];
				$order_no = $value['data']['order_no'];
				$product_code = $value['data']['product_code'];
				$depot_code = $value['data']['body_depot_code'];
				$ship_no = $value['data']['ship_no'];
				$ship_code = $value['data']['ship_code'];
				if( !is_null($order_no) ){

					$rateFromCustomer = $customs->where('client_order_code','=',$client_code)->where('cId','=',$order_no)->first();
					if( empty($rateFromCustomer) ){
						if($verify){
							$tmpArr[] = "警告：客戶訂單{$client_code},NO:{$order_no} ，目前無法使用，請確認";
						}else{
							array_push($tmpArr,[
								"text" =>"警告：客戶訂單{$client_code},NO:{$order_no} ，目前無法使用，請確認"
							]);
						}
					}
				}
				if( !is_null($client_code) ){
					$rateFromShip = $ships->where('client_order_code','=',$client_code)->where('cId','=',$ship_no)->pluck('body_rate')->first();
					if( empty($rateFromShip) ){
						if($verify){
							$tmpArr[] = "警告：出貨單{$ship_code},NO:{$ship_no} ，目前無法使用，請確認";
						}else{
							array_push($tmpArr,[
								"text" =>"警告：出貨單{$ship_code},NO:{$ship_no} ，目前無法使用，請確認"
							]);
						}
					}else{
						if( array_key_exists($ship_no,$shipData) ){
							$shipData[$ship_no] = (float)$shipData[$ship_no] + (float)( ($value['data']['body_num'] * $value['data']['body_rate'])/$rateFromShip );
						}else{
							$shipData[$ship_no] = (float)round(($value['data']['body_num'] * $value['data']['body_rate'])/$rateFromShip,2) ;
						}
					}
				}
			}
			$errorMsg = [];
			$errorMsg["status"] = false;

			//同一出貨單退回數量加總
			if( isset($data['data']['id']) ){
				$backData = DB::table('BA203_61 as a')
				->select(DB::raw("b.ship_no,b.ship_code,sum(b.body_num * b.body_rate) as body_num"))
				 ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
				->whereNotNull('b.ship_code')
				->where('a.id','<>',$data['data']['id'])
				->groupBy('b.ship_code','b.ship_no');
			}else{
				$backData = DB::table('BA203_61 as a')
				->select(DB::raw("b.ship_no,b.ship_code,sum(b.body_num * b.body_rate) as body_num"))
				 ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
				->whereNotNull('b.ship_code')
				->groupBy('b.ship_code','b.ship_no');
			}
			// $vtable = DB::table("CA202_54 as a");
			if (VerifyUtil::pageVerifyConfirmation(64)) {
				$backData = $backData->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
			}
			$backData = $backData->get();

			foreach($shipData as $key=>$value){
				//抓出出貨單的這筆
				$ship = DB::table('BA202_53')->where('id','=',$key)->first();
				$ship_num = $ship->body_num;
				$ship_rate = $ship->body_rate;
				//退回單
				$backNum = $backData->where('ship_no','=',$key)->pluck('body_num')->first();
				//抓出此筆出貨單數量加總
				$remainNum = (float)round((($ship_num * $ship_rate ) - (float)$backNum)/$ship_rate,2);
				if((float)$value > (float)$remainNum){
					if($verify){
						$tmpArr[] = "警告：出貨NO為{$key} ，總出貨退回數量大於出貨單的數量，請確認";
					}else{
						array_push($tmpArr,[
							"text" =>"警告：出貨NO為{$key} ，總出貨退回數量大於出貨單的數量，請確認"
						]);
					}
				}
			}
			if( $verify ){
				if( empty($tmpArr) ){
					$tmpArr = self::atSave($data,true);
				}
			}
			return $tmpArr;
		}

	}//end of bfSave
	static public function atSave(&$data,$verify = false)
	{
		if( !$verify ){
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
		$totalnum = 0;
        $totalquantity = 0;
        $errorarr = [];
		$subDataFormId = 62;
		$customs = DB::table('BA201_40 as a')->select(DB::raw("b.id as order_no,*"))->leftJoin('BA201_41 as b', 'a.id', '=', 'b.parent_id');
		if (VerifyUtil::pageVerifyConfirmation(53)) {
			$customs = $customs->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		$customs = $customs->get();
		//判斷是否為合約客戶，是的話就加回去
        $oldBA102data = DB::table('BA102_37')->where('client_code', $data['data']['client_code'])->first();
        if($oldBA102data->yn_cnt_cust){
            DB::table('BA102_37')
			->where('client_code', $data['data']['client_code'])
			->update([
				'cnt_balance' => $oldBA102data->cnt_balance + $data['data']['ototal'],
			]);
        }
        foreach($data['subData'][$subDataFormId] as $datakey => $value){
            $combi = $value['data']['combi_code'];
			$client_code = $value['data']['client_order_code'];
			$order_no = $value['data']['order_no'];
            $totalnum = (float)round($value['data']['body_num']*$value['data']['body_rate'],2); //數量*換算率

            //表身訂單單號跟訂單NO不為空時，需回寫訂單的已交量
			if( !is_null($client_code) && !is_null($order_no) ){

				$custom = $customs->where('order_no', $order_no)->first();
				if( !empty($custom) ){
					$totalquantity = $custom->body_quantity - ($totalnum / $custom->body_rate);
					//當已交量<0時跳出提示
					if($totalquantity < 0 ){
						if($verify){
							$errorarr[] = '警告：訂單單號 "'.$client_code.'"  的訂單NO"'.$order_no.'"，會使客戶訂單已交量小於0，請確認';
						}else{
							array_push($errorarr,[
								"text"=>'警告：訂單單號 "'.$client_code.'"  的訂單NO"'.$order_no.'"，會使客戶訂單已交量小於0，請確認'
							]);
						}

					}else{
						DB::table('BA201_41')->where('id', $order_no)
							->update([
								'body_quantity' => $totalquantity,
							]);
					}
				}else{
					if($verify){
						$errorarr[] = '警告：訂單單號 "'.$client_code.'"  的訂單NO"'.$order_no.'"目前無法使用，請確認';
					}else{
						array_push($errorarr,[
							"text"=>'警告：訂單單號 "'.$client_code.'"  的訂單NO"'.$order_no.'"目前無法使用，請確認'
						]);
					}
				}
            }
            //庫存需增加數量
			CommonController::updateDepot($value['data']['product_code'],$value['data']['body_depot_code'],"addition",$totalnum,$combi);
		}
		return $errorarr;
	}
	static public function bfDelete(&$data,$verify = false)
	{
		// dd($data);
		if($verify){
			$page_id = 63;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$warm = [];
		}else{
			$warm = '';
		}
		$subDataFormId = 62;
		$deops = DB::table('EA204_79')->get();
		if( array_key_exists('subData',$data) ){
			$subData = [];
			$deopData = [];
			$customs = DB::table('BA201_40')->select(DB::raw("nnum =((body_num - body_quantity)*body_rate), order_no = BA201_41.id,advanceday,body_num,body_price,body_quantity,body_rate,body_subtotal,client_order_code,product_code,product_name,remarks,unit_code,unit_name"))->leftJoin('BA201_41', 'BA201_40.id', '=', 'BA201_41.parent_id')->get();
			foreach($data['subData'][$subDataFormId] as $key=>$value){
				$client_code = $value['data']['client_order_code'];
				$order_no = $value['data']['order_no'];
				$product_code = $value['data']['product_code'];
				$depot_code = $value['data']['body_depot_code'];
				$rateFromCustomer = $customs->where('order_no','=',$order_no)->pluck('body_rate')->first();
				//庫存整理
				if( array_key_exists($depot_code,$deopData) ){
                    if($value['data']['combi_code']==""||$value['data']['combi_code']==null){
                        $productNum = array_key_exists($product_code,$deopData[$depot_code]['product'])?$deopData[$depot_code]['product'][$product_code]:0;
                        $deopData[$depot_code]['product'][(string)$product_code] = (float)$productNum + ( $value['data']['body_num'] * $value['data']['body_rate'] );
                    }else{
                        $data1 = DB::table('AA204_2224')->where('product_code',$product_code)->where('combi_code',$value['data']['combi_code'])->lockForUpdate()->first();
                        $subdata =DB::table('AA204_2225')
                        ->select('*')
                        ->where('parent_id', $data1->id)
                        ->get();
                        foreach($subdata as $key => $val){
                            $prototalnum = $val->body_num*$val->body_rate;
                            if( array_key_exists($depot_code,$deopData) ){
                                $productNum = array_key_exists($val->cont_code,$deopData[$depot_code]['product'])?$deopData[$depot_code]['product'][$val->cont_code]:0;
                                $deopData[$depot_code]['product'][(string)$val->cont_code] = (float)$productNum + ( $value['data']['body_num'] * $value['data']['body_rate'] * $prototalnum);
                            }else{
                                $deopData[$depot_code] = array(
                                    'name'=>$value['data']['body_depot_name'],
                                    'product'=>array(
                                        (string)$val->cont_code=>$value['data']['body_num'] * $value['data']['body_rate'] * $prototalnum
                                    ),
                                );
                            }
                        }
                    }
				}else{

				if($value['data']['combi_code']==""||$value['data']['combi_code']==null){
    $deopData[$depot_code] = array(
        'name'=>$value['data']['body_depot_name'],
        'product'=>array(
            (string)$product_code=>$value['data']['body_num'] * $value['data']['body_rate']
        )
    );
}else{
    $data1 = DB::table('AA204_2224')->where('product_code',$product_code)->where('combi_code',$value['data']['combi_code'])->lockForUpdate()->first();
    $subdata =DB::table('AA204_2225')
    ->select('*')
    ->where('parent_id', $data1->id)
    ->get();
    foreach($subdata as $key => $val){
        $prototalnum = $val->body_num*$val->body_rate
        ;
        if( array_key_exists($depot_code,$deopData) ){
            $productNum = array_key_exists($val->cont_code,$deopData[$depot_code]['product'])?$deopData[$depot_code]['product'][$val->cont_code]:0;
            $deopData[$depot_code]['product'][(string)$val->cont_code] = (float)$productNum + ( $value['data']['body_num'] * $value['data']['body_rate'] * $prototalnum);
        }else{
            $deopData[$depot_code] = array(
                'name'=>$value['data']['body_depot_name'],
                'product'=>array(
                    (string)$val->cont_code=>$value['data']['body_num'] * $value['data']['body_rate'] * $prototalnum
                ),
            );
        }
    }
}
                }
			}
            //  dd($deopData);
			foreach($deopData as $deopCode=>$value){
				foreach($value['product'] as $productCode=>$num){
					$deopNum = DB::table('EA204_79')->where('depot_code','=',(string)$deopCode)->where('product_code','=',(string)$productCode)->pluck('num')->first();

					if( $deopNum && (float)$num > (float)$deopNum){
						if($verify){
							$warm[] = "警告：產品代碼 : {$productCode} ，在分倉 : {$value['name']} 庫存不足";
						}else{
							$warm = $warm . "警告：產品代碼 : {$productCode} ，在分倉 : {$value['name']} 庫存不足 \n";
						}
					}
				}
			}
			return $warm;
		}
	}
	static public function atDelete(&$data,$verify = false)
	{
		if($verify){
			$page_id = 63;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
		}
		//判斷是否為合約客戶，是的話要把金額扣回去
        $oldBA102data = DB::table('BA102_37')->where('client_code', $data['data']['client_code'])->first();
        if($oldBA102data->yn_cnt_cust){
            DB::table('BA102_37')
            ->where('client_code', $data['data']['client_code'])
            ->update([
                'cnt_balance' => $oldBA102data->cnt_balance - $data['data']['ototal'],
            ]);
        }
		$totalnum = 0;
        $totalquantity = 0;
		$subDataFormId = 62;
		$customs = DB::table('BA201_41')->get();
        foreach($data['subData'][$subDataFormId] as $key => $val){
            //庫存先將原本的舊資料減回來
            $combi = $val['data']['combi_code'];
            $totalnum = $val['data']['body_num']*$val['data']['body_rate'];
			CommonController::updateDepot($val['data']['product_code'],$val['data']['body_depot_code'],"subtraction",$totalnum,$combi);
            //表身明細訂單NO不為空白時，加回訂單已交量
            if( !is_null($val['data']['order_no']) ){
                $oldquantity = $customs
                        ->where('id', $val['data']['order_no'])
                        ->first();
                $newquantity = (float)round($oldquantity->body_quantity + ($totalnum / $oldquantity->body_rate),2);
                DB::table('BA201_41')
                        ->where('id', $val['data']['order_no'])
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
		if( array_key_exists('back_code',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'back_code',$dataset['data']['back_code']);
			$dataset['data']['back_code'] = $number;
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
			// self::atSave($data);
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
