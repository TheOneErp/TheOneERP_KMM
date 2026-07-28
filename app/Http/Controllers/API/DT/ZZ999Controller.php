<?php
namespace App\Http\Controllers\API\DT;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CommonController;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;
use App\Utils\Excel;
use App\Utils\FileUtil;
use App\Utils\VerifyUtil;
use Carbon\Carbon;



class ZZ999Controller extends Controller{
	static public function getImport(Request $request)
	{
		// dd($request->sele_form,$request->file);
		$sele_form = $request->sele_form;
		$file = $request->file;
		if( !$sele_form || $sele_form == 'null' || !$file || $file == 'null' ){
			return array(
				'status'=> 'fail',
				'message' => '請選擇匯入的表單及上傳檔案',
				'data'=> null
			);
		}
		$dataOptions['verify'] = [
			'level' => 255,
			"population" => [
				255 => [
					"-1" =>[
						[
							"verify_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"user_id" => 2,
							"name" => "Admin"
						]
					]
				]
			],
		];
		// update(['data_options' => json_encode($dataOptions)]
		$excel = new Excel($file);
		$tmpArr = [];
		$duplicateArr = [];
		DB::beginTransaction();
		switch ($sele_form) {
			case "幣別設定":
				$res = DB::table("AA101_19")->get();
				$result = $excel->getSheetData('幣別資料');
				$index = 1 ;
				// dd($result);
				while( $index < count($result) ){
					$ifexist = $res->where('currency_code','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr)){
						DB::table("AA101_19")
						->insert([
							"currency_code" => $result[$index][0]==""?null:$result[$index][0],
							"currency_name" => $result[$index][1]==""?null:$result[$index][1],
							"currency_exchrate" => $result[$index][2]==""?null:$result[$index][2],
							"remarks" => isset($result[$index][3])?$result[$index][3]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index ++;
				}
				break;
			case "稅別設定":
				$res = DB::table("AA102_20")->get();
				$result = $excel->getSheetData('稅別資料');
				$index = 1 ;
				// dd($result);
				while( $index < count($result) ){
					$ifexist = $res->where('tax_code','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
						DB::table("AA102_20")
						->insert([
							"tax_code" => $result[$index][0]==""?null:$result[$index][0],
							"tax_name" => $result[$index][1]==""?null:$result[$index][1],
							"tax_taxrate" => $result[$index][2]==""?null:$result[$index][2],
							"remarks" => isset($result[$index][3])?$result[$index][3]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index ++;
				}
				break;
			case "產品單位":
				$res = DB::table("AA103_21")->get();
				$result = $excel->getSheetData('產品單位');
				$index = 1 ;
				// dd($result);
				while( $index < count($result) ){
					$ifexist = $res->where('unit_code','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
						DB::table("AA103_21")
						->insert([
							"unit_code" => $result[$index][0]==""?null:$result[$index][0],
							"unit_name" => $result[$index][1]==""?null:$result[$index][1],
							"remarks" => isset($result[$index][2])?$result[$index][2]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index ++;
				}
				break;
			case "客戶類別":
				$res = DB::table("BA101_23")->get();
				$result = $excel->getSheetData('客戶類別');
				$index = 1 ;
				while( $index < count($result) ){
					$ifexist = $res->where('client_cat','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
						DB::table("BA101_23")
						->insert([
							"client_cat" => $result[$index][0]==""?null:$result[$index][0],
							"client_catname" => $result[$index][1]==""?null:$result[$index][1],
							"remarks" => isset($result[$index][2])?$result[$index][2]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index ++;
				}
				break;
			case "客戶資料":
				$res = DB::table("BA102_37")->get();
				$result = $excel->getSheetData('客戶資料');
				$index = 1;
				while ($index < count($result)) {
					$ifexist = $res->where('client_code', '=', $result[$index][0])->all();
					if (count($ifexist) == 0 && !in_array($result[$index][0], $duplicateArr)) {
						DB::table("BA102_37")->insert([
							"client_code"    => $result[$index][0] === "" ? null : $result[$index][0],
							"client_name"    => $result[$index][1] === "" ? null : $result[$index][1],
							"client_cat"     => $result[$index][2] === "" ? null : $result[$index][2],
							"client_catname" => $result[$index][3] === "" ? null : $result[$index][3],
							"uniform_num"    => isset($result[$index][4]) ? $result[$index][4] : null,
							"invoice_joint"  => isset($result[$index][5]) ? $result[$index][5] : null,
							"currency"       => isset($result[$index][6]) ? $result[$index][6] : null,
							// 對數值欄位加入空字串判斷
							"rate"           => isset($result[$index][7]) ? ($result[$index][7] === "" ? null : $result[$index][7]) : null,
							"tax"            => isset($result[$index][8]) ? ($result[$index][8] === "" ? null : $result[$index][8]) : null,
							"taxrate"        => isset($result[$index][9]) ? ($result[$index][9] === "" ? null : $result[$index][9]) : null,
							"cnt_balance"    => isset($result[$index][10]) ? ($result[$index][10] === "" ? null : $result[$index][10]) : null,
							"phone"          => isset($result[$index][11]) ? $result[$index][11] : null,
							"remarks"        => isset($result[$index][12]) ? $result[$index][12] : null,
							"disable"        => isset($result[$index][13]) ? $result[$index][13] : null,
							"yn_cnt_cust"    => isset($result[$index][14]) ? $result[$index][14] : null,
							"data_options"   => json_encode($dataOptions),
							"created_by"     => 2,
							"updated_by"     => 2,
							"created_at"     => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at"     => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index++;
				}
				
				$res = DB::table("BA102_37")->get();
				$index = 1;
				$result = $excel->getSheetData('客戶資料_表身1');
				while ($index < count($result)) {
					$getId = $res->where('client_code', '=', $result[$index][0])->pluck("id")->first();
					if (!empty($getId)) {
						DB::table("BA102_38")->insert([
							"parent_id"  => $getId,
							"contact"    => isset($result[$index][2]) ? $result[$index][2] : null,
							"phone"      => isset($result[$index][3]) ? $result[$index][3] : null,
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						DB::table("BA102_37")->where('id', $getId)
							->update([
								"phone" => isset($result[$index][3]) ? $result[$index][3] : null,
							]);
					} else {
						DB::rollback();
						return array(
							'status'  => 'miss',
							'message' => "匯入表身時，查無客戶代碼{$result[$index][0]}，請確認",
							'data'    => ''
						);
					}
					$index++;
				}
				
				$index = 1;
				$result = $excel->getSheetData('客戶資料_表身2');
				while ($index < count($result)) {
					$getId = $res->where('client_code', '=', $result[$index][0])->pluck("id")->first();
					if (!empty($getId)) {
						DB::table("BA102_39")->insert([
							"parent_id"  => $getId,
							"addr"       => isset($result[$index][2]) ? $result[$index][2] : null,
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
					} else {
						DB::rollback();
						return array(
							'status'  => 'miss',
							'message' => "匯入表身時，查無客戶代碼{$result[$index][0]}，請確認",
							'data'    => ''
						);
					}
					$index++;
				}
				
				break;
			case "廠商類別":
				$res = DB::table("CA101_24")->get();
				$result = $excel->getSheetData('廠商類別');
				$index = 1 ;
				// dd($result);
				while( $index < count($result) ){
					$ifexist = $res->where('vendor_cat_code','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
						DB::table("CA101_24")
						->insert([
							"vendor_cat_code" => $result[$index][0]==""?null:$result[$index][0],
							"vendor_cat_name" => $result[$index][1]==""?null:$result[$index][1],
							"remarks" => isset($result[$index][2])?$result[$index][2]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index ++;
				}
				break;
			case "廠商資料":
				$res = DB::table("CA102_27")->get();
				$result = $excel->getSheetData('廠商資料');
				$index = 1 ;
				// dd($result);
				while( $index < count($result) ){
					$ifexist = $res->where('vendor_code','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
						DB::table("CA102_27")
						->insert([
							"vendor_code" => $result[$index][0]==""?null:$result[$index][0],
							"vendor_name" => $result[$index][1]==""?null:$result[$index][1],
							"vendor_cat_code" => $result[$index][2]==""?null:$result[$index][2],
							"vendor_cat_name" => $result[$index][3]==""?null:$result[$index][3],
							"currency" => isset($result[$index][4])?$result[$index][4]:null,
							"rate" => isset($result[$index][5])?$result[$index][5]:null,
							"tax" => isset($result[$index][6])?$result[$index][6]:null,
							"taxrate" => isset($result[$index][7])?$result[$index][7]:null,
							"remarks" => isset($result[$index][8])?$result[$index][8]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index ++;
				}
				$res = DB::table("CA102_27")->get();
				$index = 1 ;
				$result = $excel->getSheetData('廠商資料_表身1');
				while( $index < count($result) ){
					$getId = $res->where('vendor_code','=',$result[$index][0])->pluck("id")->first();
					if( !empty($getId) ){
							DB::table("CA102_28")
							->insert([
								"parent_id" => $getId,
								"contact" => isset($result[$index][2])?$result[$index][2]:null,
								"phone" => isset($result[$index][3])?$result[$index][3]:null,

								"created_by" => 2,
								"updated_by" => 2,
								"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
								"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
							]);
					}else{
						DB::rollback();
						return array(
							'status'=> 'miss',
							'message' => "匯入表身時，查無廠商代碼{$result[$index][0]}，請確認",
							'data'=> ''
						);
					}
					$index ++;
				}
				$index = 1 ;
				$result = $excel->getSheetData('廠商資料_表身2');
				while( $index < count($result) ){
					$getId = $res->where('vendor_code','=',$result[$index][0])->pluck("id")->first();
					if( !empty($getId) ){
						DB::table("CA102_29")
						->insert([
							"parent_id" => $getId,
							"addr" => isset($result[$index][2])?$result[$index][2]:null,

							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
					}else{
						DB::rollback();
						return array(
							'status'=> 'miss',
							'message' => "匯入表身時，查無廠商代碼{$result[$index][0]}，請確認",
							'data'=> ''
						);
					}
					$index ++;
				}
				break;
			case "產品類別":
				$res = DB::table("AA201_22")->get();
				$result = $excel->getSheetData('產品類別');
				$index = 1 ;
				while( $index < count($result) ){
					$ifexist = $res->where('pro_cat_code','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
						DB::table("AA201_22")
						->insert([
							"pro_cat_code" => $result[$index][0]==""?null:$result[$index][0],
							"pro_cat_name" => $result[$index][1]==""?null:$result[$index][1],
							"remarks" => isset($result[$index][2])?$result[$index][2]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index ++;
				}
				break;
			case "產品資料":
				$res = DB::table("AA202_30")->get();
				$result = $excel->getSheetData('產品資料');
				$index = 1;
				$depotdata = DB::table('EA101_26')
					->select('depot_code','depot_name')
					->get();

				while ($index < count($result)) {
					$ifexist = $res->where('product_code', '=', $result[$index][0])->all();
					if (count($ifexist) == 0 && !in_array($result[$index][0], $duplicateArr)) {
						DB::table("AA202_30")
							->insert([
								"product_code"     => $result[$index][0] === "" ? null : $result[$index][0],
								"product_name"     => $result[$index][1] === "" ? null : $result[$index][1],
								"specification"    => isset($result[$index][2]) ? $result[$index][2] : null,
								"product_kind"     => $result[$index][3] === "" ? null : $result[$index][3],
								"unit_code"        => $result[$index][4] === "" ? null : $result[$index][4],
								"unit_name"        => $result[$index][5] === "" ? null : $result[$index][5],
								"pro_cat_code"     => $result[$index][6] === "" ? null : $result[$index][6],
								"pro_cat_name"     => $result[$index][7] === "" ? null : $result[$index][7],
								"vendor_code"      => isset($result[$index][8]) ? $result[$index][8] : null,
								"vendor_name"      => isset($result[$index][9]) ? $result[$index][9] : null,
								"depot_code"       => isset($result[$index][10]) ? $result[$index][10] : null,
								"depot_name"       => isset($result[$index][11]) ? $result[$index][11] : null,
								"sell_price"       => isset($result[$index][12]) ? ($result[$index][12] === "" ? null : $result[$index][12]) : null,
								"purchase_price"   => isset($result[$index][13]) ? ($result[$index][13] === "" ? null : $result[$index][13]) : null,
								"disable"          => isset($result[$index][14]) ? $result[$index][14] : null,
								"data_options"     => json_encode($dataOptions),
								"created_by"       => 2,
								"updated_by"       => 2,
								"created_at"       => Carbon::now()->format('Y-m-d H:i:s'),
								"updated_at"       => Carbon::now()->format('Y-m-d H:i:s')
							]);

						$duplicateArr[] = $result[$index][0];

						foreach ($depotdata as $key => $val) {
							if ($result[$index][3] == "產品") {
								DB::table('EA204_79')
									->insert([
										'product_code' => $result[$index][0] === "" ? null : $result[$index][0],
										'product_name' => $result[$index][1] === "" ? null : $result[$index][1],
										'num'          => '0',
										'unit_code'    => $result[$index][4] === "" ? null : $result[$index][4],
										'unit_name'    => $result[$index][5] === "" ? null : $result[$index][5],
										'depot_code'   => $val->depot_code,
										'depot_name'   => $val->depot_name,
									]);
							}
						}
					}
					$index++;
				}

				$res = DB::table("AA202_30")->get();
				$index = 1;
				$result2 = $excel->getSheetData('產品資料_表身1');

				while ($index < count($result2)) {
					$getId = $res->where('product_code', '=', $result2[$index][0])->pluck("id")->first();
					if (!empty($getId)) {
						DB::table("AA202_31")
							->insert([
								"parent_id"           => $getId,
								"body_unit_code"      => isset($result2[$index][2]) ? $result2[$index][2] : null,
								"body_unit_name"      => isset($result2[$index][3]) ? $result2[$index][3] : null,
								"body_rate"           => isset($result2[$index][4]) ? ($result2[$index][4] === "" ? null : $result2[$index][4]) : null,
								"body_sell_price"     => isset($result2[$index][5]) ? ($result2[$index][5] === "" ? null : $result2[$index][5]) : null,
								"body_purchase_price" => isset($result2[$index][6]) ? ($result2[$index][6] === "" ? null : $result2[$index][6]) : null,
								"body_remarks"        => isset($result2[$index][7]) ? $result2[$index][7] : null,
								"created_by"          => 2,
								"updated_by"          => 2,
								"created_at"          => Carbon::now()->format('Y-m-d H:i:s'),
								"updated_at"          => Carbon::now()->format('Y-m-d H:i:s')
							]);
					} else {
						DB::rollback();
						return array(
							'status'  => 'miss',
							'message' => "匯入表身時，查無產品代碼{$result2[$index][0]}，請確認",
							'data'    => ''
						);
					}
					$index++;
				}

				/* if(empty($tmpArr)){
					DB::table($littleTable)
					->insert([
						'batch_code' => $datas['batch_code'], //出貨單號
						'batch_no' => $datas['batch_no'], //表身NO
						'undertakerday' => $datas['undertakerday'], //承辦時間
						'num' => $puVal->diifnum, //數量 * 換算率
						'parent_id' => $source_batch_code //小視窗表頭ID
					]);
					// $result2 = $excel->getSheetData('產品資料_表身1');
					dd($result2);

				}else{
					DB::rollback();
					return array(
						'status'=> 'duplicate',
						'message' => '',
						'data'=> $tmpArr
					);
				} */
				// dd($result);

				break;
			case "倉庫資料":
				$res = DB::table("EA101_26")->get();
				$result = $excel->getSheetData('倉庫資料');
				$index = 1 ;
				//抓出現有產品
				$productdata = DB::table('AA202_30')
				->select('product_code','product_name','unit_code','unit_name')
				->where('product_kind', '產品')
				->get();
				while( $index < count($result) ){
					$ifexist = $res->where('depot_code','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
						DB::table("EA101_26")
						->insert([
							"depot_code" => $result[$index][0]==""?null:$result[$index][0],
							"depot_name" => $result[$index][1]==""?null:$result[$index][1],
							"depot_attribute" => $result[$index][2]==""?null:$result[$index][2],
							"remarks" => isset($result[$index][3])?$result[$index][3]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
						//寫入分倉
						foreach($productdata as $key => $val){
							DB::table('EA204_79')
								->insert([
									'product_code' => $val->product_code,
									'product_name' => $val->product_name,
									'num' => '0',
									'unit_code' => $val->unit_code,
									'unit_name' => $val->unit_name,
									'depot_code' => $result[$index][0]==""?null:$result[$index][0],
									'depot_name' => $result[$index][1]==""?null:$result[$index][1],

									"data_options" => json_encode($dataOptions),
									"created_by" => 2,
									"updated_by" => 2,
									"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
									"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
								]);
						}
					}
					$index ++;
				}
				break;
				case "合約儲值管理":
					$res = DB::table("BA104_6241")->get();
					$result = $excel->getSheetData('合約儲值管理');
					$index = 1 ;
	
					while( $index < count($result) ){
						$ifexist = $res->where('cnt_num','=',$result[$index][0])->all();
						if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
							DB::table("BA104_6241")
							->insert([
								"client_code" => $result[$index][0]==""?null:$result[$index][0],
								"client_name" => $result[$index][1]==""?null:$result[$index][1],
								"cnt_date" => isset($result[$index][2])?$result[$index][2]:null,
								"cnt_num" => isset($result[$index][3])?$result[$index][3]:null,
								"cnt_amt" => isset($result[$index][4])?$result[$index][4]:null,
								"amt_recd" => isset($result[$index][5])?$result[$index][5]:null,
								"payment_status" => isset($result[$index][6])?$result[$index][6]:null,
								"remarks" => isset($result[$index][7])?$result[$index][7]:null,
								"data_options" => json_encode($dataOptions),
								"created_by" => 2,
								"updated_by" => 2,
								"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
								"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
							]);
							$duplicateArr[] = $result[$index][0];
						}
						$index ++;
					}
					break;
			case "包裝方式":
				$res = DB::table("AA104_2121")->get();
				$result = $excel->getSheetData('包裝方式');
				$index = 1 ;
				while( $index < count($result) ){
					$ifexist = $res->where('packing_code','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
						DB::table("AA104_2121")
						->insert([
							"packing_code" => $result[$index][0]==""?null:$result[$index][0],
							"packing_name" => $result[$index][1]==""?null:$result[$index][1],
							"remarks" => isset($result[$index][2])?$result[$index][2]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index ++;
				}
				break;
			case "運輸方式":
				$res = DB::table("AA105_2122")->get();
				$result = $excel->getSheetData('運輸方式');
				$index = 1 ;
				while( $index < count($result) ){
					$ifexist = $res->where('transport_code','=',$result[$index][0])->all();
					if(count($ifexist) == 0 && !in_array($result[$index][0],$duplicateArr) ){
						DB::table("AA105_2122")
						->insert([
							"transport_code" => $result[$index][0]==""?null:$result[$index][0],
							"transport_name" => $result[$index][1]==""?null:$result[$index][1],
							"remarks" =>isset($result[$index][2])?$result[$index][2]:null,

							"data_options" => json_encode($dataOptions),
							"created_by" => 2,
							"updated_by" => 2,
							"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
							"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
						]);
						$duplicateArr[] = $result[$index][0];
					}
					$index ++;
				}
				break;
			case "庫存調整單":
				$res = DB::table("EA204_79")->get();
				$result = $excel->getSheetData('分倉庫存數量');
				$index = 1 ;
				$deopData = [];

				while( $index < count($result) ){
					// dump($index);
					$depot_code = $result[$index][0]==""?null:$result[$index][0];
					$depot_name = $result[$index][1]==""?null:$result[$index][1];
					$product_code = $result[$index][2]==""?null:$result[$index][2];
					$product_name = $result[$index][3]==""?null:$result[$index][3];
					$num = $result[$index][4]==""?null:$result[$index][4];

					$resData = $res->where('depot_code','=',$depot_code)->where('product_code','=',$product_code)->first();

					if( !empty($resData) &&  $resData->num != $num ){
						if( empty($num) ){
							$num = 0;
						}
						if( empty($resData->num) ){
							$resData->num = 0;
						}
						// dump((float)$resData->num,(float)$num);
						if((float)$num != (float)$resData->num){
							$adjust_num = $num - $resData->num ;
							$deopData[$depot_code]['depot_name'] = $depot_name;
							$deopData[$depot_code]['data'][] = [
								"depot_code" => $depot_code,
								"depot_name" => $depot_name,
								"product_code" => $product_code,
								"product_name" => $product_name,
								"num" => $num,
								"adjust_num" => $adjust_num,
								"unit_code" => $resData->unit_code,
								"unit_name" => $resData->unit_name,
							];
						}
					}
					$index ++;
				}
				//新增庫存調整單
				foreach( $deopData as $key => $val ){
					$number = CommonController::generateDocumentNumber('58','adjust_code');
					// dd($val);。
					$id = DB::table("EA201_50")
					->insertGetId([
						'adjust_code' => $number,

						"depot_code" => $key,
						"depot_name" => $val['depot_name'],
						"remarks" => Carbon::now()->format('Ymd') . "庫存開帳",
						'undertaker' => 'SYSTEM',
						'undertakerday' => Carbon::now()->format('Y-m-d'),
						'undertakername' => 'SYSTEM',

						"data_options" => json_encode($dataOptions),
						"created_by" => 2,
						"updated_by" => 2,
						"created_at" => Carbon::now()->format('Y-m-d H:i:s'),
						"updated_at" => Carbon::now()->format('Y-m-d H:i:s')
					]);
					foreach( $val['data'] as $subKey => $subVal ){
						//庫存調整單表身
						DB::table('EA201_51')
						->insert([
							'parent_id' => $id,
							'body_num' => $subVal['adjust_num'],
							'body_rate' => '1',
							'product_code' => $subVal['product_code'],
							'product_name' => $subVal['product_name'],
							'unit_code' => $subVal['unit_code'],
							'unit_name' => $subVal['unit_name'],
						]);
						//分倉數量
						DB::table('EA204_79')
                        ->where('product_code',$subVal['product_code'])
                        ->where('depot_code',$subVal['depot_code'])
						->update([
							'num' => $subVal['num'],
							"created_by" => 2,
							"updated_by" => 2,

						]);
					}
				}
				break;
                case "出貨單":
                    $res = DB::table("BA202_52")->get();
                    $result = $excel->getSheetData('出貨單');
                    $index = 1 ;
                    // dd($result);
                    while( $index < count($result) ){
                        $ifexist = $res->where('ship_code','=',$result[$index][1])->all();
                        if(count($ifexist) == 0  && !in_array($result[$index][1],$duplicateArr) ){
                            DB::table("BA202_52")
                            ->insert([
                                "client_code" => $result[$index][0]==""?null:$result[$index][0],
                                "client_name" => $result[$index][1]==""?null:$result[$index][1],
                                "client_cat" => $result[$index][2]==""?null:$result[$index][2],
                                "client_catname" => $result[$index][3]==""?null:$result[$index][3],
                                "uniform_num" => isset($result[$index][4])?$result[$index][4]:null,
                                "invoice_joint" => isset($result[$index][5])?$result[$index][5]:null,
                                "currency" => isset($result[$index][6])?$result[$index][6]:null,
                                "rate" => isset($result[$index][7])?$result[$index][7]:null,
                                "tax" => isset($result[$index][8])?$result[$index][8]:null,
                                "taxrate" => isset($result[$index][9])?$result[$index][9]:null,
                                "remarks" => isset($result[$index][10])?$result[$index][10]:null,

                                "data_options" => json_encode($dataOptions),
                                "created_by" => 2,
                                "updated_by" => 2,
                                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                            ]);
                            $duplicateArr[] = $result[$index][0];
                        }
                        $index ++;
                    }
                    $res = DB::table("BA102_37")->get();
                    $index = 1 ;
                    $result = $excel->getSheetData('客戶資料_表身1');
                    while( $index < count($result) ){
                        $getId = $res->where('client_code','=',$result[$index][0])->pluck("id")->first();
                        if( !empty($getId) ){
                                DB::table("BA102_38")
                                ->insert([
                                    "parent_id" => $getId,
                                    "contact" => $result[$index][2]==""?null:$result[$index][2],
                                    "phone" => isset($result[$index][3])?$result[$index][3]:null,

                                    "created_by" => 2,
                                    "updated_by" => 2,
                                    "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                                    "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                                ]);
                        }else{
                            DB::rollback();
                            return array(
                                'status'=> 'miss',
                                'message' => "匯入表身時，查無客戶代碼{$result[$index][0]}，請確認",
                                'data'=> ''
                            );
                        }
                        $index ++;
                    }
                    $index = 1 ;
                    $result = $excel->getSheetData('客戶資料_表身2');
                    while( $index < count($result) ){
                        $getId = $res->where('client_code','=',$result[$index][0])->pluck("id")->first();
                        if( !empty($getId) ){
                            DB::table("BA102_39")
                            ->insert([
                                "parent_id" => $getId,
                                "addr" => $result[$index][2]==""?null:$result[$index][2],

                                "created_by" => 2,
                                "updated_by" => 2,
                                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
                            ]);
                        }else{
                            DB::rollback();
                            return array(
                                'status'=> 'miss',
                                'message' => "匯入表身時，查無客戶代碼{$result[$index][0]}，請確認",
                                'data'=> ''
                            );
                        }
                        $index ++;
                    }
                    break;
			}
			DB::commit();
			return array(
				'status'=> 'success',
				'message' => '',
				'data'=> null
			);

		/* $errorMsg["errors"] = $tmpArr;
        if( !empty($errorMsg["errors"]) ){
            DB::rollback();
        }else{

			DB::commit();
			return array(
				'status'=> 'success',
				'message' => '',
				'data'=> null
			);
        } */

		// dd(array_count_values ($result[9]),$vactionData);

	}


}
?>
