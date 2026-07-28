<?php

namespace App\Http\Inject\Reports\BA\BA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class BA304{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
$BA202data = DB::table('BA202_52 as a')
    ->select(DB::raw("
        a.client_code,
        a.client_name, 
        a.undertakerday,
        CONVERT(char(10), a.ship_date, 120) as ship_date,
        a.ship_code,
        a.amt_discount,
        b.b_tax as taxname,
        a.ssubtotal,
        a.stax,
        a.stotal,
        
        -- 【新增】依照 payment_status 決定 amt_outstanding
        (CASE 
            WHEN b.payment_status = '未收款' THEN b.body_subtotal 
            WHEN b.payment_status = '已收款' THEN 0 
            ELSE a.amt_outstanding   
        END) as amt_outstanding,
        
        a.ototal,
        b.product_code, 
        b.product_name,
        b.body_num,
        b.unit_name, 
        b.body_price, 
        b.body_subtotal,
        b.remarks,
        b.payment_status,
        c.uniform_num,
        a.final_pmt as amt_unrecd,
        2 as sn
    "))
    ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
    ->leftJoin('BA102_37 as c', 'c.client_code','=','a.client_code')
    ->where('yn_cnt_cust', '=', 0);
         $BA203data = DB::table('BA203_61 as a')
         ->select(DB::raw("a.client_code,a.client_name, a.undertakerday,('退回項目\n'+CONVERT(char(10), a.back_day, 120)) as ship_date,a.back_code as ship_code,null as amt_discount,a.tax as taxname, - a.ssubtotal as ssubtotal,- a.stax as stax,- a.stotal ,0 as amt_outstanding, - a.ototal as ototal,b.product_code, b.product_name,b.body_num ,b.unit_name,body_price ,- b.body_subtotal as body_subtotal,b.body_remarks as remarks,null as payment_status ,c.uniform_num,NULL as amt_unrecd,3 as sn"))
         ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
         ->leftJoin('BA102_37 as c', 'c.client_code','=','a.client_code')
         ->where('yn_cnt_cust', '=', 0);

     //   $BA201data = DB::table('BA201_40 as a')
   //     ->select(DB::raw("a.client_code,a.client_name, a.undertakerday,CONVERT(char(10), a.undertakerday, 120) as ship_date,a.client_order_code as ship_code,null as amt_discount,a.tax as taxname,a.ssubtotal,a.stax,(CASE WHEN a.yn_n_sales = 1 THEN 0 ELSE a.stotal END )as stotal,a.ototal,b.product_code, b.product_name ,b.body_num,b.unit_name,  b.body_price ,
    //    CAST(
   //         (CASE WHEN a.yn_n_sales = 1 THEN 0 ELSE
    //        (CASE
    //    WHEN a.tax = '稅外加'
    //       THEN b.body_subtotal * ( 1 +  CAST(('0' + a.taxrate) AS float) )
   //     ELSE b.body_subtotal
  // END)
   // END )
   // AS decimal(10,2)) as body_subtotal
     //   ,a.remarks as h_remarks,b.body_remarks as remarks,b.payment_status,c.uniform_num, a.final_pmt  as amt_unrecd,4 as sn"))
    //   ->leftJoin('BA201_41 as b', 'b.parent_id','=','a.id')
    //    ->leftJoin('BA102_37 as c', 'c.client_code','=','a.client_code');
        //客戶說他們還有更動之前的未收還沒收到，所以開放給他們查詢，之後再把下一行補回來
        // ->where('c.yn_cnt_cust', '=', 1);

      //  $BA104data = DB::table("BA104_6241 as a")
      //  ->select(DB::raw("a.client_code,a.client_name,a.cnt_date as undertakerday,CONVERT(char(10), a.cnt_date, 120) as  ship_date,a.cnt_num as ship_code,null as amt_discount,null as taxname,null as ssubtotal,null as stax,a.cnt_amt as stotal,a.cnt_amt as ototal,null as product_code,'合約儲值金額' as product_name,1 as body_num,'' as unit_name,a.cnt_amt as body_price,null as body_subtotal,null as h_remarks,null as remarks,payment_status,b.uniform_num,(cnt_amt-amt_recd) as amt_unrecd,1 as sn"))
      //  ->leftJoin('BA102_37 as b', 'a.client_code','=','b.client_code');

        //dd($BA202data,$BA203data);
        if( !empty($filters['s_undertakerday']) ){
			 $BA202data = $BA202data->where('ship_date', '>=', $filters['s_undertakerday']);
             $BA203data = $BA203data->where('back_day', '>=', $filters['s_undertakerday']);
          //  $BA201data = $BA201data->where('undertakerday', '>=', $filters['s_undertakerday']);
          //  $BA104data = $BA104data->where('cnt_date', '>=', $filters['s_undertakerday']);
		}

		if( !empty($filters['e_undertakerday']) ){
			$BA202data = $BA202data->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA203data = $BA203data->where('back_day', '<=', $filters['e_undertakerday']);
        //$BA201data = $BA201data->where('undertakerday', '<=', $filters['e_undertakerday']);
          //  $BA104data = $BA104data->where('cnt_date', '<=', $filters['e_undertakerday']);
		}

		if( !empty($filters['s_client_code']) ){
			 $BA202data = $BA202data->where('a.client_code', '>=', $filters['s_client_code']);
             $BA203data = $BA203data->where('a.client_code', '>=', $filters['s_client_code']);
           // $BA201data = $BA201data->where('a.client_code', '>=', $filters['s_client_code']);
            //$BA104data = $BA104data->where('a.client_code', '>=', $filters['s_client_code']);
		}

		if( !empty($filters['e_client_code']) ){
			 $BA202data = $BA202data->where('a.client_code', '<=', $filters['e_client_code']);
             $BA203data = $BA203data->where('a.client_code', '<=', $filters['e_client_code']);
           // $BA201data = $BA201data->where('a.client_code', '<=', $filters['e_client_code']);
           // $BA104data = $BA104data->where('a.client_code', '<=', $filters['e_client_code']);
		}

		if( !empty($filters['payment_status']) ){
             $BA202data = $BA202data->where('payment_status','=', $filters['payment_status']);
          //  $BA201data = $BA201data->where('payment_status','=', $filters['payment_status']);
          //  $BA104data = $BA104data->where('payment_status', '=', $filters['payment_status']);
        }



        // if (VerifyUtil::pageVerifyConfirmation(59)) {
		// 	$BA202data = $BA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		// }

        $BA202data=$BA202data
        // ->unionAll($BA202data)
        // ->unionAll($BA203data)
       // ->unionAll($BA104data)
        ->orderby('undertakerday')
        ->orderby('client_name')
        ->orderby('sn')
        ->orderby('ship_code')
        ->get();



        $this->datas = $BA202data;
        $user = User::find(SessionUtil::getUserID())->name;
        $check="";
        foreach($this->datas as $key=>$row ){
            $row->s_undertakerday = $filters['s_undertakerday'];
            $row->e_undertakerday = $filters['e_undertakerday'];
            $row->s_client_code = $filters['s_client_code'];
            $row->e_client_code = $filters['e_client_code'];



            $row->user = $user;
            if($check == $row->ship_code){
                $row->stotal = null;
                $row->amt_unrecd = null;
            }else{
                $check = $row->ship_code;
            }
        }
		// dd($this->datas);

    }
}
