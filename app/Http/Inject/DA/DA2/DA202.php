<?php

namespace App\Http\Inject\DA\DA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class DA202 extends InjectBase
{
	static public function verifySave(&$data,$status){
		foreach($data['subData'][57] as $key => $val){
			foreach($data['subData'][57][$key]['subData'][58] as $datakey => $dataval){
				if( $status == "add" ){
					DB::table('DA202_58')
					->where('id', $dataval['data']['id'])
					->update([
						'batch_code' => $dataval['data']['batch_code'],
						'batch_no' => $dataval['data']['batch_no'],
					]);
				}else{
					DB::table('DA202_58')
					->where('id', $dataval['data']['id'])
					->update([
						'batch_code' => null,
						'batch_no' => null,
					]);
				}
				
			}				
		}
	}
	static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 61;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
		$errorarr = [];
		$subDataFormId = 57;
        if($data['status'] == 'update'){
            $oldbodydata = DB::table('DA202_57')
                    ->select('*')
                    ->where('parent_id', $data['data']['id'])
					->get();
            //產品
            foreach($oldbodydata as $key => $val){
				//批號管理為Y時，此列表不能更動
				if( !$val->batch ){
					//將完工單的產品對應的倉庫扣除數量
					$prototalnum = $val->body_num*$val->body_rate;
					CommonController::updateDepot($val->product_code,$val->depot_code,"subtraction",$prototalnum);
					//將已回寫到加工單的已交量先扣除
                    $bodymachining = DB::table('DA201_45')
                            ->select('id','body_quantity','body_rate')
                            ->where('id', $val->machining_no)
                            ->get();
					foreach($bodymachining as $bmkey => $bmval){
						$newquantity = $bmval->body_quantity - ($prototalnum/$bmval->body_rate);
						DB::table('DA201_45')
								->where('id', $bmval->id)
								->update(['body_quantity' => $newquantity]);
					}
					//需先刪除桶號庫存查詢的該筆桶號
					if($val->keg != null && $val->keg != ""){
                        $da202keg = DB::table('DA202_57')
                                ->select('*')
                                ->where('keg', $val->keg)
                                ->get();
						if(count($da202keg)==1){
							$ba202keg = DB::table('BA202_53')->select('*')->where('keg', $val->keg)->get();
							$ea206keg = DB::table('EA206_1070')->select('*')->where('keg', $val->keg)->get();
							if(count($ba202keg)>0 || count($ea206keg)>0){

							}else{
								$kegdata = DB::table('EA205_80')
                                     ->select('keg','product_code','num','depot_code')
                                     ->where('keg', $val->keg)
                                     ->first();
                                if($kegdata->product_code == $val->product_code && $kegdata->depot_code == $val->depot_code){
                                    $kegnewnum = $kegdata->num - $prototalnum;
                                    $usedkeg = DB::table('DA202_57')
                                         ->select('*')
                                         ->where('keg', $val->keg)
                                         ->get();
                                    if(count($usedkeg)==1){
                                        if($kegnewnum <= 0){
                                            DB::table('EA205_80')
                                                ->where('keg', $val->keg)
                                                ->delete();
                                        }else{
                                            DB::table('EA205_80')
                                                ->where('keg', $val->keg)
                                                ->update(['num' => $kegnewnum]);
                                        }
                                    }else if(count($usedkeg)>1){
                                        if($kegnewnum <= 0){
                                            DB::table('EA205_80')
                                                ->where('keg', $val->keg)
                                                ->update([
                                                    'product_code' => '',
                                                    'product_name' => '',
                                                    'num' => $kegnewnum,
                                                    'unit_name' => '',
                                                    'unit_code' => '',
                                                ]);
                                        }else{
                                            DB::table('EA205_80')
                                                ->where('keg', $val->keg)
                                                ->update(['num' => $kegnewnum]);
                                        }
                                    }
                                }
							}
						}else if(count($da202keg)>1){
							//桶號、產品、倉庫都相同時，扣除數量
							$ea205keg = DB::table('EA205_80')
								->select('*')
								->where('keg', $val->keg)
								->where('product_code', $val->product_code)
								->where('depot_code', $val->depot_code)
                                ->get();
                            if(count($ea205keg)>0){
                                $totalkegnum = $ea205keg[0]->num - $prototalnum;
                                if($totalkegnum <= 0){
                                    DB::table('EA205_80')
                                        ->where('keg', $val->keg)
                                        ->update([
                                            'product_code' => '',
                                            'product_name' => '',
                                            'num' => $totalkegnum,
                                            'unit_name' => '',
                                            'unit_code' => '',
                                        ]);
                                }else{
                                    DB::table('EA205_80')
                                        ->where('keg', $val->keg)
                                        ->where('product_code', $val->product_code)
                                        ->where('depot_code', $val->depot_code)
                                        ->update(['num' => $totalkegnum]);
                                }
                            }else{
								if($verify){
									$errorarr[] = '警告：原桶號 "'.$val->keg.'"，已被其他產品使用，故不可修改該筆資料';
								}else{
									array_push($errorarr,[
										"text" => '警告：原桶號 "'.$val->keg.'"，已被其他產品使用，故不可修改該筆資料'
									]);
								}
                                
                            }
						}
					}
					//子件
                    $oldcomponentdata = DB::table('DA202_58')
                            ->select('*')
                            ->where('parent_id', $val->id)
                            ->get();
					foreach($oldcomponentdata as $comkey => $comval){
						$totalnum = $comval->component_num * $comval->component_rate;
						CommonController::updateDepot($comval->component_code,$comval->component_depot,"addition",$totalnum);
                        //將批號刪掉
                        CommonController::deleteBatchCode("CA",$comval->batch_code,$comval->batch_no,$data['data']['finished_code'],$comval->id);

					}
				}
            }//end of foreach
        }
		if( $verify ){
			if( empty($errorarr) ){
				$errorarr = self::atSave($data,true);
			}
		}
		return $errorarr;
	}
	static public function atSave(&$data,$verify = false)
	{
		if( $verify ){
			self::verifySave($data,"add");
			$page_id = 61;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			$data = PageUtil::getData($pageData, $data['data']['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
		
		$totalnum = 0;
        $isOK = true;
		$errorarr = [];
		foreach($data['subData'][57] as $bodykey => $bodyval){ //foreach 1
			if( !$bodyval['data']['batch']){
				foreach($data['subData'][57][$bodykey]['subData'][58] as $datakey => $dataval){
                    $batch_code =  $dataval['data']['batch_code'];
                    $batch_no = $dataval['data']['batch_no'];
                    $component_code = $dataval['data']['component_code'];
                    $totalnum = $data['subData'][57][$bodykey]['subData'][58][$datakey]['data']['component_num'] * $data['subData'][57][$bodykey]['subData'][58][$datakey]['data']['component_rate'];
					$component_depot = $dataval['data']['component_depot'];
					/** */
					$errorText = CommonController::saveBatchCode("CA",$batch_code,$batch_no,$component_code,$data['data']['finished_code'],$dataval['data']['id'],$data['data']['undertakerday'],$totalnum,$component_depot);
					if( !empty($errorText) ){
						if($verify){
							$errorarr[] = $errorText['text'];
						}else{
							array_push($errorarr,["text"=>$errorText]['text']);
						}                            
					}
					/** */
					$oldnum = DB::table('EA204_79')
							 ->select('num')
							 ->where('product_code', $data['subData'][57][$bodykey]['subData'][58][$datakey]['data']['component_code'])
							 ->where('depot_code', $data['subData'][57][$bodykey]['subData'][58][$datakey]['data']['component_depot'])
							 ->get();
					if(count($oldnum)>0){
						if((float)$totalnum > (float)($oldnum[0]->num)){
							//卡控子件表身數量大於倉庫數量時，不能保存
							$isOK = false;
							if($verify){
								$errorarr[] = '警告：子件代碼【"'.$data['subData'][57][$bodykey]['subData'][58][$datakey]['data']['component_code'].'"】，扣料庫存不足，請確認';
							}else{
								array_push($errorarr,[
									"text"=>'警告：子件代碼【"'.$data['subData'][57][$bodykey]['subData'][58][$datakey]['data']['component_code'].'"】，扣料庫存不足，請確認'
								]);
							}							
						}else{
							//驗證通過，子件明細：庫存數量需扣除子件的數量
							$newnum = $oldnum[0]->num - $totalnum;
							DB::table('EA204_79')
							->where('product_code', $data['subData'][57][$bodykey]['subData'][58][$datakey]['data']['component_code'])
							->where('depot_code', $data['subData'][57][$bodykey]['subData'][58][$datakey]['data']['component_depot'])
							->update(['num' => $newnum]);
						}
					}

				}
				if($isOK == true){
					//驗證通過
					//將已交量回寫加工單對應明細的已交量
					$prototalnum = $data['subData'][57][$bodykey]['data']['body_num']*$data['subData'][57][$bodykey]['data']['body_rate'];
                    $bodymachining = DB::table('DA201_45 as a')
                            ->leftJoin('DA201_44 as b', 'b.id', '=', 'a.parent_id')
                            ->select('a.id','a.body_num','a.body_quantity','a.body_rate')
                            ->where('a.id', $data['subData'][57][$bodykey]['data']['machining_no']);
                    if (VerifyUtil::pageVerifyConfirmation(56)) {
                        $bodymachining = $bodymachining->where("b.data_options", "LIKE", '%"verify":{%"level":255%');
                    }
                    $bodymachining = $bodymachining->get();
					foreach($bodymachining as $bmkey => $bmval){
                        if( empty($bodymachining) ){
							if($verify){
								$errorarr[] = '警告：加工單號 "'.$data['subData'][57][$bodykey]['data']['machining_code'].'"  的加工NO"'.$data['subData'][57][$bodykey]['data']['machining_no'].'"，目前無法使用，請確認';
							}else{
								array_push($errorarr,[
									"text"=>'警告：加工單號 "'.$data['subData'][57][$bodykey]['data']['machining_code'].'"  的加工NO"'.$data['subData'][57][$bodykey]['data']['machining_no'].'"，目前無法使用，請確認'
								]);
							}                            
                        }else{
                            $newquantity = $prototalnum/$bmval->body_rate+$bmval->body_quantity;
							DB::table('DA201_45')
								->where('id', $bmval->id)
								->update(['body_quantity' => $newquantity]);
                        }
						
					}
					//當桶號有輸入時，需新增桶號庫存資料
					if($data['subData'][57][$bodykey]['data']['keg'] != null && $data['subData'][57][$bodykey]['data']['keg'] != ""){  
						$newkegnum = $data['subData'][57][$bodykey]['data']['body_num'] * $data['subData'][57][$bodykey]['data']['body_rate']; 
						$kegdata = DB::table('EA205_80')
								->select('*')
								->where('keg', $data['subData'][57][$bodykey]['data']['keg'])
								->get();
						$productdata = DB::table('AA202_30')
							 ->select('*')
							 ->where('product_code', $data['subData'][57][$bodykey]['data']['product_code']);
                        if (VerifyUtil::pageVerifyConfirmation(49)) {
                            $productdata = $productdata->where("AA202_30.data_options", "LIKE", '%"verify":{%"level":255%');
                        }
                        $productdata = $productdata->get();
						if(count($kegdata)>0){
							foreach($kegdata as $okegkey => $okegval){
								if($okegval->product_code == $data['subData'][57][$bodykey]['data']['product_code'] && $okegval->depot_code == $data['subData'][57][$bodykey]['data']['depot_code']){
									//桶號、產品、倉庫都相同時，寫入數量
									$totalkegnum = $okegval->num + $newkegnum;
									DB::table('EA205_80')
											->where('id', $okegval->id)
											->update(['num' => $totalkegnum]);
								}else if($okegval->product_code == null && $okegval->depot_code == $data['subData'][57][$bodykey]['data']['depot_code']){
									DB::table('EA205_80')
											->where('id', $okegval->id)
											->update([
												'product_code' => $data['subData'][57][$bodykey]['data']['product_code'],
												'product_name' => $data['subData'][57][$bodykey]['data']['product_name'],
												'num' => $newkegnum,
												'unit_name' => $productdata[0]->unit_name,
												'unit_code' => $productdata[0]->unit_code,
											]);
								}else{
									if($verify){
										$errorarr[] = '警告：桶號 "'.$data['subData'][57][$bodykey]['data']['keg'].'"，已存在相同桶號資料，請填寫其他桶號';
									}else{
										array_push($errorarr,[
											"text"=>'警告：桶號 "'.$data['subData'][57][$bodykey]['data']['keg'].'"，已存在相同桶號資料，請填寫其他桶號'
										]);
									}									
								}
							}
						}else{
                            $dataOption = [
                                'verify' => [
                                    'level' => 255
                                ]
                            ];
							DB::table('EA205_80')
							->insert([
								'keg' => $data['subData'][57][$bodykey]['data']['keg'],
								'product_code' => $data['subData'][57][$bodykey]['data']['product_code'],
								'product_name' => $data['subData'][57][$bodykey]['data']['product_name'],
								'num' => $newkegnum,
								'unit_name' => $productdata[0]->unit_name,
								'unit_code' => $productdata[0]->unit_code,
								'depot_code' => $data['subData'][57][$bodykey]['data']['depot_code'],
								'depot_name' => $data['subData'][57][$bodykey]['data']['depot_name'],
								'data_options' => json_encode($dataOption),
							]);
						}
					}
					//完工明細：庫存數量需增加產品的數量
					CommonController::updateDepot($data['subData'][57][$bodykey]['data']['product_code'],$data['subData'][57][$bodykey]['data']['depot_code'],"addition",$prototalnum);
				}
			}//end of if
		}//end of foreach 1
		return $errorarr;
	}
	static public function bfDelete(&$data,$verify = false)
	{
		// dd($data);
		if($verify){
			$page_id = 61;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$warm = [];
		}else{
			$warm = '';
		}
		$batchExist = false;
        foreach($data['subData'][57] as $key => $val){
			if( $val['data']['batch'] ){
				if($verify){
					$warm[] = "此完工單已有批號管理";
				}else{
					$warm = $warm . "此完工單已有批號管理 \n";
				}
				break;
			}
            $totalnum = $data['subData'][57][$key]['data']['body_num'] * $data['subData'][57][$key]['data']['body_rate'];
            $depotnum = DB::table('EA204_79')
                     ->where('product_code', $data['subData'][57][$key]['data']['product_code'])
                     ->where('depot_code', $data['subData'][57][$key]['data']['depot_code'])
                     ->pluck('num');
            if(count($depotnum)>0 && $totalnum > $depotnum[0]){
				if($verify){
					$warm[] = "產品代碼為{$data['subData'][57][$key]['data']['product_code']}，產出倉庫存不足";
				}else{
					$warm = $warm . "產品代碼為{$data['subData'][57][$key]['data']['product_code']}，產出倉庫存不足 \n";
				}
            }
            //將已回寫到加工單的已交量先扣除
            $bodymachining = DB::table('DA201_45')
                     ->select('id','body_quantity','body_rate')
                     ->where('id', $data['subData'][57][$key]['data']['machining_no'])
                     ->get();
            foreach($bodymachining as $bmkey => $bmval){
                $newquantity = $bmval->body_quantity - ($totalnum/$bmval->body_rate);
                if($newquantity < 0){
					if($verify){
						$warm[] = "工單號碼為{$data['subData'][57][$key]['data']['machining_code']}，的工單NO{$data['subData'][57][$key]['data']['machining_no']}數量大於加工單的已交數量";
					}else{
						$warm = $warm . "工單號碼為{$data['subData'][57][$key]['data']['machining_code']}，的工單NO{$data['subData'][57][$key]['data']['machining_no']}數量大於加工單的已交數量 \n";
					}
                }
            }
            
            //無對應的桶號庫存不可刪除
            if($data['subData'][57][$key]['data']['keg'] != null){
                $kegdata = DB::table('EA205_80')
                     ->select('keg','product_code','num','depot_code')
                     ->where('keg', $data['subData'][57][$key]['data']['keg'])
                     ->where('product_code', $data['subData'][57][$key]['data']['product_code'])
                     ->where('depot_code', $data['subData'][57][$key]['data']['depot_code'])
                     ->get();
                if(count($kegdata)>0){
                    if((float)$kegdata[0]->num < $data['subData'][57][$key]['data']['body_num'] * $data['subData'][57][$key]['data']['body_rate']){
						if($verify){
							$warm[] = "桶號為{$data['subData'][57][$key]['data']['keg']}，桶號庫存查詢數量不足";
						}else{
							$warm = $warm . "桶號為{$data['subData'][57][$key]['data']['keg']}，桶號庫存查詢數量不足 \n";
						}
                    }
                }else{
					if($verify){
						$warm[] = "桶號為{$data['subData'][57][$key]['data']['keg']}，找不到對應的桶號庫存查詢資料";
					}else{
						$warm = $warm . "桶號為{$data['subData'][57][$key]['data']['keg']}，找不到對應的桶號庫存查詢資料 \n";
					}
                }
            }
		}//end of foreach
		return $warm;
	}
	static public function atDelete(&$data,$verify = false)
	{
		if($verify){
			$page_id = 61;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['data']['id']);
			self::verifySave($data,"delete");
		}
		//產品
        foreach($data['subData'][57] as $key => $val){
            //將完工單的產品對應的倉庫扣除數量
            $prototalnum = $data['subData'][57][$key]['data']['body_num'] * $data['subData'][57][$key]['data']['body_rate'];
			CommonController::updateDepot($data['subData'][57][$key]['data']['product_code'],$data['subData'][57][$key]['data']['depot_code'],"subtraction",$prototalnum);
            //將已回寫到加工單的已交量先扣除
            $bodymachining = DB::table('DA201_45')
                     ->select('id','body_quantity','body_rate')
                     ->where('id', $data['subData'][57][$key]['data']['machining_no'])
                     ->get();
            foreach($bodymachining as $bmkey => $bmval){
                $newquantity = $bmval->body_quantity - ($prototalnum/$bmval->body_rate);
                DB::table('DA201_45')
                        ->where('id', $bmval->id)
                        ->update(['body_quantity' => $newquantity]);
            }
            //需先刪除桶號庫存查詢的該筆桶號
            if($data['subData'][57][$key]['data']['keg'] != null){
                $kegdata = DB::table('EA205_80')
                     ->select('keg','product_code','num','depot_code')
                     ->where('keg', $data['subData'][57][$key]['data']['keg'])
                     ->first();
                if($kegdata->product_code == $data['subData'][57][$key]['data']['product_code'] && $kegdata->depot_code == $data['subData'][57][$key]['data']['depot_code']){
                    $kegnewnum = $kegdata->num - $data['subData'][57][$key]['data']['body_num'] * $data['subData'][57][$key]['data']['body_rate'];
//                    dd($kegdata->num);
                    $usedkeg = DB::table('DA202_57')
                         ->where('keg', $data['subData'][57][$key]['data']['keg'])
                         ->get();
//                    dd(count($usedkeg));
                    if(count($usedkeg)==0){
                        if($kegnewnum == 0){
                            DB::table('EA205_80')
                                ->where('keg', $data['subData'][57][$key]['data']['keg'])
                                ->delete();
                        }else{
                            DB::table('EA205_80')
                                ->where('keg', $data['subData'][57][$key]['data']['keg'])
                                ->update(['num' => $kegnewnum]);
                        }
                    }else if(count($usedkeg)>=1){
                        if($kegnewnum <= 0){
                            DB::table('EA205_80')
                                ->where('keg', $data['subData'][57][$key]['data']['keg'])
                                ->update([
                                    'product_code' => '',
                                    'product_name' => '',
                                    'num' => $kegnewnum,
                                    'unit_name' => '',
                                    'unit_code' => '',
                                ]);
                        }else{
                            DB::table('EA205_80')
                                ->where('keg', $data['subData'][57][$key]['data']['keg'])
                                ->update(['num' => $kegnewnum]);
                        }
                    }
                    
                }
            }
            //子件
            foreach($data['subData'][57][$key]['subData'][58] as $comkey => $comval){
                $totalnum = $data['subData'][57][$key]['subData'][58][$comkey]['data']['component_num'] * $data['subData'][57][$key]['subData'][58][$comkey]['data']['component_rate'];
				CommonController::updateDepot($data['subData'][57][$key]['subData'][58][$comkey]['data']['component_code'],$data['subData'][57][$key]['subData'][58][$comkey]['data']['component_depot'],"addition",$totalnum);
                //將批號刪掉
                CommonController::deleteBatchCode("CA", $comval['data']['batch_code'],$comval['data']['batch_no'],$data['data']['finished_code'], $comval['data']['id']);
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
		$pageId = $pageData['page']['page_id'];
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
			DB::beginTransaction();
			$tmpArr = self::bfSave($data);
			// dd($tmpArr);
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
		if( array_key_exists('finished_code',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'finished_code',$dataset["data"]["finished_code"]);
			$dataset['data']['finished_code'] = $number;
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
		// dd($data['status']);
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
