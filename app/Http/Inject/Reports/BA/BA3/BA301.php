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
        ->select(DB::raw("a.ship_code,a.client_code,b.body_depot_name as depot_name, a.client_name,c.client_catname, a.undertakerday, a.ship_date,a.stotal,a.invoice_num,a.remarks as h_remarks,b.remarks,b.product_code, b.product_name ,b.body_num,b.unit_name, b.body_price ,CAST( CASE
        WHEN b_tax = '稅外加'
           THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) )
        ELSE b.body_subtotal
   END AS decimal(10,2)) as body_subtotal ,b.payment_status,'1' as sn,b.id as idid"))
                // ->select('a.ship_code','a.client_code', 'a.client_name', 'a.undertakerday', 'a.ship_date','b.product_code', 'b.product_name' ,'b.body_num','b.unit_name', 'b.body_price' ,'b.body_subtotal' ,'b.remarks','b.payment_status')
                ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
                ->leftJoin('BA102_37 as c', 'a.client_code','=','c.client_code')
                ->leftJoin('AA202_30 as d', 'd.product_code','=','b.product_code');
        $BA202data1 = DB::table('BA202_52 as a')
        ->select(DB::raw("a.ship_code,a.client_code,b.body_depot_name as depot_name, a.client_name,e.client_catname, a.undertakerday, a.ship_date,a.stotal,a.invoice_num,a.remarks as h_remarks,b.remarks,d.cont_code as product_code,d.cont_name as product_name ,
        CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as body_num,d.unit_name,
        null as body_price ,null as body_subtotal ,null as payment_status,'2' as sn,b.id as idid"))
    ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
    ->leftJoin('AA204_2224 as c', function($q) use ($type)
    {
        $q->on('b.combi_code', '=', 'c.combi_code')
            ->on('b.product_code', '=', 'c.product_code');
    })
    ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
    ->leftJoin('BA102_37 as e', 'a.client_code','=','e.client_code')
    ->leftJoin('AA202_30 as f', 'f.product_code','=','b.product_code')
    ->whereNotNull('b.combi_code');
// dd($BA202data->get());
        // $BA202data_shipcode =DB::table('BA202_52 as a')
        // ->select('a.ship_code','a.client_code', 'a.client_name', 'a.undertakerday', 'a.ship_date','b.product_code', 'b.product_name' ,'b.body_num','b.unit_name', 'b.body_price' ,'b.body_subtotal' ,'b.remarks','b.payment_status')
        // ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id');


        $BA203data = DB::table('BA203_61 as a')
        ->select(DB::raw("a.back_code as ship_code,a.client_code as client_code,b.body_depot_name as depot_name, a.client_name as client_name,c.client_catname, a.undertakerday as undertakerday, a.back_day as ship_date,-a.stotal as stotal,null as invoice_num,a.remarks as h_remarks,b.body_remarks as remarks,b.product_code as product_code, b.product_name as product_name ,- b.body_num as body_num,b.unit_name as unit_name, b.body_price as body_price ,CAST( CASE
        WHEN a.tax = '稅外加'
           THEN -b.body_subtotal * ( 1 +  CAST(('0' + a.taxrate) AS float) )
        ELSE -b.body_subtotal
   END AS decimal(10,2)) as body_subtotal,null as payment_status ,'1' as sn,b.ship_no as idid"))
        // ->select('a.back_code','b.ship_code as ship_code2','a.client_code as client_code', 'a.client_name as client_name', 'a.undertakerday as undertakerday', 'a.back_day','b.product_code as product_code', 'b.product_name as product_name' ,'b.body_num as body_num2','b.unit_name as unit_name2', 'b.body_price as body_price2' ,'b.body_subtotal as body_subtotal2' ,'b.client_order_code')
        ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
        ->leftJoin('BA102_37 as c', 'a.client_code','=','c.client_code')
        ->leftJoin('AA202_30 as d', 'd.product_code','=','b.product_code');
        $BA203data1 = DB::table('BA203_61 as a')
        ->select(DB::raw("a.back_code as ship_code,a.client_code as client_code,b.body_depot_name as depot_name, a.client_name as client_name,e.client_catname, a.undertakerday as undertakerday, a.back_day as ship_date,-a.stotal as stotal,null as invoice_num,a.remarks as h_remarks,b.body_remarks as remarks,
        d.cont_code as product_code,d.cont_name as product_name ,CAST(- b.body_num*d.body_num*d.body_rate AS decimal(10,2))  as body_num,d.unit_name as unit_name,
         null as body_price ,null as body_subtotal ,null as payment_status,'2' as sn,b.ship_no as idid"))
         ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
         ->leftJoin('AA204_2224 as c', 'b.combi_code','=','c.combi_code')
        ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
        ->leftJoin('BA102_37 as e', 'a.client_code','=','e.client_code')
        ->leftJoin('AA202_30 as f', 'f.product_code','=','b.product_code')
         ->whereNotNull('b.combi_code');
      //   dd($BA203data->get());

        $BA211data = DB::table('BA211_6263 as a')
        ->select(DB::raw("a.ship_code,a.client_code,b.body_depot_name as depot_name, a.client_name,c.client_catname, a.undertakerday, a.ship_date,a.stotal,a.invoice_num,a.remarks as h_remarks,b.remarks,b.product_code, b.product_name ,b.body_num,b.unit_name, b.body_price ,CAST( CASE
        WHEN b_tax = '稅外加'
           THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) )
        ELSE b.body_subtotal
   END AS decimal(10,2)) as body_subtotal ,b.payment_status,'1' as sn,b.id as idid"))
                // ->select('a.ship_code','a.client_code', 'a.client_name', 'a.undertakerday', 'a.ship_date','b.product_code', 'b.product_name' ,'b.body_num','b.unit_name', 'b.body_price' ,'b.body_subtotal' ,'b.remarks','b.payment_status')
                ->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id')
                ->leftJoin('BA102_37 as c', 'a.client_code','=','c.client_code')
                ->leftJoin('AA202_30 as d', 'd.product_code','=','b.product_code');
        $BA211data1 = DB::table('BA211_6263 as a')
        ->select(DB::raw("a.ship_code,a.client_code,b.body_depot_name as depot_name, a.client_name,e.client_catname, a.undertakerday, a.ship_date,a.stotal,a.invoice_num,a.remarks as h_remarks,b.remarks,d.cont_code as product_code,d.cont_name as product_name ,
        CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as body_num,d.unit_name,
        null as body_price ,null as body_subtotal ,null as payment_status,'2' as sn,b.id as idid"))
    ->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id')
    ->leftJoin('AA204_2224 as c', function($q) use ($type)
    {
        $q->on('b.combi_code', '=', 'c.combi_code')
            ->on('b.product_code', '=', 'c.product_code');
    })
    ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
    ->leftJoin('BA102_37 as e', 'a.client_code','=','e.client_code')
    ->leftJoin('AA202_30 as f', 'f.product_code','=','b.product_code')
    ->whereNotNull('b.combi_code');
      
        //dd($BA202data,$BA203data);
        if( !empty($filters['s_undertakerday']) ){
			$BA202data = $BA202data->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA202data1 = $BA202data1->where('ship_date', '>=', $filters['s_undertakerday']);
            // $BA202data_shipcode = $BA202data_shipcode->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA203data = $BA203data->where('a.back_day', '>=', $filters['s_undertakerday']);
            $BA203data1 = $BA203data1->where('a.back_day', '>=', $filters['s_undertakerday']);
            $BA211data = $BA211data->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA211data1 = $BA211data1->where('ship_date', '>=', $filters['s_undertakerday']);
		}

		if( !empty($filters['e_undertakerday']) ){
			$BA202data = $BA202data->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA202data1 = $BA202data1->where('ship_date', '<=', $filters['e_undertakerday']);
            // $BA202data_shipcode = $BA202data_shipcode->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA203data = $BA203data->where('a.back_day', '<=', $filters['e_undertakerday']);
            $BA203data1 = $BA203data1->where('a.back_day', '<=', $filters['e_undertakerday']);
            $BA211data = $BA211data->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA211data1 = $BA211data1->where('ship_date', '<=', $filters['e_undertakerday']);
		}

		if( !empty($filters['s_client_code']) ){
			$BA202data = $BA202data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA202data1 = $BA202data1->where('a.client_code', '>=', $filters['s_client_code']);
            // $BA202data_shipcode = $BA202data_shipcode->where('client_code', '>=', $filters['s_client_code']);
            $BA203data = $BA203data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA203data1 = $BA203data1->where('a.client_code', '>=', $filters['s_client_code']);
            $BA211data = $BA211data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA211data1 = $BA211data1->where('a.client_code', '>=', $filters['s_client_code']);
		}

		if( !empty($filters['e_client_code']) ){
			$BA202data = $BA202data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA202data1 = $BA202data1->where('a.client_code', '<=', $filters['e_client_code']);
            // $BA202data_shipcode = $BA202data_shipcode->where('client_code', '<=', $filters['e_client_code']);
            $BA203data = $BA203data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA203data1 = $BA203data1->where('a.client_code', '<=', $filters['e_client_code']);
            $BA211data = $BA211data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA211data1 = $BA211data1->where('a.client_code', '<=', $filters['e_client_code']);
		}

		if( !empty($filters['s_product_code']) ){
			$BA202data = $BA202data->where('b.product_code', '>=', $filters['s_product_code']);
            $BA202data1 = $BA202data1->where('b.product_code', '>=', $filters['s_product_code']);
            // $BA202data_shipcode = $BA202data_shipcode->where('product_code', '>=', $filters['s_product_code']);
            $BA203data = $BA203data->where('b.product_code', '>=', $filters['s_product_code']);
            $BA203data1 = $BA203data1->where('b.product_code', '>=', $filters['s_product_code']);
            $BA211data = $BA211data->where('b.product_code', '>=', $filters['s_product_code']);
            $BA211data1 = $BA211data1->where('b.product_code', '>=', $filters['s_product_code']);
            //dd($BA202data);
        }

		if( !empty($filters['payment_status']) ){
            $BA202data = $BA202data->where('payment_status','=', $filters['payment_status']);
            $BA202data1 = $BA202data1->where('payment_status','=', $filters['payment_status']);
            $BA211data = $BA211data->where('payment_status','=', $filters['payment_status']);
            $BA211data1 = $BA211data1->where('payment_status','=', $filters['payment_status']);
            // $BA202data_shipcode = $BA202data_shipcode->where('payment_status','=', $filters['payment_status']);
        }

        if( !empty($filters['e_product_code']) ){
			$BA202data = $BA202data->where('b.product_code', '<=', $filters['e_product_code']);
            $BA202data1 = $BA202data1->where('b.product_code', '<=', $filters['e_product_code']);
            // $BA202data_shipcode = $BA202data_shipcode->where('product_code', '<=', $filters['e_product_code']);
            $BA203data = $BA203data->where('b.product_code', '<=', $filters['e_product_code']);
            $BA203data1 = $BA203data1->where('b.product_code', '<=', $filters['e_product_code']);
            $BA211data = $BA211data->where('b.product_code', '<=', $filters['e_product_code']);
            $BA211data1 = $BA211data1->where('b.product_code', '<=', $filters['e_product_code']);
		}

        if( !empty($filters['pro_cat_code']) ){
            $array1=explode(';',$filters['pro_cat_code']);
            $BA202data->where(function ($BA202data) use ($array1) {
                for($i=0;$i<=count($array1)-1;$i++){
                    if($array1[$i]!=""){
                        $BA202data=$BA202data->orwhere('d.pro_cat_code','=',$array1[$i]);
                    }
                }
            });
            $BA202data1->where(function ($BA202data1) use ($array1) {
                for($i=0;$i<=count($array1)-1;$i++){
                    if($array1[$i]!=""){
                        $BA202data1=$BA202data1->orwhere('f.pro_cat_code','=',$array1[$i]);
                    }
                }
            });
            $BA203data->where(function ($BA203data) use ($array1) {
                for($i=0;$i<=count($array1)-1;$i++){
                    if($array1[$i]!=""){
                        $BA203data=$BA203data->orwhere('d.pro_cat_code','=',$array1[$i]);
                    }
                }
            });
            $BA203data1->where(function ($BA203data1) use ($array1) {
                for($i=0;$i<=count($array1)-1;$i++){
                    if($array1[$i]!=""){
                        $BA203data1=$BA203data1->orwhere('f.pro_cat_code','=',$array1[$i]);
                    }
                }
            });
            $BA211data->where(function ($BA211data) use ($array1) {
                for($i=0;$i<=count($array1)-1;$i++){
                    if($array1[$i]!=""){
                        $BA211data=$BA211data->orwhere('d.pro_cat_code','=',$array1[$i]);
                    }
                }
            });
            $BA211data1->where(function ($BA211data1) use ($array1) {
                for($i=0;$i<=count($array1)-1;$i++){
                    if($array1[$i]!=""){
                        $BA211data1=$BA211data1->orwhere('f.pro_cat_code','=',$array1[$i]);
                    }
                }
            });
        }

        $save = $BA202data->unionAll($BA202data1)->unionAll($BA203data)->unionAll($BA203data1)->unionAll($BA211data)->unionAll($BA211data1);

        $bindings = $save->getBindings();
        $sql = str_replace('?', "'%s'", $save->toSql());
        $sql = sprintf($sql, ...$bindings);

        $save1 = DB::table(DB::raw("($sql) as R"));

        if( !empty($filters['yn_cnt']) ){
            if( $filters['yn_cnt'] == "合約" ){
                $save1->where(function ($save1) use ($filters) {
                    $save1=$save1->orwhere('ship_code','like','BA202-%');
                    $save1=$save1->orwhere('ship_code','like','BA203-%');
                });
            }else if( $filters['yn_cnt'] == "非合約" ){
                $save1 = $save1->where('ship_code', 'like', 'BA211-%');
            }
        }

        $save1 = $save1
            ->orderby('client_code')  // <--- 關鍵！優先依照「客戶代碼」排序，同一客戶的資料才會連續排在一起
            ->orderby('ship_date')
            ->orderby('ship_code')
            ->orderby('idid')
            ->orderby('sn')
            ->orderby('product_code')
            ->get();

       
        

        $this->datas = $save1;
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
            if($check == $row->ship_code){
                $row->stotal = null;
            }else{
                $check = $row->ship_code;
            }
        }
		 dd($this->datas);

    }
}
