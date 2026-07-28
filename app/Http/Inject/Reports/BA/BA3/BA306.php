<?php

namespace App\Http\Inject\Reports\BA\BA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class BA306{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
        // $BA202data = DB::table('BA202_52 as a')
        // ->select(DB::raw("a.client_code,a.client_name, a.undertakerday,CONVERT(char(10), a.ship_date, 120) as ship_date,a.ship_code,a.amt_discount,b.b_tax as taxname,a.ssubtotal,a.stax,a.stotal,a.ototal,b.product_code, b.product_name ,b.body_num,b.unit_name, b.body_price ,b.body_subtotal ,b.remarks,b.payment_status,c.uniform_num,a.final_pmt as amt_unrecd,2 as sn"))
        // ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
        // ->leftJoin('BA102_37 as c', 'c.client_code','=','a.client_code')
        // ->where('yn_cnt_cust', '=', 0);

        // $BA203data = DB::table('BA203_61 as a')
        // ->select(DB::raw("a.client_code,a.client_name, a.undertakerday,('退回項目\n'+CONVERT(char(10), a.back_day, 120)) as ship_date,a.back_code as ship_code,null as amt_discount,a.tax as taxname, - a.ssubtotal as ssubtotal,- a.stax as stax,- a.stotal as stotal, - a.ototal as ototal,b.product_code, b.product_name,b.body_num ,b.unit_name,body_price ,- b.body_subtotal as body_subtotal,b.body_remarks as remarks,null as payment_status ,c.uniform_num,NULL as amt_unrecd,3 as sn"))
        // ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
        // ->leftJoin('BA102_37 as c', 'c.client_code','=','a.client_code')
        // ->where('yn_cnt_cust', '=', 0);

        $BA211data = DB::table('BA211_6263 as a')
        ->select(DB::raw("a.client_code,a.client_name, a.undertakerday,CONVERT(char(10), a.ship_date, 120) as ship_date,a.ship_code,null as amt_discount,b.b_tax as taxname,a.ssubtotal,a.stax,a.stotal,a.ototal,b.product_code, b.product_name ,b.body_num,b.unit_name,  b.body_price ,
        CAST(
            
            (CASE
        WHEN b.b_tax = '稅外加'
           THEN b.body_subtotal * ( 1 +  CAST(('0' + b.b_taxrate) AS float) )
        ELSE b.body_subtotal
   END)
    
    AS decimal(10,2)) as body_subtotal
        ,a.remarks as h_remarks,b.remarks as remarks,b.payment_status,c.uniform_num, a.final_pmt  as amt_unrecd,4 as sn"))
        ->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id')
        ->leftJoin('BA102_37 as c', 'c.client_code','=','a.client_code');


        //dd($BA202data,$BA203data);
        if( !empty($filters['s_undertakerday']) ){
			// $BA202data = $BA202data->where('ship_date', '>=', $filters['s_undertakerday']);
            // $BA203data = $BA203data->where('back_day', '>=', $filters['s_undertakerday']);
            $BA211data = $BA211data->where('ship_date', '>=', $filters['s_undertakerday']);
		}

		if( !empty($filters['e_undertakerday']) ){
			// $BA202data = $BA202data->where('ship_date', '<=', $filters['e_undertakerday']);
            // $BA203data = $BA203data->where('back_day', '<=', $filters['e_undertakerday']);
            $BA211data = $BA211data->where('ship_date', '<=', $filters['e_undertakerday']);
		}

		if( !empty($filters['s_client_code']) ){
			// $BA202data = $BA202data->where('a.client_code', '>=', $filters['s_client_code']);
            // $BA203data = $BA203data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA211data = $BA211data->where('a.client_code', '>=', $filters['s_client_code']);
		}

		if( !empty($filters['e_client_code']) ){
			// $BA202data = $BA202data->where('a.client_code', '<=', $filters['e_client_code']);
            // $BA203data = $BA203data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA211data = $BA211data->where('a.client_code', '<=', $filters['e_client_code']);
		}

		if( !empty($filters['payment_status']) ){
            // $BA202data = $BA202data->where('payment_status','=', $filters['payment_status']);
            $BA211data = $BA211data->where('payment_status','=', $filters['payment_status']);
        }



        // if (VerifyUtil::pageVerifyConfirmation(59)) {
		// 	$BA202data = $BA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		// }

        $BA202data=$BA211data
        // ->unionAll($BA202data)
        // ->unionAll($BA203data)
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
