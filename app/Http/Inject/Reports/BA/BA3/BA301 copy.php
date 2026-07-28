<?php

namespace App\Http\Inject\Reports\BA\BA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class BA301{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
        $type="";
        $BA202data = DB::table('BA202_52 as a')
        ->select(DB::raw("a.ship_code,a.client_code,a.depot_name, a.client_name,c.client_catname, a.undertakerday, a.ship_date,a.stotal,a.invoice_num,a.remarks as h_remarks,b.product_code, b.product_name ,b.gift_options,b.body_num,b.unit_name, b.body_price ,CAST( CASE
        WHEN b_tax = '稅外加'
           THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) )
        ELSE b.body_subtotal
   END AS decimal(10,2)) as body_subtotal ,b.remarks,b.payment_status,'1' as sn,b.id as idid"))
                // ->select('a.ship_code','a.client_code', 'a.client_name', 'a.undertakerday', 'a.ship_date','b.product_code', 'b.product_name' ,'b.body_num','b.unit_name', 'b.body_price' ,'b.body_subtotal' ,'b.remarks','b.payment_status')
                ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
                ->leftJoin('BA102_37 as c', 'a.client_code','=','c.client_code');
        $BA202data1 = DB::table('BA202_52 as a')
        ->select(DB::raw("a.ship_code,a.client_code,a.depot_name, a.client_name,e.client_catname, a.undertakerday, a.ship_date,a.stotal,a.invoice_num,a.remarks as h_remarks,d.cont_code as product_code,d.cont_name as product_name ,null as gift_options,
        CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as body_num,d.unit_name,
        null as body_price ,null as body_subtotal ,null as remarks,null as payment_status,'2' as sn,b.id as idid"))
    ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
    ->leftJoin('AA204_2224 as c', function($q) use ($type)
    {
        $q->on('b.combi_code', '=', 'c.combi_code')
            ->on('b.product_code', '=', 'c.product_code');
    })
    ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
    ->leftJoin('BA102_37 as e', 'a.client_code','=','e.client_code')
    ->whereNotNull('b.combi_code');
// dd($BA202data->get());
        $BA202data_shipcode =DB::table('BA202_52 as a')
        ->select('a.ship_code','a.client_code', 'a.client_name', 'a.undertakerday', 'a.ship_date','b.product_code', 'b.product_name' ,'b.body_num','b.unit_name', 'b.body_price' ,'b.body_subtotal' ,'b.remarks','b.payment_status')
        ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id');


        $BA203data = DB::table('BA203_61 as a')
        ->select(DB::raw("a.back_code,b.ship_code as ship_code2,a.client_code as client_code, a.client_name as client_name,c.client_catname, a.undertakerday as undertakerday, a.back_day,-a.stotal as stotal,a.remarks as h_remarks,b.body_remarks as remarks,b.product_code as product_code, b.product_name as product_name ,b.gift_options,- b.body_num as body_num2,b.unit_name as unit_name2, b.body_price as body_price2 ,CAST( CASE
        WHEN a.tax = '稅外加'
           THEN -b.body_subtotal * ( 1 +  CAST(('0' + a.taxrate) AS float) )
        ELSE -b.body_subtotal
   END AS decimal(10,2)) as body_subtotal2 ,b.client_order_code,'1' as sn,b.ship_no as idid"))
        // ->select('a.back_code','b.ship_code as ship_code2','a.client_code as client_code', 'a.client_name as client_name', 'a.undertakerday as undertakerday', 'a.back_day','b.product_code as product_code', 'b.product_name as product_name' ,'b.body_num as body_num2','b.unit_name as unit_name2', 'b.body_price as body_price2' ,'b.body_subtotal as body_subtotal2' ,'b.client_order_code')
        ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
        ->leftJoin('BA102_37 as c', 'a.client_code','=','c.client_code');
        $BA203data1 = DB::table('BA203_61 as a')
        ->select(DB::raw("a.back_code,b.ship_code as ship_code2,a.client_code as client_code, a.client_name as client_name,e.client_catname, a.undertakerday as undertakerday, a.back_day,-a.stotal as stotal,a.remarks as h_remarks,b.body_remarks as remarks,
        d.cont_code as product_code,d.cont_name as product_name,null as gift_options ,CAST(- b.body_num*d.body_num*d.body_rate AS decimal(10,2))  as body_num2,d.unit_name as unit_name2,
         null as body_price2 ,null as body_subtotal2 ,b.client_order_code,'2' as sn,b.ship_no as idid"))
         ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
         ->leftJoin('AA204_2224 as c', 'b.combi_code','=','c.combi_code')
        ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
        ->leftJoin('BA102_37 as e', 'a.client_code','=','e.client_code')
         ->whereNotNull('b.combi_code');
      //   dd($BA203data->get());

        //dd($BA202data,$BA203data);
        if( !empty($filters['s_undertakerday']) ){
			$BA202data = $BA202data->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA202data1 = $BA202data1->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA202data_shipcode = $BA202data_shipcode->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA203data = $BA203data->where('a.back_day', '>=', $filters['s_undertakerday']);
            $BA203data1 = $BA203data1->where('a.back_day', '>=', $filters['s_undertakerday']);
		}

		if( !empty($filters['e_undertakerday']) ){
			$BA202data = $BA202data->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA202data1 = $BA202data1->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA202data_shipcode = $BA202data_shipcode->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA203data = $BA203data->where('a.back_day', '<=', $filters['e_undertakerday']);
            $BA203data1 = $BA203data1->where('a.back_day', '<=', $filters['e_undertakerday']);
		}

		if( !empty($filters['s_client_code']) ){
			$BA202data = $BA202data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA202data1 = $BA202data1->where('a.client_code', '>=', $filters['s_client_code']);
            $BA202data_shipcode = $BA202data_shipcode->where('client_code', '>=', $filters['s_client_code']);
            $BA203data = $BA203data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA203data1 = $BA203data1->where('a.client_code', '>=', $filters['s_client_code']);
		}

		if( !empty($filters['e_client_code']) ){
			$BA202data = $BA202data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA202data1 = $BA202data1->where('a.client_code', '<=', $filters['e_client_code']);
            $BA202data_shipcode = $BA202data_shipcode->where('client_code', '<=', $filters['e_client_code']);
            $BA203data = $BA203data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA203data1 = $BA203data1->where('a.client_code', '<=', $filters['e_client_code']);
		}

		if( !empty($filters['s_product_code']) ){
			$BA202data = $BA202data->where('b.product_code', '>=', $filters['s_product_code']);
            $BA202data1 = $BA202data1->where('b.product_code', '>=', $filters['s_product_code']);
            $BA202data_shipcode = $BA202data_shipcode->where('product_code', '>=', $filters['s_product_code']);
            $BA203data = $BA203data->where('product_code', '>=', $filters['s_product_code']);
            $BA203data1 = $BA203data1->where('b.product_code', '>=', $filters['s_product_code']);
            //dd($BA202data);
        }

		if( !empty($filters['payment_status']) ){
            $BA202data = $BA202data->where('payment_status','=', $filters['payment_status']);
            $BA202data1 = $BA202data1->where('payment_status','=', $filters['payment_status']);
            $BA202data_shipcode = $BA202data_shipcode->where('payment_status','=', $filters['payment_status']);
        }

        if( !empty($filters['e_product_code']) ){
			$BA202data = $BA202data->where('b.product_code', '<=', $filters['e_product_code']);
            $BA202data1 = $BA202data1->where('b.product_code', '<=', $filters['e_product_code']);
            $BA202data_shipcode = $BA202data_shipcode->where('product_code', '<=', $filters['e_product_code']);
            $BA203data = $BA203data->where('product_code', '<=', $filters['e_product_code']);
            $BA203data1 = $BA203data1->where('b.product_code', '<=', $filters['e_product_code']);
		}

        if (VerifyUtil::pageVerifyConfirmation(59)) {
			$BA202data = $BA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
        $BA202data_shipcode = $BA202data_shipcode
        ->select('a.ship_code')
        ->groupby('ship_code')
        ->pluck('ship_code')
        ->toarray();
        //dd($BA202data);
        //dd($BA203data->get());

        $BA202data = $BA202data->union($BA202data1)->orderBy('client_code')->orderBy('idid')->orderBy('sn')->get()->toarray();
        //dd($BA202data);


        $BA203data = $BA203data->union($BA203data1)/*->wherein("ship_code",$BA202data_shipcode)*/->orderBy('idid')->orderBy('sn')->get()->toarray();
      //  dd($BA203data);
        foreach($BA203data as $key=>$row ){
            $row->ship_date = null;
            $row->ship_code = null;
        }
        //dd($BA203data);

        //for($i=0;$i<=(count($BA202data)-1);$i++){
        //    for($y=0;$y<=(count($BA203data)-1);$y++){
         //   if(($BA202data[$i]->ship_code == $BA203data[$y]->ship_code2) && ($BA202data[$i]->product_code==$BA203data[$y]->product_code)){
        //        dd(($BA202data[$i]->body_num-$BA203data[$y]->body_num2));
         //       $BA202data[$i]->body_num == 0;//intval($BA202data[$i]->body_num)-intval($BA203data[$y]->body_num2);
         //       $BA202data[$i]->body_price == 0;//intval($BA202data[$i]->body_price)-intval($BA203data[$y]->body_price2);
         //       }
         //   }
       //}

        //dd($BA202data,$BA203data);


        for($i=0;$i<=count($BA203data)-1;$i++){
            array_push($BA202data,$BA203data[$i]);
        }
        foreach ($BA202data as $key => $row) {
            if($row->ship_date){
                $row->datas=$row->ship_date;
            }else{
                $row->datas=$row->back_day;
            }
            if($row->ship_code){
                $row->code=$row->ship_code;
            }else{
                $row->code=$row->back_code;
            }
        }

        //dd($BA202data);
        $client_code = array_column($BA202data, 'client_code');
        $ship_code = array_column($BA202data, 'ship_code');
        $idid = array_column($BA202data, 'idid');
        $sn = array_column($BA202data, 'sn');
        $product_code  = array_column($BA202data, 'product_code');
        $ship_date = array_column($BA202data, 'ship_date');
        $datas = array_column($BA202data, 'datas');
        $code = array_column($BA202data, 'code');
        foreach ($datas as $key => $part) {

            $sort[$key] = strtotime($part);
       }
       //dd($client_code,$ship_code,$idid,$sn,$product_code,$product_code,$ship_date,$datas);
// dd($BA202data);
        if($BA202data != null){
            array_multisort($datas, SORT_ASC,$code, SORT_ASC,$idid, SORT_ASC,$sn, SORT_ASC,$product_code, SORT_ASC, $sort, SORT_ASC, $BA202data);
        }
        //dd($BA202data);
        //$BA202data = $BA202data->sortBy('client_code');
        //$BA202data->values()->all();
        //dd($BA202data_shipcode,$BA202data,$BA203data);
        //asort($BA202data);

        $this->datas = $BA202data;
        $user = User::find(SessionUtil::getUserID())->name;
        $check="";
        foreach($this->datas as $key=>$row ){
            $row->s_undertakerday = $filters['s_undertakerday'];
            $row->e_undertakerday = $filters['e_undertakerday'];
            $row->s_client_code = $filters['s_client_code'];
            $row->e_client_code = $filters['e_client_code'];
            $row->s_product_code = $filters['s_product_code'];
            $row->e_product_code = $filters['e_product_code'];


            $row->user = $user;
            if($check == $row->code){
                $row->stotal = null;
            }else{
                $check = $row->code;
            }
        }
		// dd($this->datas);

    }
}
