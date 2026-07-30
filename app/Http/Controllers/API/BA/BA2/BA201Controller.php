<?php
namespace App\Http\Controllers\API\BA\BA2;

use App\Http\Controllers\Base\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;
use App\Utils\VerifyUtil;

use Carbon\Carbon;
use App\Http\Controllers\CommonController;

/*use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;*/

class BA201Controller extends Controller{
	static public function transToWork(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		DB::beginTransaction();
		$pageId = '53';
		$client_order_code = $data['client_order_code'];
		$worktData = DB::table('BA201_40 as a')->select(DB::raw('a.*,a.data_options as dataoptions,b.*'))->leftJoin('BA201_41 as b', 'b.parent_id','=','a.id')->where('client_order_code','=',$client_order_code)->whereNull('machining_code')->lockForUpdate()->get();
		if( $worktData->count() ==  0 ){
			DB::rollback();
			$msgArr = ["text" => "警告:此客戶訂單表身皆已轉加工單，請確認","status" => true,"number"=>""];
			return $msgArr;
		}else{
			if (VerifyUtil::pageVerifyConfirmation(53)) {
				$data_options = DataUtil::convertToArray(json_decode($worktData->pluck('dataoptions')->first()));
				if( $data_options['verify']['level'] != 255 ){
					DB::rollback();
					$msgArr = ["text" => "警告:此客戶訂單尚未通過審核無法轉出加工單，請確認","status" => true,"number"=>""];
					return $msgArr;
				}
			}
		}
		$num = count($worktData);
		$numberArr = array();
		$editable = '0';
		$productData = DB::table('AA203_32 as a')->leftJoin('AA203_33 as c', 'a.id', '=', 'c.parent_id')->orderBy('a.product_code', 'ASC')->get();

		$orderData = DB::table('BA201_40 as a')->select(DB::raw('b.id as order_no,a.*,b.*,AA203_32.station_day,AA203_32.station_code,AA203_32.station_name,AA203_32.depot_code,AA203_32.depot_name'))
			->leftJoin('BA201_41 as b', 'a.id', '=', 'b.parent_id')
			->leftJoin('AA202_30 as c', 'b.product_code', '=', 'c.product_code')
			->leftJoin('AA203_32 ', function ($join) {
				$join->on('AA203_32.product_code', '=', 'b.product_code')
					->on('AA203_32.formula_name', '=', 'b.body_formula');
			})->whereNotNull('b.body_formula')->where('a.client_order_code','=',$client_order_code)
			->where('c.product_kind','=','產品')
			->orderBy('a.id', 'ASC')->lockForUpdate()->get();
		$order = $orderData->where('machining_code','=','')->groupBy('station_code');
		if( $orderData->count() > 0 ){
			$numberArr = $order->map(function ($row,$key) use($pageId,$productData,$data) {
				$getHead = $row[0]; //only for get the same value of table header
				$number = CommonController::generateDocumentNumber($pageId,'machining_code'); //加工單號
				$station_day = $row->sum('station_day');
				if( $station_day >= 44000 ){
					return ;
				}
				$machining_finished = Carbon::parse($getHead->advanceday)->subDay($station_day)->format('Y-m-d');
				//head
				if (VerifyUtil::pageVerifyConfirmation(56)) {
					$dataOptions = [
						'verify'=>[
							'level'=>0
						]
					];
					$dataOptions = json_encode($dataOptions);
				}else{
					$dataOptions = null;
				}
				$id = DB::table('DA201_44')->insertGetId(
					[
						'machining_code' => $number,
						'machining_finished' => $machining_finished,
						'undertaker' => $getHead->undertaker,
						'undertakername' =>  $getHead->undertakername,
						'undertakerday' => Carbon::now()->format('Y-m-d'),
						'station_code' => $getHead->station_code,
						'station_name' => $getHead->station_name,
						'data_options' => $dataOptions,
						"created_at" =>  Carbon::now(), # new \Datetime()
						"updated_at" => Carbon::now(),  # new \Datetime()
						"created_by"=> session("user_id"),
						"updated_by"=> session("user_id")
					]
				);
				return [
					'number' =>$number,
					'id' =>$id
				];
			});

			$numberRes = [];
			if( !empty($numberArr) ){
//				dd($orderData);
				foreach( $orderData as $key=>$val ){
					if( !$val->machining_code ){
						//body
						$body_num = $val->body_num;
						$body_rate = $val->body_rate;
						$total_num = $body_num * $body_rate;
						$bodyId = DB::table('DA201_45')->insertGetId([
							'parent_id'=>$numberArr[$val->station_code]['id'],
							'product_code' => $val->product_code,
							'product_name' => $val->product_name,
							'body_num' => $body_num,
							'unit_code' =>  $val->unit_code,
							'unit_name' => $val->unit_name,
							'body_rate' => $body_rate,
							'body_quantity' => '0',
							'depot_code' => $val->depot_code,
							'depot_name' =>  $val->depot_name,
							'client_order_code' => $val->client_order_code,
							'order_no' => $val->order_no,
							'body_cancel' => 'N',
							"created_at" =>  Carbon::now(), # new \Datetime()
							"updated_at" => Carbon::now(),  # new \Datetime()
							"created_by"=> session("user_id"),
							"updated_by"=> session("user_id")
						]);

						//body of body
						$product = $productData->where('product_code', '=', $val->product_code)
							->where('formula_name', '=', $val->body_formula)->all();
						foreach($product as $productKey=>$productValue){
							DB::table('DA201_46')->insert([
								'parent_id'=>$bodyId,
								'component_code' => $productValue->component_code,
								'component_name' => $productValue->component_name,
								'component_num' => $total_num*$productValue->body_num,
								'component_unit' =>  $productValue->unit_code,
								'component_unitname' => $productValue->unit_name,
								'component_rate' => $productValue->body_rate,
								'component_depot' => $productValue->body_depot_code,
								'component_depotname' => $productValue->body_depot_name,
								"created_at" =>  Carbon::now(), # new \Datetime()
								"updated_at" => Carbon::now(),  # new \Datetime()
								"created_by"=> session("user_id"),
								"updated_by"=> session("user_id")
							]);
						}//end of foreach
						DB::table('BA201_41')
							->where('id', $val->order_no)
							->update([
								'machining_code' => $numberArr[$val->station_code]['number'],
							]);
						$numberRes[] = array(
							'order_no'=>$val->order_no,
							'number'=>$numberArr[$val->station_code]['number']
						);
					}
				}
			}//end of if
			DB::commit();
			$msgArr = ["text" => "轉出成功","status" => false,"number"=>$numberRes];
		}else{
			DB::rollback();
			$msgArr = ["text" => "警告:此訂單無生產配方，請確認","status" => true,"number"=>""];
		}

		return $msgArr;
	}

