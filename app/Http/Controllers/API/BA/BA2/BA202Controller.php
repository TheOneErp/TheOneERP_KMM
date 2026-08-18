<?php
namespace App\Http\Controllers\API\BA\BA2;

use App\Http\Controllers\Base\ReportController;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;
use App\Utils\VerifyUtil;
use Carbon\Carbon;

/*use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;*/

class BA202Controller extends Controller{
	static public function getCustomerOrder(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		//dd($data);
		/**判斷是否需要審核 */
		$vPageId = 53;
		$prefix = 'BA201_40';
		$vtable = DB::table($prefix);
		if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
			$vtable = $vtable->where("{$prefix}.data_options", "LIKE", '%"verify":{%"level":255%');
		}

		$custom = $vtable->select(DB::raw("[choose] = NULL, order_no = BA201_41.id,advanceday,body_num,o_body_price,body_price,body_quantity,body_rate,body_subtotal,tax,taxrate,currency,rate,client_order_code,product_code,product_name,remarks,unit_code,unit_name,BA201_41.packing_code,BA201_41.packing_name,BA201_41.discount,BA201_41.body_remarks,BA201_41.combi_code,BA201_41.combi_name,BA201_41.cost"))->leftJoin('BA201_41', 'BA201_40.id', '=', 'BA201_41.parent_id');
		//dd($custom);
		$custom = $custom->where('BA201_41.body_cancel','=','N')->whereRaw('body_quantity < body_num');
		//dd($custom);
		if( !is_null($data['advancedayS']) && $data['advancedayS'] != '' ){
			$custom = $custom->where('advanceday', '>=', $data['advancedayS']);
		}

		if( !is_null($data['advancedayE']) && $data['advancedayE'] != '' ){
			$custom = $custom->where('advanceday', '<=', $data['advancedayE']);
		}

		if( !is_null($data['client_order_codeS']) && $data['client_order_codeS'] != '' ){
			$custom = $custom->where('client_order_code', '>=', $data['client_order_codeS']);
		}

		if( !is_null($data['client_order_codeE']) && $data['client_order_codeE'] != '' ){
			$custom = $custom->where('client_order_code', '<=', $data['client_order_codeE']);
		}

		if( !is_null($data['product_codeS']) && $data['product_codeS'] != '' ){
			$custom = $custom->where('product_code', '>=', $data['product_codeS']);
		}

		if( !is_null($data['product_codeE']) && $data['product_codeE'] != '' ){
			$custom = $custom->where('product_code', '<=', $data['product_codeE']);
		}
		$custom = $custom->where('client_code', '=', $data['client_code'])->get();


