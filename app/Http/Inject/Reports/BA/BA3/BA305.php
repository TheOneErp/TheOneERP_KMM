<?php

namespace App\Http\Inject\Reports\BA\BA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class BA305{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
        $type="";
        $BA201data = DB::table('BA201_40 as a')
        ->select(DB::raw("a.client_code, a.client_name,a.undertakerday,a.client_order_code,c.client_cat,(CASE WHEN a.yn_n_sales = '1' THEN 0 ELSE a.stotal END ) as stotal,a.remarks,b.product_code, b.product_name,b.body_num,b.body_price ,(CASE WHEN a.yn_n_sales = '1' THEN 0 ELSE b.body_subtotal END ) as body_subtotal,b.body_remarks,payment_status,c.client_catname,2 as sn1,2 as sn2,b.id as idid"))
                ->leftJoin('BA201_41 as b', 'b.parent_id','=','a.id')
                ->leftJoin('BA102_37 as c', 'a.client_code','=','c.client_code');
        $BA201data1 = DB::table('BA201_40 as a')
                ->leftJoin('BA201_41 as b', 'b.parent_id', '=', 'a.id')
                ->leftJoin('AA204_2224 as c', function($q) {
                    $q->on('b.combi_code', '=', 'c.combi_code')
                      ->on('b.product_code', '=', 'c.product_code');
                })
                ->leftJoin('AA204_2225 as d', 'd.parent_id', '=', 'c.id')
                ->leftJoin('BA102_37 as e', 'a.client_code', '=', 'e.client_code')
                ->select(DB::raw("
                    a.client_code,                             -- (1)
                    a.client_name,                             -- (2)
                    a.undertakerday,                           -- (3)
                    a.client_order_code,                       -- (4)
                    e.client_cat,                              -- ← 新增：第 5 欄 (對齊第一段的 c.client_cat)
                    (CASE WHEN a.yn_n_sales='1' THEN 0 ELSE a.stotal END) AS stotal,  -- (6)
                    NULL AS remarks,                           -- (7)
                    d.cont_code AS product_code,               -- (8)
                    d.cont_name AS product_name,               -- (9)
                    CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) AS body_num,  -- (10)
                    NULL AS body_price,                        -- (11)
                    NULL AS body_subtotal,                     -- (12)
                    NULL AS body_remarks,                      -- (13)
                    NULL AS payment_status,                    -- (14)
                    e.client_catname,                          -- (15)
                    2 as sn1,
                    3 AS sn2,                                   -- (16)
                    b.id AS idid                               -- (17)
                "))
                ->whereNotNull('b.combi_code');

        $BA104data = DB::table("BA104_6241 as a")
        ->select(DB::raw("
          a.client_code,                             -- (1)
          a.client_name,                             -- (2)
          a.cnt_date    AS undertakerday,            -- (3)
          a.cnt_num     AS client_order_code,        -- (4)
          b.client_cat,
          a.cnt_amt     AS stotal,                   -- (5)
          NULL         AS remarks,                   -- (6)
          NULL         AS product_code,              -- (7)
          '合約儲值金額' AS product_name,             -- (8)
          1            AS body_num,                  -- (9)
          a.cnt_amt    AS body_price,                -- (10)
          a.cnt_amt    AS body_subtotal,             -- (11)
          NULL         AS body_remarks,              -- (12) placeholder
          payment_status,                            -- (13)
          b.client_catname,                          -- (14)
          1            AS sn1,
          1            AS sn2,                        -- (15)
          b.id         AS idid                       -- (16) placeholder
        "))
        ->leftJoin('BA102_37 as b','a.client_code','=','b.client_code');

        $BA203data = DB::table('BA203_61 as a')
        ->select(DB::raw("a.client_code, a.client_name,a.undertakerday,back_code as client_order_code,c.client_cat,(CASE WHEN c.yn_cnt_cust = '1' THEN 0 ELSE -a.stotal END ) as stotal,a.remarks,b.product_code, b.product_name,b.body_num,b.body_price ,(CASE WHEN c.yn_cnt_cust = '1' THEN 0 ELSE -b.body_subtotal END ) as body_subtotal,b.body_remarks,null as payment_status,c.client_catname,3 as sn1,4 as sn2,b.id as idid"))
                ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
                ->leftJoin('BA102_37 as c', 'a.client_code','=','c.client_code');

        $BA203data1 = DB::table('BA203_61 as a')
                ->leftJoin('BA203_62 as b', 'b.parent_id', '=', 'a.id')
                ->leftJoin('AA204_2224 as c', function($q) {
                    $q->on('b.combi_code', '=', 'c.combi_code')
                      ->on('b.product_code', '=', 'c.product_code');
                })
                ->leftJoin('AA204_2225 as d', 'd.parent_id', '=', 'c.id')
                ->leftJoin('BA102_37 as e', 'a.client_code', '=', 'e.client_code')
                ->select(DB::raw("
                    a.client_code,                             -- (1)
                    a.client_name,                             -- (2)
                    a.undertakerday,                           -- (3)
                    a.back_code as client_order_code,                       -- (4)
                    e.client_cat,                              -- ← 新增：第 5 欄 (對齊第一段的 c.client_cat)
                    (CASE WHEN e.yn_cnt_cust='1' THEN 0 ELSE -a.stotal END) AS stotal,  -- (6)
                    NULL AS remarks,                           -- (7)
                    d.cont_code AS product_code,               -- (8)
                    d.cont_name AS product_name,               -- (9)
                    CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) AS body_num,  -- (10)
                    NULL AS body_price,                        -- (11)
                    NULL AS body_subtotal,                     -- (12)
                    NULL AS body_remarks,                      -- (13)
                    NULL AS payment_status,                    -- (14)
                    e.client_catname,                          -- (15)
                    3 as sn1,
                    5 AS sn2,                                   -- (16)
                    b.id AS idid                               -- (17)
                "))
                ->whereNotNull('b.combi_code');

        if( !empty($filters['s_undertakerday']) ){
			$BA201data = $BA201data->where('undertakerday', '>=', $filters['s_undertakerday']);
            $BA201data1 = $BA201data1->where('undertakerday', '>=', $filters['s_undertakerday']);
            $BA104data = $BA104data->where('cnt_date', '>=', $filters['s_undertakerday']);
            $BA203data = $BA203data->where('undertakerday', '>=', $filters['s_undertakerday']);
            $BA203data1 = $BA203data1->where('undertakerday', '>=', $filters['s_undertakerday']);
		}

		if( !empty($filters['e_undertakerday']) ){
			$BA201data = $BA201data->where('undertakerday', '<=', $filters['e_undertakerday']);
            $BA201data1 = $BA201data1->where('undertakerday', '<=', $filters['e_undertakerday']);
            $BA104data = $BA104data->where('cnt_date', '<=', $filters['e_undertakerday']);
            $BA203data = $BA203data->where('undertakerday', '<=', $filters['e_undertakerday']);
            $BA203data1 = $BA203data1->where('undertakerday', '<=', $filters['e_undertakerday']);
		}

        if( !empty($filters['payment_status']) ){
            $BA201data = $BA201data->where('payment_status','=', $filters['payment_status']);
            $BA201data1 = $BA201data1->where('payment_status','=', $filters['payment_status']);
            $BA104data = $BA104data->where('payment_status','=', $filters['payment_status']);
        }
        if( !empty($filters['s_client_code']) ){
            $BA201data = $BA201data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA201data1 = $BA201data1->where('a.client_code', '>=', $filters['s_client_code']);
            $BA104data = $BA104data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA203data = $BA203data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA203data1 = $BA203data1->where('a.client_code', '>=', $filters['s_client_code']);
        }
        if( !empty($filters['e_client_code']) ){
			$BA201data = $BA201data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA201data1 = $BA201data1->where('a.client_code', '<=', $filters['e_client_code']);
            $BA104data = $BA104data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA203data = $BA203data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA203data1 = $BA203data1->where('a.client_code', '<=', $filters['e_client_code']);
        }
        if( !empty($filters['client_cat']) ){
            $array1=explode(';',$filters['client_cat']);
            $BA201data->where(function ($BA201data) use ($array1) {
                for($i=0;$i<=count($array1)-1;$i++){
                    if($array1[$i]!=""){
                        $BA201data=$BA201data->orwhere('c.client_cat','=',$array1[$i]);
                    }
                }
            });
      
            $BA201data1->where(function ($BA201data1) use ($array1) {
                for($i=0;$i<=count($array1)-1;$i++){
                    if($array1[$i]!=""){
                        $BA201data1=$BA201data1->orwhere('client_cat','=',$array1[$i]);
                    }
                }
            });
        }

        if (VerifyUtil::pageVerifyConfirmation(53)) {
			$BA201data = $BA201data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
        $BA201data=$BA201data->unionAll($BA104data)->unionAll($BA201data1)
        // ->unionAll($BA203data)->unionAll($BA203data1)
        ->orderBy('undertakerday',        'asc') 
        ->orderBy('sn1',          'asc')
        ->orderBy('client_order_code',        'asc') // 第二層：對應 PHP 裡的 $code
        ->orderBy('idid',        'asc') // 第三層：對應 PHP 裡的 $idid
        ->orderBy('sn2',          'asc') // 第四層：對應 PHP 裡的 $sn
        ->orderBy('product_code','asc') // 第五層：對應 PHP 裡的 $product_code
        ->get();
        $company_name = "孔媽媽";

        $user = User::find(SessionUtil::getUserID())->name;
        $check="";
        foreach($BA201data as $key=>$row ){
            $row->company_name = $company_name;
            $row->s_undertakerday = $filters['s_undertakerday'];
            $row->e_undertakerday = $filters['e_undertakerday'];
            $row->user = $user;
            if($check == $row->client_order_code){
                $row->stotal = null;
            }else{
                $check = $row->client_order_code;
            }
        }

        $this->datas = $BA201data;
		//dd($this->datas);

    }
}