	static public function checkExisInWork(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		$client_order_code = $data['client_order_code'];
		$worktData = DB::table('DA201_45')->get();
		$num = $worktData->where('client_order_code','=',$client_order_code)->count();
		$editable = '0';
		if( $num != 0 )
			$editable = '1';
		return $editable;
	}

    static public function cited(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		$status = 0;
        foreach($data['tables'] as $key => $val){
			if( isset($data['no']) ){
				$indata = DB::table($val)
                    ->select('*')
                    ->where($data['temp'], $data['no'])
					->get();
				if(count($indata)>0){
					$status = 1;
				}
			}
        }
		return $status;

	}


    static public function getclientcurrencytax(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		$getclient = 0;

        $indata = DB::table('BA102_37')
                ->select('currency','rate','tax','taxrate')
                ->where('client_code', $data['client_code'])
                ->get();
        if(!empty($indata)){
            $getclient = $indata;
        }
		return $getclient;

	}
    static public function printOrder1(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
			abort(400);

		$format = "pdf";

		$order_code = $data['order_code'];
		$type = $data['type'];

		//dd($data);
		$reportData = DB::table('BA201_40 as a')
		->select(DB::raw("
			a.client_order_code,
			a.advanceday,
			a.client_code,
			a.client_name,
			a.otax            as otax,
			a.osubtotal       as osubtotal,
			ROUND(a.ototal,0) as ototal,
			a.remarks         as head_remarks,
			b.product_code,
			b.product_name,
			b.body_num,
			b.unit_name,
			b.body_price,
			b.body_subtotal,
			(b.body_num - b.body_quantity) as undelvd_num,
			b.body_remarks    as remarks,
			'1'                as sn,
			b.id              as idid,
			'1'                as con,
			a.undertakerday
		"))
		->leftJoin('BA201_41 as b', 'b.parent_id', '=', 'a.id')
		->where('a.client_order_code', '=', $order_code)
		->where('b.body_cancel', '=', 'N');

	//	dd($reportData->get());
	// 組合商品查詢
	$reportData1 = DB::table('BA201_40 as a')
		->select(DB::raw("
			a.client_order_code,
			a.advanceday,
			a.client_code,
			a.client_name,
			a.otax            as otax,
			a.osubtotal       as osubtotal,
			ROUND(a.ototal,0) as ototal,
			a.remarks         as head_remarks,
			d.cont_code       as product_code,
			d.cont_name       as product_name,
			CAST(b.body_num * d.body_num * d.body_rate AS decimal(10,2)) as body_num,
			d.unit_name,
			null              as body_price,
			null              as body_subtotal,
			null              as undelvd_num,
			null              as remarks,
			'2'                as sn,
			b.id              as idid,
			null              as con,
			a.undertakerday
		"))
		->leftJoin('BA201_41 as b', 'b.parent_id', '=', 'a.id')
		->leftJoin('AA204_2224 as c', function($q) {
			$q->on('b.combi_code', '=', 'c.combi_code')
			  ->on('b.product_code', '=', 'c.product_code');
		})
		->leftJoin('AA204_2225 as d', 'd.parent_id', '=', 'c.id')
		->where('a.client_order_code', '=', $order_code)
		->whereNotNull('b.combi_code');
		//dd($reportData->get(),$reportData1->get());
		$reportData = $reportData->unionAll($reportData1)->orderBy('client_code')->orderBy('idid')->orderBy('sn')->get();
		$client_code = $reportData->pluck('client_code')->first();
		$customData = DB::table('BA102_37 as a')
		->select(DB::raw("a.client_code,a.client_name,a.cnt_balance,a.yn_cnt_cust,a.uniform_num,b.phone,a.fax,c.addr") )
		->leftJoin('BA102_38 as b', 'b.parent_id','=','a.id')
		->leftJoin('BA102_39 as c', 'c.parent_id','=','a.id')
		->where('client_code','=',$client_code)
		->orderBy('b.id')->first();
       //  dd($reportData,$client_code,$customData);
        $contractData = DB::table('BA104_6241 as a')
        ->where('client_code','=',$client_code)
        ->orderBy('a.id','DESC')
        ->first();
		
     
        if($customData->yn_cnt_cust){
            $jasperName = "BA201_contract";
        }else{
            $jasperName = "BA201_price";
        }
        $company_name = "孔媽媽";
			$company_tel = "07-6285979";
			$company_fax = "";
			$company_addr = "高雄市岡山區大莊里大莊路350號";
			$company_mail = "";
		if( $type == "type1" ){

            foreach($reportData as $key=>$row ){
                $row->phone = $customData->phone;
                $row->addr = $customData->addr;
                $row->uniform_num = $customData->uniform_num;
				$row->fax = $customData->fax;
                $row->company_name = $company_name;
                $row->company_tel = $company_tel;
                $row->company_fax = $company_fax;
                $row->company_addr = $company_addr;
                $row->company_mail = $company_mail;
                $row->cnt_balance = $customData->cnt_balance;
                if($contractData){
                    $row->cnt_num ="此訂單扣合約編號".$contractData->cnt_num."，尚餘";
                }
            }
		}else{
            foreach($reportData as $key=>$row ){
                $row->phone = $customData->phone;
                $row->addr = $customData->addr;
                $row->uniform_num = $customData->uniform_num;
				$row->fax = $customData->fax;
                $row->company_name = $company_name;
                $row->company_tel = $company_tel;
                $row->company_fax = $company_fax;
                $row->company_addr = $company_addr;
                $row->company_mail = $company_mail;
                $row->cnt_balance = null;
                $row->ototal=null;
                $row->otax=null;
                $row->osubtotal=null;
                $row->body_price=null;
                $row->body_subtotal=null;
                if($contractData){
                    $row->cnt_num =null;
                }
            }
		}


		$reportClass = new ReportController;
		//dd($reportData);
		$res = $reportClass->export($format,$jasperName,$reportData);

		return $res;
	}
    static public function printOrder2(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
			abort(400);

		$format = "pdf";

		$order_code = $data['order_code'];
		$type = $data['type'];

		$reportData = DB::table('BA201_40 as a')
                ->select(DB::raw("a.client_order_code,a.advanceday,a.client_code,a.client_name ,     
				a.otax as otax,
      			a.osubtotal as osubtotal,
       			ROUND(a.ototal, 0) as ototal,a.remarks as head_remarks,b.product_code, b.product_name ,
                b.body_num,b.unit_name, b.body_price ,b.body_subtotal ,(b.body_num-b.body_quantity) as undelvd_num,b.body_remarks as remarks,1 as sn,b.id as idid,1 as con,a.undertakerday") )
				->leftJoin('BA201_41 as b', 'b.parent_id','=','a.id')
				->where('client_order_code','=',$order_code)
                ->where('body_cancel','=',"N")
                ->whereRaw('body_num > body_quantity');
    //     $reportData1 = DB::table('BA202_52 as a')
    //     ->select(DB::raw("a.ship_code,a.ship_date,a.client_code,a.client_name ,a.otax,a.osubtotal,a.ototal,a.remarks as head_remarks,
    //     d.cont_code as product_code,d.cont_name as product_name ,
    //     CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as body_num,d.unit_name,
    //     null as body_price ,null as body_subtotal ,null as remarks,'2' as sn,b.id as idid,null as con") )
    // ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
    // ->leftJoin('AA204_2224 as c', 'b.combi_name','=','c.combi_name')
    // ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
    // ->where('ship_code','=',$ship_code)
    // ->whereNotNull('b.combi_name');
		$client_code = $reportData->pluck('client_code')->first();
		$customData = DB::table('BA102_37 as a')
		->select(DB::raw("a.client_code,a.client_name,a.cnt_balance,a.yn_cnt_cust,a.uniform_num,b.phone,a.fax,c.addr") )
		->leftJoin('BA102_38 as b', 'b.parent_id','=','a.id')
		->leftJoin('BA102_39 as c', 'c.parent_id','=','a.id')
		->where('client_code','=',$client_code)
		->orderBy('b.id')->first();
        // dd($customData);
        $contractData = DB::table('BA104_6241 as a')
        ->where('client_code','=',$client_code)
        ->orderBy('a.id','DESC')
        ->first();
        $reportData = $reportData->get();
        // $reportData = $reportData->union($reportData1)->orderBy('client_code')->orderBy('idid')->orderBy('sn')->get();
        // if($customData->yn_cnt_cust){
        //     $jasperName = "BA201_contract1";
        // }else{
            $jasperName = "BA201_price1";
        // }
        $company_name = "孔媽媽";
			$company_tel = "07-6285979";
			$company_fax = "";
			$company_addr = "高雄市岡山區大莊里大莊路350號";
			$company_mail = "";
		if( $type == "type1" ){

            foreach($reportData as $key=>$row ){
                $row->phone = $customData->phone;
                $row->addr = $customData->addr;
                $row->uniform_num = $customData->uniform_num;
				$row->fax = $customData->fax;
                $row->company_name = $company_name;
                $row->company_tel = $company_tel;
                $row->company_fax = $company_fax;
                $row->company_addr = $company_addr;
                $row->company_mail = $company_mail;
                $row->cnt_balance = $customData->cnt_balance;
                if($contractData){
                    $row->cnt_num ="此訂單扣合約編號".$contractData->cnt_num."，尚餘";
                }
            }
		}else{
            foreach($reportData as $key=>$row ){
                $row->phone = $customData->phone;
                $row->addr = $customData->addr;
                $row->uniform_num = $customData->uniform_num;
				$row->fax = $customData->fax;
                $row->company_name = $company_name;
                $row->company_tel = $company_tel;
                $row->company_fax = $company_fax;
                $row->company_addr = $company_addr;
                $row->company_mail = $company_mail;
                $row->cnt_balance = null;
                $row->ototal=null;
                $row->otax=null;
                $row->osubtotal=null;
                $row->body_price=null;
                $row->body_subtotal=null;
                if($contractData){
                    $row->cnt_num =null;
                }
            }
		}


		$reportClass = new ReportController;
		//dd($reportData);
		$res = $reportClass->export($format,$jasperName,$reportData);

		return $res;
	}
    static public function printOrder3(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
			abort(400);

		$format = "pdf";

		$order_code = $data['order_code'];
		$type = $data['type'];

		$reportData = DB::table('BA201_40 as a')
                ->select(DB::raw("a.client_order_code,a.advanceday,a.undertakerday,a.client_code,a.client_name ,      
				a.otax as otax,
				a.osubtotal as osubtotal,
				ROUND(a.ototal, 0) as ototal,a.remarks as head_remarks,b.product_code, b.product_name ,
                b.body_num,b.unit_name, b.body_price ,b.body_subtotal ,b.body_remarks as remarks,'1' as sn,b.id as idid,'1' as con") )
				->leftJoin('BA201_41 as b', 'b.parent_id','=','a.id')
				->where('client_order_code','=',$order_code);
    //     $reportData1 = DB::table('BA202_52 as a')
    //     ->select(DB::raw("a.ship_code,a.ship_date,a.client_code,a.client_name ,a.otax,a.osubtotal,a.ototal,a.remarks as head_remarks,
    //     d.cont_code as product_code,d.cont_name as product_name ,
    //     CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as body_num,d.unit_name,
    //     null as body_price ,null as body_subtotal ,null as remarks,'2' as sn,b.id as idid,null as con") )
    // ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
    // ->leftJoin('AA204_2224 as c', 'b.combi_name','=','c.combi_name')
    // ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
    // ->where('ship_code','=',$ship_code)
    // ->whereNotNull('b.combi_name');
		$client_code = $reportData->pluck('client_code')->first();
		$customData = DB::table('BA102_37 as a')
		->select(DB::raw("a.client_code,a.client_name,a.cnt_balance,a.yn_cnt_cust,a.uniform_num,b.phone,a.fax,c.addr") )
		->leftJoin('BA102_38 as b', 'b.parent_id','=','a.id')
		->leftJoin('BA102_39 as c', 'c.parent_id','=','a.id')
		->where('client_code','=',$client_code)
		->orderBy('b.id')->first();
        // dd($customData);
        $contractData = DB::table('BA104_6241 as a')
        ->where('client_code','=',$client_code)
        ->orderBy('a.id','DESC')
        ->first();
        $reportData = $reportData->get();
        // $reportData = $reportData->union($reportData1)->orderBy('client_code')->orderBy('idid')->orderBy('sn')->get();
        // if($customData->yn_cnt_cust){
        //     $jasperName = "BA201_contract";
        // }else{
        //     $jasperName = "BA201_price";
        // }
        $jasperName = "BA201_price2";
        $company_name = "孔媽媽";
			$company_tel = "07-6285979";
			$company_fax = "";
			$company_addr = "高雄市岡山區大莊里大莊路350號";
			$company_mail = "";
		if( $type == "type1" ){

            foreach($reportData as $key=>$row ){
                $row->phone = $customData->phone;
                $row->addr = $customData->addr;
                $row->uniform_num = $customData->uniform_num;
				$row->fax = $customData->fax;
                $row->company_name = $company_name;
                $row->company_tel = $company_tel;
                $row->company_fax = $company_fax;
                $row->company_addr = $company_addr;
                $row->company_mail = $company_mail;
                $row->cnt_balance = $customData->cnt_balance;
                // if($contractData){
                //     $row->cnt_num ="此訂單扣合約編號".$contractData->cnt_num."，尚餘";
                // }
            }
		}else{
            foreach($reportData as $key=>$row ){
                $row->phone = $customData->phone;
                $row->addr = $customData->addr;
                $row->uniform_num = $customData->uniform_num;
				$row->fax = $customData->fax;
                $row->company_name = $company_name;
                $row->company_tel = $company_tel;
                $row->company_fax = $company_fax;
                $row->company_addr = $company_addr;
                $row->company_mail = $company_mail;
                $row->cnt_balance = null;
                $row->ototal=null;
                $row->otax=null;
                $row->osubtotal=null;
                $row->body_price=null;
                $row->body_subtotal=null;
                if($contractData){
                    $row->cnt_num =null;
                }
            }
		}


		$reportClass = new ReportController;
		//dd($reportData);
		$res = $reportClass->export($format,$jasperName,$reportData);

		return $res;
	}
}
?>
