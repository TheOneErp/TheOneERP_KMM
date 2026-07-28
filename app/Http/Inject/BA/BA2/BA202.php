<?php

namespace App\Http\Inject\BA\BA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
use App\Http\Inject\EA\EA2\EA201;
use Carbon\Carbon;

//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class BA202 extends InjectBase
{
	static public function verifySave(&$data,$status){
		if( $status == "add" ){
			foreach($data['subData'][53] as $key => $val){
				DB::table('BA202_53')
				->where('id', $val['data']['id'])
				->update([
					'batch_code' => $val['data']['batch_code'],
					'batch_no' => $val['data']['batch_no'],
				]);
			}
		}else{
			foreach($data['subData'][53] as $key => $val){
				DB::table('BA202_53')
				->where('id', $val['data']['id'])
				->update([
					'clear_num' => null,
					'keg_code'=> null,
					'batch_code' => null,
					'batch_no' => null,
				]);
			}
		}
	}
	static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 59;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
		$subData = [];
		$deopData = [];
		$tmpArr = [];
		$subDataFormId = 53;
		$kegSubId = 72;
		$prefix = 'BA202_52';
		$vtable = DB::table($prefix);

		// DB::beginTransaction();
		// dd($data);
		$productData = DB::table('AA202_30')->get();
		//修改
		if($data['status'] == 'update'){
            //判斷是否為合約客戶，是的話要把金額還回去
            $oldBA202data = DB::table('BA202_52')->where('id', $data['data']['id'])->first();
            $oldBA102data = DB::table('BA102_37')->where('client_code', $oldBA202data->client_code)->first();
            if($oldBA102data->yn_cnt_cust){
                DB::table('BA102_37')
				->where('client_code', $oldBA202data->client_code)
				->update([
					'cnt_balance' => $oldBA102data->cnt_balance + $oldBA202data->ototal,
				]);
            }
			//桶號
			// $KegData = DB::table('EA205_80')->get();
			//桶號小視窗
			$customs = DB::table('BA201_40')->select(DB::raw("nnum =((body_num - body_quantity)*body_rate), order_no = BA201_41.id,advanceday,body_num,body_price,body_quantity,body_rate,body_subtotal,client_order_code,product_code,product_name,remarks,unit_code,unit_name"))->leftJoin('BA201_41', 'BA201_40.id', '=', 'BA201_41.parent_id')->get();
			$oldbodydata = DB::table('BA202_53')->where('parent_id', $data['data']['id'])->get();
			$totalnum = 0;
			$totalquantity = 0;
			foreach($oldbodydata as $key => $val){

				//桶號將舊資料加回來
				if( $val->source_keg ){
					$oldKeg = DB::table('BA205_72')->where('parent_id','=',$val->source_keg)->get();

					foreach( $oldKeg as $oKegKey=>$oKeyVal ){
						$kegNow = DB::table('EA205_80')->where('keg','=',$oKeyVal->keg)->lockForUpdate()->get();//現在的桶號
						foreach( $kegNow as $nkegKey=>$nkegVal ){
							if( $nkegVal->product_code != $val->product_code && $nkegVal->product_code ){
								if($verify){
									$tmpArr[] = '警告：桶號為"'.$oKeyVal->keg.'" ，已放置其他產品，請確認';
								}else{
									array_push($tmpArr,[
										"text" => '警告：桶號為"'.$oKeyVal->keg.'" ，已放置其他產品，請確認'
									]);
								}

							}else{
								$unit_code = $productData->where('product_code','=',$val->product_code)->pluck('unit_code')->first();
								$unit_name = $productData->where('product_code','=',$val->product_code)->pluck('unit_name')->first();
								//更新桶號數量
								if( $val->clear ){
									DB::table('EA205_80')->where('keg', $oKeyVal->keg)
									->update( [
										'num' => (float)$oKeyVal->remaining_num,
										'product_code' => $val->product_code,
										'product_name' => $val->product_name,
										'unit_code' => $unit_code,
										'unit_name' => $unit_name
									]);
								}else{
									DB::table('EA205_80')->where('keg', $oKeyVal->keg)
									->update( [
										'num' => (float)$nkegVal->num + (float)$oKeyVal->body_num,
										'product_code' => $val->product_code,
										'product_name' => $val->product_name,
										'unit_code' => $unit_code,
										'unit_name' => $unit_name
									]);
								}
							}
						}
					}
				}
				//將批號刪掉
				/* $batchPrefix= mb_substr($val->batch_code,0,5);
				//進貨
				if( $batchPrefix ==  'CA202' ){
					CommonController::deleteBatchCode("CA",$val->batch_code,$val->batch_no,$data['data']['ship_code'],$val->id);
				}else{
					if(empty($batchPrefix)){//多跑一次進貨單
						CommonController::deleteBatchCode("CA",$val->batch_code,$val->batch_no,$data['data']['ship_code'],$val->id);
					}
					CommonController::deleteBatchCode("DA",$val->batch_code,$val->batch_no,$data['data']['ship_code'],$val->id);
				} */
				//庫存先將原本的舊資料加回來
				$product_kind = $productData->where('product_code',$val->product_code)->pluck('product_kind')->first();
                $totalnum = (float)round($val->body_num*$val->body_rate,2);
				if( $product_kind != "費用" ){ //費用不須更動庫存
					$totalnum = (float)round($val->body_num*$val->body_rate,2);
                    $combi =$val->combi_code;
					CommonController::updateDepot($val->product_code,$val->body_depot_code,"addition",$totalnum,$combi);
				}
				//表身明細採購NO不為空白時，扣除客戶訂單單已交量
				if( !is_null($val->order_no) ){
                    $customs = DB::table('BA201_40')->select(DB::raw("nnum =((body_num - body_quantity)*body_rate), order_no = BA201_41.id,advanceday,body_num,body_price,body_quantity,body_rate,body_subtotal,client_order_code,product_code,product_name,remarks,unit_code,unit_name"))->leftJoin('BA201_41', 'BA201_40.id', '=', 'BA201_41.parent_id')->get();
					$oldquantity = $customs->where('order_no', $val->order_no)->first();
					$newquantity = (float)round($oldquantity->body_quantity - ($totalnum / $oldquantity->body_rate),2);
						DB::table('BA201_41')
							->where('id', $val->order_no)
							->update([
								'body_quantity' => $newquantity,
							]);
				}
			}
			//扣除倉庫調整數量
			$kegAdj = DB::table('EA206_1069 as a')->select(DB::raw("keg_code,a.id as parent_id,a.source_code,b.*"))->leftJoin('EA206_1070 as b', 'a.id', '=', 'b.parent_id')->get();
			$deopAdj = DB::table('EA201_50 as a')->select(DB::raw("adjust_code,a.id as parent_id,a.source_code,a.depot_code,b.*"))->leftJoin('EA201_51 as b', 'a.id', '=', 'b.parent_id')->get();
			$deopAdjData = $deopAdj->where('source_code',$data['data']['ship_code'])->all();
			$kegAdjData = $kegAdj->where('source_code',$data['data']['ship_code'])->all();
			//庫存調整單
			if( !empty ($deopAdjData) && empty($tmpArr) ){
				foreach($deopAdjData as $adjKey=>$adjValue){
					//刪除庫存調整單表身
					DB::table('EA201_51')->where('parent_id', '=', $adjValue->parent_id)->delete();
				}
			}
			//刪除庫存調整單表頭
			DB::table('EA201_50')->where('source_code', '=', $data['data']['ship_code'])->delete();

			if( !empty ($kegAdjData) && empty($tmpArr) ){
				//刪除桶號調整單表身
				foreach($kegAdjData as $adjKey=>$adjValue){
					DB::table('EA206_1070')->where('parent_id', '=', $adjValue->parent_id)->delete();
				}
			}
			//刪除桶號調整單表頭
			DB::table('EA206_1069')->where('source_code', '=', $data['data']['ship_code'])->delete();
        }
		$customs = DB::table('BA201_40')->select(DB::raw("nnum =((body_num - body_quantity)*body_rate), order_no = BA201_41.id,advanceday,body_num,body_price,body_quantity,body_rate,body_subtotal,client_order_code,product_code,product_name,remarks,unit_code,unit_name"))->leftJoin('BA201_41', 'BA201_40.id', '=', 'BA201_41.parent_id')->get();
		$buckets = DB::table('EA205_80')->get();
		if( array_key_exists('subData',$data) ){
			$errorMsg = [];
			foreach($data['subData'][$subDataFormId] as $key=>$value){
                $totalnum = $value['data']['body_num'] * $value['data']['body_rate'];
				$client_code = $value['data']['client_order_code'];
				$order_no = $value['data']['order_no'];
				$product_code = $value['data']['product_code'];
				$depot_code = $value['data']['body_depot_code'];
                $combi_code = $value['data']['combi_code'];
				$clear = $value['data']['clear'];
				$rateFromCustomer = $customs->where('order_no','=',$order_no)->pluck('body_rate')->first();
				// $sourceKegSub = $value['data']['source_keg']['subData'][$kegSubId];
				$sourceKegSub = [];
                /* $batch_code =  $value['data']['batch_code'];
				$batch_no = $value['data']['batch_no']; */

				//庫存整理
				if( array_key_exists($depot_code,$deopData) ){
                    $kegTemp = [];
                    if($value['data']['combi_code']==""||$value['data']['combi_code']==null){
                        $productNum = array_key_exists($product_code,$deopData[$depot_code]['product'])?$deopData[$depot_code]['product'][$product_code]:0;
                        $deopData[$depot_code]['product'][(string)$product_code] = (float)$productNum + ( $value['data']['body_num'] * $value['data']['body_rate'] );
                    }else{
                        $data1 = DB::table('AA204_2224')->where('product_code',(string)$product_code)->where('combi_code',$value['data']['combi_code'])->lockForUpdate()->first();
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
                                    'kegData' => $kegTemp
                                );
                            }
                        }
                    }


					if( !empty($sourceKegSub) ){
						foreach( $sourceKegSub as $kKey=>$kValue ){
                            if( $kValue['data']['body_num'] == "" ){
                                $data['subData'][$subDataFormId][$key]['data']['source_keg']['subData'][$kegSubId][$kKey]['data']['body_num'] = null;
                            }
                            if( $kValue['data']['remaining_num'] == "" ){
                                $data['subData'][$subDataFormId][$key]['data']['source_keg']['subData'][$kegSubId][$kKey]['data']['remaining_num'] = null;
                            }
							$deopData[$depot_code]['kegData'][$kValue['data']['keg']] = [
								'num' => $kValue['data']['body_num'],
								'clear' => $value['data']['clear']
							];
						}
					}

				}else{
					$kegTemp = [];
					if( !empty($sourceKegSub) ){

						foreach( $sourceKegSub as $kKey=>$kValue ){
                            if( $kValue['data']['body_num'] == "" ){
                                $data['subData'][$subDataFormId][$key]['data']['source_keg']['subData'][$kegSubId][$kKey]['data']['body_num'] = null;
                            }
                            if( $kValue['data']['remaining_num'] == "" ){
                                $data['subData'][$subDataFormId][$key]['data']['source_keg']['subData'][$kegSubId][$kKey]['data']['remaining_num'] = null;
                            }
							if( $kValue['data']['keg'] ){
								$kegTemp[(string)$kValue['data']['keg']] = [
									'num' => $kValue['data']['body_num'],
									'clear' => $value['data']['clear']
								];
                            }

                        }
					}
if($value['data']['combi_code']==""||$value['data']['combi_code']==null){
    $deopData[$depot_code] = array(
        'name'=>$value['data']['body_depot_name'],
        'product'=>array(
            (string)$product_code=>$value['data']['body_num'] * $value['data']['body_rate']
        ),
        'kegData' => $kegTemp
    );
}else{
    $data1 = DB::table('AA204_2224')->where('product_code',(string)$product_code)->where('combi_code',$value['data']['combi_code'])->lockForUpdate()->first();
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
                'kegData' => $kegTemp
            );
        }
    }
}


                }
			} // end of foreach
			$errorMsg["status"] = false;
			//桶號
			$KegData = DB::table('EA205_80')->get();
			foreach($deopData as $deopCode=>$value){
				foreach($value['product'] as $productCode=>$num){
					$product_kind = $productData->where('product_code',$productCode)->pluck('product_kind')->first();
                    // $combi_code =DB::table('AA204_2224')->where('product_code',(string)$productCode)->where('combi_name',$combi_name)->lockForUpdate()->first();
					if( $product_kind != "費用"){
                        // if(!$combi_code){
						$deopNum = DB::table('EA204_79')->where('depot_code','=',$deopCode)->where('product_code','=',(string)$productCode)->pluck('num')->first();
						if( $deopNum && (float)$num > (float)$deopNum){
							if($verify){
								$tmpArr[] = '警告：產品代碼為"'.$productCode.'" ，分倉庫存不足，請確認';
							}else{
								array_push($tmpArr,[
									"text" => '警告：產品代碼為"'.$productCode.'" ，分倉庫存不足，請確認'
								]);
							}
						}
                        // }else{
                            // $data1 = DB::table('AA204_2224')->where('product_code',$productCode)->where('combi_name',$combi_name)->lockForUpdate()->first();
                            // $subdata =DB::table('AA204_2225')
                            // ->select('*')
                            // ->where('parent_id', $data1->id)
                            // ->get();


                            // foreach($subdata as $key => $val){
                            //     $prototalnum = $val->body_num*$val->body_rate*$totalnum;
                            //     $deopNum = DB::table('EA204_79')->where('depot_code','=',$deopCode)->where('product_code','=',(string)$val->cont_code)->pluck('num')->first();
                            //     if( $deopNum && (float)$prototalnum > (float)$deopNum){
                            //         if($verify){
                            //             $tmpArr[] = '警告：產品代碼為"'.$val->cont_code.'" ，分倉庫存不足，請確認';
                            //         }else{
                            //             array_push($tmpArr,[
                            //                 "text" => '警告：產品代碼為"'.$val->cont_code.'" ，分倉庫存不足，請確認'
                            //             ]);
                            //         }
                            //     }
                            // }
                        // }
                    }
				}
				if( !empty($value['kegData']) ){
					foreach($value['kegData'] as $kegKey=>$kegValue){
						if( $kegKey ){
							$KegNum = $KegData->where('keg','=',(string)$kegKey)->pluck('num')->first();
							if( $kegValue['num'] > $KegNum && !$kegValue['clear'] ){
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
            }
            // dd($deopData);


        }
        // dd($data);
		if( empty($tmpArr) ){
			foreach($deopData as $deopCode=>$value){
				foreach($value['product'] as $productCode=>$num){
					$product_kind = $productData->where('product_code',$productCode)->pluck('product_kind')->first();
					if( $product_kind != "費用"){
						$deopsafeNum = DB::table('EA204_79')->where('depot_code','=',$deopCode)->where('product_code','=',(string)$productCode)->pluck('safe_num')->first();
						$deopNum = DB::table('EA204_79')->where('depot_code','=',$deopCode)->where('product_code','=',(string)$productCode)->pluck('num')->first();
						if($deopsafeNum==""||$deopsafeNum==null){

						}else{
							if($deopNum-$num<$deopsafeNum){
								$user =DB::table('users')->get();
								foreach($user as $key => $val){
									if($val->user_disabled==0){
										DB::table('notifications')
										->insert([
											'user_id' => $val->user_id,
											'notification_text' =>"倉庫代碼".$deopCode."產品代碼".(string)$productCode."，出貨後庫存低於安全庫存量。時間：".Carbon::now()->format('Y-m-d H:i:s'),
											'notification_setting_id' =>0,
											'notification_link' =>"3244",
											'notification_read' =>0,
											'created_by'=> session("user_id"),
											'updated_by'=> session("user_id"),
											'created_at'=>Carbon::now()->format('Y-m-d H:i:s'),
											'updated_at'=>Carbon::now()->format('Y-m-d H:i:s'),
										]);
									}
								}
							}
						}
					}
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

	static public function atSave(&$data,$verify = false)
	{
		if( $verify ){
			self::verifySave($data,"add");
			$page_id = 59;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
		// dd();
		$subDataFormId = 53;
		$totalnum = 0;
		$totalquantity = 0;
		$kegSubId = 72;
		$tmpArr = [];
		$vtable = DB::table('BA201_40');
		if (VerifyUtil::pageVerifyConfirmation(53)) {
			$vtable = $vtable->where("BA201_40.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		$customs = $vtable->select(DB::raw("BA201_41.id,order_no = BA201_41.id,body_quantity,body_rate,client_order_code"))->leftJoin('BA201_41', 'BA201_40.id', '=', 'BA201_41.parent_id');
		$productData = DB::table('AA202_30')->get();
		//修改
		if( $data['status'] == 'update' ){
			DB::table('BA103_82')->where('ship_code', '=', $data['data']['ship_code'])->delete();
		}
		$deopData = [];
        //判斷是否為合約客戶，是的話就扣掉
        $oldBA102data = DB::table('BA102_37')->where('client_code', $data['data']['client_code'])->first();
        if($oldBA102data->yn_cnt_cust){
            //如果餘額不足，就跳出卡控
            if($oldBA102data->cnt_balance < $data['data']['ototal']){
                $LeaveMoney=(float)$oldBA102data->cnt_balance;
                if($verify){
                    $tmpArr[] = "此客戶代碼: {$data['data']['client_code']} 客戶名稱: {$data['data']['client_name']} 餘額剩餘 {$LeaveMoney}，餘額不足，請確認";
                }else{
                    array_push($tmpArr,[
                        "text" => "此客戶代碼: {$data['data']['client_code']} 客戶名稱: {$data['data']['client_name']} 餘額剩餘 {$LeaveMoney}，餘額不足，請確認"
                    ]);
                }
            }
            DB::table('BA102_37')
			->where('client_code', $data['data']['client_code'])
			->update([
				'cnt_balance' => $oldBA102data->cnt_balance - $data['data']['ototal'],
			]);
        }
		if( array_key_exists('subData',$data) ){
			foreach($data['subData'][$subDataFormId] as $datakey => $dataval){
				$custom =$customs->get();
				$product_code = $dataval['data']['product_code'];
				$depot_code = $dataval['data']['body_depot_code'];
				$client_code = $dataval['data']['client_order_code'];
				$order_no = $dataval['data']['order_no'];
				$totalnum = (float)round($dataval['data']['body_num']*$dataval['data']['body_rate'],2);
                $combi = $dataval['data']['combi_code'];
				/* if( $verify ){
					$sourceKegSub = $dataval['data']['source_keg']['subData'][$kegSubId];

				}else{
					$sourceKegSub = $dataval['referencePages']['source_keg']['subData'][$kegSubId];
				} */
				$sourceKegSub = [];

				/* $batch_code =  $dataval['data']['batch_code'];
				$batch_no = $dataval['data']['batch_no']; */
				//批號
				$product_kind = $productData->where('product_code',$product_code)->pluck('product_kind')->first();
				if( $product_kind != "費用" ){
					/* if( !empty($batch_code) ){
						$batchPrefix= mb_substr($batch_code,0,2);
					}else{
						$batchPrefix = "";
					} */
					// $errorText = CommonController::saveBatchCode($batchPrefix,$batch_code,$batch_no,$product_code,$data['data']['ship_code'],$dataval['data']['id'],$data['data']['undertakerday'],$totalnum,$depot_code,true);

					if(  !empty($errorText) ){
						if($verify){
							$tmpArr[] = $errorText['text'];
						}else{
							array_push($tmpArr,[
								"text" => $errorText['text']
							]);
						}
					}
				}

				//修改時 調整單號清空
				if( $data['status'] != 'add' ){
					DB::table('BA202_53')
					->where('id', $dataval['data']['id'])
					->update([
						'clear_num' => '',
						'keg_code' => ''
					]);
				}
				//桶號調整額
				$numTatal = 0;
				$remainingNumTotal = 0;
				$kegStatus = 1;
				if( $sourceKegSub ){
					foreach( $sourceKegSub as $KegSubKey => $KegSubVal ){
						$numTatal = (float)round((float)$numTatal + (float)$KegSubVal['data']['body_num'],2);
						$remainingNumTotal = (float)round((float)$remainingNumTotal + (float)$KegSubVal ['data']['remaining_num'],2);
					}
					if( $dataval['data']['clear'] ){//出清時
						if( $totalnum > $remainingNumTotal ){
							$deductNum = (float)round(($totalnum - $remainingNumTotal)/count($sourceKegSub),2);
						}else{
							$kegStatus = 2;
						}
					}else{
						$deductNum = 0;
					}
					//扣除桶號及整理帶調整庫存
					foreach( $sourceKegSub as $kKey=>$kValue ){
						if( $kValue['data']['keg'] ){
							$depotNum = DB::table('EA204_79')->where('depot_code', $depot_code)->where('product_code', (string)$product_code)->pluck('num')->first();
							$kegnums =DB::table('EA205_80')->where('keg', $kValue['data']['keg'])->lockForUpdate()->first();
							$kegnum =$kegnums->num;
							$diffNum = (float)round((float)$kValue['data']['body_num'] - (float)$kegnum,2);
							$updateArr = [];
							if( $kegStatus == 2 ){
								$deductNum =  (float)round((float)$kValue['data']['body_num'] - (float)$kValue['data']['remaining_num'],2);
							}
							if( $dataval['data']['clear'] || $diffNum == 0 ){
								$updateArr = [
									'num' => 0,
									'product_code' => '',
									'product_name' => '',
									'unit_code' => '',
									'unit_name' => '',
								];
							}else{
								$updateArr = [
									'num' => (float)round((float)$kegnum - (float)$kValue['data']['body_num'],2)
								];
							}
							//桶號扣除
							DB::table('EA205_80')
							->where('keg', $kValue['data']['keg'])
							->update($updateArr);

							if( $dataval['data']['clear']){
								if( array_key_exists($depot_code,$deopData) ){
									$unit_code = $productData->where('product_code','=',$dataval['data']['product_code'])->pluck('unit_code')->first();
									$unit_name = $productData->where('product_code','=',$dataval['data']['product_code'])->pluck('unit_name')->first();
									/*$productNum = array_key_exists($product_code,$deopData[$depot_code]['product'])?$deopData[$depot_code]['product'][$product_code]:0;
									$deopData[$depot_code]['product'][$product_code] = (float)$productNum + ( $dataval['data']['body_num'] * $dataval['data']['body_rate'] ); */
									$deopData[$depot_code]['keg'][$kValue['data']['keg']]['product_code'] = $dataval['data']['product_code'];
									$deopData[$depot_code]['keg'][$kValue['data']['keg']]['product_name'] = $dataval['data']['product_name'];
									$deopData[$depot_code]['keg'][$kValue['data']['keg']]['body_num']= $deductNum;
									$deopData[$depot_code]['keg'][$kValue['data']['keg']]['unit_code'] = $unit_code;
									$deopData[$depot_code]['keg'][$kValue['data']['keg']]['unit_name'] = $unit_name;
									$deopData[$depot_code]['keg'][$kValue['data']['keg']]['body_remarks'] = "原數量(".$kegnum.")";
									$deopData[$depot_code]['keg'][$kValue['data']['keg']]['depot_name'] = $dataval['data']['body_depot_name'];
									$deopData[$depot_code]['keg'][$kValue['data']['keg']]['depot_code'] = $depot_code;
									$deopData[$depot_code]['depot_name'] = $dataval['data']['body_depot_name'];
									$deopData[$depot_code]['keg'][$kValue['data']['keg']]['depot_num'] = $depotNum;
								}else{
									$unit_code = $productData->where('product_code','=',$dataval['data']['product_code'])->pluck('unit_code')->first();
									$unit_name = $productData->where('product_code','=',$dataval['data']['product_code'])->pluck('unit_name')->first();
									$deopData[$depot_code] = array(
										'depot_name'=>$dataval['data']['body_depot_name'],
										/*'product'=>array(
											$product_code=>$dataval['data']['body_num'] * $dataval['data']['body_rate']
										),*/
										'keg' => array(
											$kValue['data']['keg'] => [
												'product_code' => $dataval['data']['product_code'],
												'product_name' => $dataval['data']['product_name'],
												'body_num' => $deductNum,
												'unit_code' => $unit_code,
												'unit_name' => $unit_name,
												'body_remarks' => "原數量(".$kegnum.")",
												'depot_name' => $dataval['data']['body_depot_name'],
												'depot_code' => $depot_code,
												'depot_num' => $depotNum
											]
										)
									);
								}
							}
						}
					}
				}
				if( $product_kind != "費用" ){
					//庫存需減少數量
					CommonController::updateDepot($product_code,$depot_code,"subtraction",$totalnum,$combi);
				}
				//回寫到廠商購買價
				DB::table('BA103_82')
					->insert([
						'client_code' => $data['data']['client_code'],
						'client_name' => $data['data']['client_name'],
						'product_code' => $product_code,
						'product_name' => $dataval['data']['product_name'],
						'unit_code' => $dataval['data']['unit_code'],
						'unit_name' => $dataval['data']['unit_name'],
						'body_price' => $dataval['data']['body_price'],
						'ship_date' => $data['data']['ship_date'],
						'source_type' => 'Output',
						'ship_code' => $data['data']['ship_code'],
                        'body_rate' => $dataval['data']['body_rate'],
					]);
				//表身訂單單號跟訂單NO不為空時，需加回客戶訂單表身的已交量
				if( !is_null($dataval['data']['client_order_code']) && !is_null($dataval['data']['order_no']) ){
					$customBody = $custom->where('client_order_code','=',$client_code)->where('order_no','=',$order_no)->first();
					if( empty($customBody) ){
						if($verify){
							$tmpArr[] = "此訂單{$client_code},NO{$order_no}目前無法使用，請確認";
						}else{
							array_push($tmpArr,[
								"text" => "此訂單{$client_code},NO{$order_no}目前無法使用，請確認"
							]);
						}
					}else{
						$customBodyId = $customBody->id;
						$body_quantity = $customBody->body_quantity;
						$body_rate = $customBody->body_rate;
						$totalquantity = (float)round($body_quantity + ($totalnum / $body_rate),2);
						DB::table('BA201_41')
							->where('id', $customBodyId)
							->update([
								'body_quantity' => $totalquantity,
							]);
					}

				}
			}//end of foreach

			$depotPageId = '58';
			$kegPageId = '1070';
			$ship_id = DB::table('BA202_52')->where('ship_code', $data['data']['ship_code'])->pluck('id')->first();
			//轉出庫存調整單，桶號調整單
			foreach($deopData as $depotkey => $depotval ){
				$depotNumber = CommonController::generateDocumentNumber($depotPageId,'adjust_code');
				$kegNumber = CommonController::generateDocumentNumber($kegPageId,'keg_code');
				//倉庫調整單
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

				$headdata = DB::table('EA201_50')
					->insertGetId([
						'adjust_code' => $depotNumber,
						'undertakerday' => $data['data']['ship_date'],
						'undertaker' => 'SYSTEM',
						'undertakername' => 'SYSTEM',
						'depot_code' => (string)$depotkey,
						'depot_name' => $depotval['depot_name'],
						'remarks' => "出貨單(".$data['data']['ship_code'].")出清補正",
						'source_code' => $data['data']['ship_code'],
						'data_options' => json_encode($dataOptions)
					]);
				//桶號調整單
				$keghead = DB::table('EA206_1069')
					->insertGetId([
						'keg_code' => $kegNumber,
						'undertakerday' => $data['data']['ship_date'],
						'undertaker' => 'SYSTEM',
						'undertakername' => 'SYSTEM',
						'remarks' => "出貨單(".$data['data']['ship_code'].")出清補正",
						'source_code' => $data['data']['ship_code'],
						'data_options' => json_encode($dataOptions),
					]);

				$kegData = $depotval['keg'] ;
				if( $kegData ){
					foreach($kegData as $itemkey => $itemval){
						$unit_code = $productData->where('product_code','=',$itemval['product_code'])->pluck('unit_code')->first();
						$unit_name = $productData->where('product_code','=',$itemval['product_code'])->pluck('unit_name')->first();
						DB::table('EA201_51')
							->insert([
								'parent_id' => $headdata,
								'product_code' => $itemval['product_code'],
								'product_name' => $itemval['product_name'],
								'body_num' => $itemval['body_num'],
								'unit_code' => $unit_code,
								'unit_name' => $unit_name,
								'body_rate' => '1',
								'body_remarks' => "原數量(".$itemval['depot_num'].")"
							]);
						DB::table('EA206_1070')
							->insert([
								'parent_id' => $keghead,
								'keg' => $itemkey,
								'product_code' => $itemval['product_code'],
								'product_name' => $itemval['product_name'],
								'body_num' => $itemval['body_num'],
								'unit_code' => $unit_code,
								'unit_name' => $unit_name,
								'depot_code' => (string)$depotkey,
								'depot_name' => $itemval['depot_name'],
								'body_remarks' => $itemval['body_remarks'],
							]);

						DB::table('BA202_53')
						->where('keg', 'Y')
						->where('parent_id', $ship_id)
						->where('body_depot_code', (string)$depotkey)
						->update([
							'clear_num' => $depotNumber,
							'keg_code' => $kegNumber
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
			$page_id = 59;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$warm = [];
		}else{
			$warm = '';
		}
        $totalnum = 0;
		$subDataFormId = 53;
		$kegSubId = 72;
		$shipBack = DB::table('BA203_62')->get();

        foreach($data['subData'][$subDataFormId] as $key => $val){

            $totalnum = $val['data']['body_num'] * $val['data']['body_rate'];
			// $sourceKegSub = $val['data']['source_keg']['subData'][$kegSubId];
			$sourceKegSub = [];
			if( $sourceKegSub ){
				foreach( $sourceKegSub as $kKey=>$kValue ){
					$kegProductCode =DB::table('EA205_80')->where('keg', $kValue['data']['keg'])->pluck('product_code')->first();
					if( $kegProductCode !=  $val['data']['product_code'] && $kegProductCode ){
						if($verify){
							$warm[] = "警告：桶號為 {$kValue['data']['keg']} ，已放置其他產品";
						}else{
							$warm = $warm."警告：桶號為 {$kValue['data']['keg']} ，已放置其他產品。 \n";
						}
					}
				}
			}

            $shipBackData = $shipBack->where('ship_no', $val['data']['id'])->all();
            if(count($shipBackData)>0){
				if($verify){
					$warm[] = "警告：產品代碼為 {$val['data']['product_code']} ，已有出貨退回紀錄。";
				}else{
					$warm = $warm."警告：產品代碼為 {$val['data']['product_code']} ，已有出貨退回紀錄。 \n";
				}
            }
		}
		return $warm;
	}

	static public function atDelete(&$data,$verify = false)
	{

		if($verify){
			$page_id = 59;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
			self::verifySave($data,"delete");
			$warm = [];
		}else{
			$warm = '';
		}
        //判斷是否為合約客戶，是的話要把金額還回去
        //$oldBA102data = DB::table('BA102_37')->where('client_code', $data['data']['client_code'])->first();
		$customs = DB::table('BA201_41')->get();
      //  if($oldBA102data->yn_cnt_cust){
       //     DB::table('BA102_37')
      //      ->where('client_code', $data['data']['client_code'])
      //      ->update([
      //          'cnt_balance' => $oldBA102data->cnt_balance + $data['data']['ototal'],
      //      ]);
      //  }
		$totalnum = 0;
        $totalquantity = 0;
		$subDataFormId = 53;
		$kegSubId = 72;
		$customs = DB::table('BA201_41')->get();
		$productData = DB::table('AA202_30')->get();
        foreach($data['subData'][$subDataFormId] as $key => $val){
            //庫存先將原本的舊資料增加
            $combi = $val['data']['combi_code'];
            $totalnum = (float)round($val['data']['body_num'] * $val['data']['body_rate'],2);
			CommonController::updateDepot($val['data']['product_code'],$val['data']['body_depot_code'],"addition",$totalnum,$combi);
			//表身訂單單號跟訂單NO不為空時，需扣除客戶訂單表身的已交量
			if( !is_null($val['data']['order_no']) ){
                $oldquantity = DB::table('BA201_41')
                        ->where('id', $val['data']['order_no'])
                        ->first();
                $newquantity = $oldquantity->body_quantity - ($totalnum / $oldquantity->body_rate);
                DB::table('BA201_41')
                        ->where('id', $val['data']['order_no'])
                        ->update([
                            'body_quantity' => $newquantity,
                        ]);
            }

			// $sourceKegSub = $val['data']['source_keg']['subData'][$kegSubId];
			$sourceKegSub = [];
			$unit_code = DB::table('AA202_30')->where('product_code','=',$val['data']['product_code'])->pluck('unit_code')->first();
			$unit_name = DB::table('AA202_30')->where('product_code','=',$val['data']['product_code'])->pluck('unit_name')->first();
			if( $sourceKegSub ){
				foreach( $sourceKegSub as $kegKey=>$keyVal){
					if( $keyVal['data']['keg'] ){
						if( !is_null($val['data']['clear']) ){
							$newnum = $keyVal['data']['remaining_num'];
						}else{
							$oldKegNum = DB::table('EA205_80')->where('keg', $keyVal['data']['keg'])->pluck('num')->first();
							$newnum = (float)round($oldKegNum + $keyVal['data']['body_num'],2);
						}
						DB::table('EA205_80')->where('keg', $keyVal['data']['keg'])
							->update([
								'num' => $newnum,
								'product_code' => $val['data']['product_code'],
								'product_name' => $val['data']['product_name'],
								'unit_code' => $unit_code,
								'unit_name' => $unit_name,
							]);
					}
				}
			}

			//將批號刪掉
			// CommonController::deleteBatchCode("CA", $val['data']['batch_code'],$val['data']['batch_no'],$data['data']['ship_code'], $val['data']['id']);
			// CommonController::deleteBatchCode("DA", $val['data']['batch_code'],$val['data']['batch_no'],$data['data']['ship_code'], $val['data']['id']);
		}
		$deopAdj = DB::table('EA201_50 as a')->select(DB::raw("a.id as parent_id,a.source_code,a.depot_code,b.*"))->leftJoin('EA201_51 as b', 'a.id', '=', 'b.parent_id')->get();
		$kegAdj = DB::table('EA206_1069 as a')->select(DB::raw("a.id as parent_id,a.source_code,b.*"))->leftJoin('EA206_1070 as b', 'a.id', '=', 'b.parent_id')->get();
		//刪除倉庫調整數單
		$deopAdjData = $deopAdj
				->where('source_code',$data['data']['ship_code'])
				->all();
		if( !empty ($deopAdjData) ){
			foreach($deopAdjData as $adjKey=>$adjValue){
				//刪除庫存調整單表身
				DB::table('EA201_51')->where('parent_id', '=', $adjValue->parent_id)->delete();
			}
		}
		//刪除庫存調整單表頭
		DB::table('EA201_50')->where('source_code', '=', $data['data']['ship_code'])->delete();

		//刪除桶號調整單
		$kegAdjData = $kegAdj->where('source_code',$data['data']['ship_code'])->all();
		if( !empty ($kegAdjData) ){
			foreach($kegAdjData as $adjKey=>$adjValue){
				//刪除桶號調整單表身
				DB::table('EA206_1070')->where('parent_id', '=', $adjValue->parent_id)->delete();
			}
			//刪除桶號調整單表頭
			DB::table('EA206_1069')->where('source_code', '=', $data['data']['ship_code'])->delete();
		}
        //刪除廠商購買價
		DB::table('BA103_82')->where('ship_code', '=', $data['data']['ship_code'])->delete();
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
    {}
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
			// self::atSave($data);
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