		//dd($custom);
		return $custom;

	}
    static public function getCustomerOrder1(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		// dd($data);
		/**判斷是否需要審核 */
		$vPageId = 77;
		// $prefix = 'BA103_82';
		// $vtable = DB::table($prefix);
		// $custom = $vtable->select(DB::raw("[choose] = NULL,body_price,body_rate,product_code,product_name,unit_code,unit_name"));
		// $custom = $custom->where('client_code', '=', $data['client_code'])->get();

        $getdata=DB::table('BA103_82')
        ->selectraw('*,ROW_NUMBER() over (partition by product_code order by ship_code DESC) sn')
        ->where('client_code', '=', $data['client_code']);
        $bindings = $getdata->getBindings();

            $sql = str_replace('?', "'%s'", $getdata->toSql());

            $sql = sprintf($sql, ...$bindings);

            $save1=DB::table(DB::raw("($sql) as R"))
            ->selectraw('[choose] = NULL,body_price,body_price,body_rate,product_code,product_name,unit_code,unit_name,ship_date')
            ->where('R.sn', '=', 1)->get();
                 //dd($save1);
		return $save1;

	}
    static public function getProduct(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		// dd($data);
		/**判斷是否需要審核 */
		$vPageId = 77;
		$prefix = 'AA202_30';
		$vtable = DB::table($prefix);
		$Product = $vtable->select(DB::raw("[choose] = NULL,product_code,product_name,unit_code,unit_name,1 as body_rate,pro_cat_code,pro_cat_name,sell_price as body_price,sell_price as o_body_price,purchase_price as cost"));
		$Product = $Product->where('disable','<>','1');
        $Product->where(function($Product) use ($data){
        return $Product->where('product_code', 'like', '%'.$data['serach'].'%')
        ->orwhere('product_name', 'like', '%'.$data['serach'].'%')
        ->orwhere('unit_code', 'like', '%'.$data['serach'].'%')
        ->orwhere('unit_name', 'like', '%'.$data['serach'].'%')
        ->orwhere('pro_cat_code', 'like', '%'.$data['serach'].'%')
        ->orwhere('pro_cat_name', 'like', '%'.$data['serach'].'%');
        });
        $Product=$Product->get();


		return $Product;

	}
	static public function checkExisInShipBack( Request $request ){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		$shipBack = DB::table('BA203_62')->get();
		$shipBackData = $shipBack
                     ->where('ship_code', $data['ship_code'])
                     ->all();
		$editable = '0';
		if(count($shipBackData)>0){
			$editable =  '1';
		}
		return array("status"=>$editable,"res"=>$shipBackData);
	}

	static public function getBucketProduct( Request $request ){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

		$buckets = DB::table('EA205_80')->get();
		$bucketsData = $buckets
                     ->whereStrict('keg', $data['keg'])
                     ->first();
		$products = DB::table('AA202_30');

		$status = 0;
		$text = '';
		if( empty($bucketsData) ){
			$text = "很抱歉，此桶號不存在於桶號庫存查詢中，請確認。";
		}else if( $bucketsData->num <=0  ){
			$text = "很抱歉，此桶號於桶號庫存查詢中已無庫存，請確認。";
		}else{
			$body_price = $products->where('product_code', '=', $bucketsData->product_code)->pluck('sell_price')->first();
			$bucketsData->body_price = $body_price;
			$status = 1;
		}
		$bucketInfo = [
			'status'=>$status,
			'data'=>$bucketsData,
			'text'=>$text
		];
//		dd($bucketsData);
		return $bucketInfo;
	}

    //看板轉出貨
    static public function addship(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		$components = DB::table('DA201_45');

		$component = $components->select(DB::raw("[choose] = NULL,DA201_45.id,component_code,component_name,component_num,component_unit,component_unitname,component_rate,component_depot,component_depotname,component_remarks"))->leftJoin('DA201_46', 'DA201_45.id', '=', 'DA201_46.parent_id');

        $component = $component->where('DA201_45.id', '=', $data['station_id'])->get();

		return $component;
	}

	//匯出出貨單報表
	static public function printShip(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
			abort(400);

		$format = "pdf";

		$ship_code = $data['ship_code'];
		$type = $data['type'];

		$reportData = DB::table('BA202_52 as a')
                ->select(DB::raw("a.ship_code,a.ship_date,a.client_code,a.client_name,
                a.fax,     
				a.otax as otax,
				a.osubtotal as osubtotal,
				ROUND(a.ototal, 0) as ototal,a.invoice_num,a.remarks as head_remarks,b.product_code, b.product_name ,
                b.body_num,b.unit_name, b.body_price ,b.body_subtotal ,b.remarks,b.gift_options,'1' as sn,b.id as idid,'1' as con") )
				->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
				->where('ship_code','=',$ship_code);
        $reportData1 = DB::table('BA202_52 as a')
        ->select(DB::raw("a.ship_code,a.ship_date,a.client_code,a.client_name ,
        a.fax ,
		a.otax as otax,
        a.osubtotal as osubtotal,
        ROUND(a.ototal, 0) as ototal,a.invoice_num,a.remarks as head_remarks,
        d.cont_code as product_code,d.cont_name as product_name ,
        CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as body_num,d.unit_name,
        null as body_price ,null as body_subtotal ,null as remarks,null as gift_options,'2' as sn,b.id as idid,null as con") )
    ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
    // ->leftJoin('AA204_2224 as c', 'b.combi_name','=','c.combi_name')
	->leftJoin('AA204_2224 as c', function($q) use ($type)
    {
        $q->on('b.combi_code', '=', 'c.combi_code')
            ->on('b.product_code', '=', 'c.product_code');
    })
    ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
    ->where('ship_code','=',$ship_code)
    ->whereNotNull('b.combi_code');
		$client_code = $reportData->pluck('client_code')->first();
		$customData = DB::table('BA102_37 as a')
		->select(DB::raw("a.client_code,a.client_name,a.cnt_balance,a.yn_cnt_cust,a.uniform_num,b.phone,b.contact,c.addr,a.client_cat") )
		->leftJoin('BA102_38 as b', 'b.parent_id','=','a.id')
		->leftJoin('BA102_39 as c', 'c.parent_id','=','a.id')
		->where('client_code','=',$client_code)
		->orderBy('b.id')->first();
        $contractData = DB::table('BA104_6241 as a')
        ->where('client_code','=',$client_code)
        ->orderBy('a.id','DESC')
        ->first();
        $reportData = $reportData->union($reportData1)->orderBy('client_code')->orderBy('idid')->orderBy('sn')->get();
        if($customData->yn_cnt_cust){
            $jasperName = "BA202_contract";
        }else{
            $jasperName = "BA202_price";
        }
        $company_name = "孔媽媽";
			$company_tel = "07-6285979";
			$company_fax = "";
			$company_addr = "820高雄市岡山區大莊里大莊路350號";
			$company_mail = "";
		if( $type == "type1" ){

            foreach($reportData as $key=>$row ){
                $row->phone = $customData->phone;
                $row->contact = $customData->contact;
                $row->addr = $customData->addr;
				$row->client_cat = $customData->client_cat;
                $row->uniform_num = $customData->uniform_num;
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
                $row->contact = $customData->contact;
                $row->addr = $customData->addr;
				$row->client_cat = $customData->client_cat;
                $row->uniform_num = $customData->uniform_num;
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
	static public function printShip1(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
			abort(400);

		$format = "pdf";

		$ship_code = $data['ship_code'];
		$type = $data['type'];

		$reportData = DB::table('BA211_6263 as a')
                ->select(DB::raw("a.ship_code,a.ship_date,a.client_code,a.client_name ,      
				a.otax as otax,
				a.osubtotal as osubtotal,
				ROUND(a.ototal, 0) as ototal,a.invoice_num,a.remarks as head_remarks,b.product_code, b.product_name ,
                b.body_num,b.unit_name, b.body_price ,b.body_subtotal ,b.remarks,b.gift_options,'1' as sn,b.id as idid,'1' as con") )
				->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id')
				->where('ship_code','=',$ship_code);
        $reportData1 = DB::table('BA211_6263 as a')
        ->select(DB::raw("a.ship_code,a.ship_date,a.client_code,a.client_name ,      
		a.otax as otax,
        a.osubtotal as osubtotal,
        ROUND(a.ototal, 0) as ototal,a.invoice_num,a.remarks as head_remarks,
        d.cont_code as product_code,d.cont_name as product_name ,
        CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as body_num,d.unit_name,
        null as body_price ,null as body_subtotal ,null as remarks,null as gift_options,'2' as sn,b.id as idid,null as con") )
    ->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id')
    // ->leftJoin('AA204_2224 as c', 'b.combi_name','=','c.combi_name')
	->leftJoin('AA204_2224 as c', function($q) use ($type)
    {
        $q->on('b.combi_code', '=', 'c.combi_code')
            ->on('b.product_code', '=', 'c.product_code');
    })
    ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
    ->where('ship_code','=',$ship_code)
    ->whereNotNull('b.combi_code');
		$client_code = $reportData->pluck('client_code')->first();
		$customData = DB::table('BA102_37 as a')
		->select(DB::raw("a.client_code,a.client_name,a.cnt_balance,a.yn_cnt_cust,a.uniform_num,b.phone,b.contact,c.addr,a.client_cat") )
		->leftJoin('BA102_38 as b', 'b.parent_id','=','a.id')
		->leftJoin('BA102_39 as c', 'c.parent_id','=','a.id')
		->where('client_code','=',$client_code)
		->orderBy('b.id')->first();
        $contractData = DB::table('BA104_6241 as a')
        ->where('client_code','=',$client_code)
        ->orderBy('a.id','DESC')
        ->first();
        $reportData = $reportData->union($reportData1)->orderBy('client_code')->orderBy('idid')->orderBy('sn')->get();
        if($customData->yn_cnt_cust){
            $jasperName = "BA202_contract";
        }else{
            $jasperName = "BA202_price";
        }
        $company_name = "孔媽媽";
		$company_tel = "07-6285979";
			$company_fax = "";
			$company_addr = "820高雄市岡山區大莊里大莊路350號";
			$company_mail = "";
		if( $type == "type1" ){

            foreach($reportData as $key=>$row ){
                $row->phone = $customData->phone;
                $row->contact = $customData->contact;
                $row->addr = $customData->addr;
				$row->client_cat = $customData->client_cat;
                $row->uniform_num = $customData->uniform_num;
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
                $row->contact = $customData->contact;
                $row->addr = $customData->addr;
				$row->client_cat = $customData->client_cat;
                $row->uniform_num = $customData->uniform_num;
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
    static public function printShip2(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
			abort(400);

		$format = "pdf";

		$ship_code = $data['ship_code'];
		$type = "type1";

		$reportData = DB::table('BA211_6263 as a')
                ->select(DB::raw("a.ship_code,a.ship_date,a.client_code,a.client_name ,     
				a.otax as otax,
				a.osubtotal as osubtotal,
				ROUND(a.ototal, 0) as ototal,a.invoice_num,a.remarks as head_remarks,b.product_code, b.product_name ,
                b.body_num,b.unit_name, b.body_price ,b.body_subtotal ,b.remarks,b.gift_options,'1' as sn,b.id as idid,'1' as con") )
				->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id')
				->where('ship_code','=',$ship_code);
        $reportData1 = DB::table('BA211_6263 as a')
        ->select(DB::raw("a.ship_code,a.ship_date,a.client_code,a.client_name ,      
		a.otax as otax,
        a.osubtotal as osubtotal,
        ROUND(a.ototal, 0) as ototal,a.invoice_num,a.remarks as head_remarks,
        d.cont_code as product_code,d.cont_name as product_name ,
        CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as body_num,d.unit_name,
        null as body_price ,null as body_subtotal ,null as remarks,null as gift_options,'2' as sn,b.id as idid,null as con") )
    ->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id')
    // ->leftJoin('AA204_2224 as c', 'b.combi_name','=','c.combi_name')
	->leftJoin('AA204_2224 as c', function($q) use ($type)
    {
        $q->on('b.combi_code', '=', 'c.combi_code')
            ->on('b.product_code', '=', 'c.product_code');
    })
    ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
    ->where('ship_code','=',$ship_code)
    ->whereNotNull('b.combi_code');
		$client_code = $reportData->pluck('client_code')->first();
		$customData = DB::table('BA102_37 as a')
		->select(DB::raw("a.client_code,a.client_name,a.cnt_balance,a.yn_cnt_cust,a.uniform_num,b.phone,b.contact,c.addr,a.client_cat") )
		->leftJoin('BA102_38 as b', 'b.parent_id','=','a.id')
		->leftJoin('BA102_39 as c', 'c.parent_id','=','a.id')
		->where('client_code','=',$client_code)
		->orderBy('b.id')->first();
        $contractData = DB::table('BA104_6241 as a')
        ->where('client_code','=',$client_code)
        ->orderBy('a.id','DESC')
        ->first();
        $reportData = $reportData->union($reportData1)->orderBy('client_code')->orderBy('idid')->orderBy('sn')->get();
        // if($customData->yn_cnt_cust){
        //     $jasperName = "BA202_contract";
        // }else{
        //     $jasperName = "BA202_price";
        // }
        $jasperName = "BA211";
        $company_name = "孔媽媽";
			$company_tel = "07-6285979";
			$company_fax = "";
			$company_addr = "820高雄市岡山區大莊里大莊路350號";
			$company_mail = "";
		if( $type == "type1" ){

            foreach($reportData as $key=>$row ){
                $row->phone = $customData->phone;
                $row->contact = $customData->contact;
                $row->addr = $customData->addr;
				$row->client_cat = $customData->client_cat;
                $row->uniform_num = $customData->uniform_num;
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
                $row->contact = $customData->contact;
                $row->addr = $customData->addr;
				$row->client_cat = $customData->client_cat;
                $row->uniform_num = $customData->uniform_num;
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
