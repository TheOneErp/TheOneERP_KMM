<?php

namespace App\Http\Inject\Reports\CA\CA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class CA301{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
        //dd($filters);
        $CA202data = DB::table('CA202_54 as a')
                ->select(DB::raw("a.vendor_code,a.vendor_name,a.receive_code,a.receive_day,b.product_code,b.product_name,b.body_num,b.unit_name,b.body_price,CAST( CASE
                WHEN tax = '稅外加'
                   THEN b.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) )
                ELSE b.body_subtotal
           END AS decimal(10,2)) as body_subtotal,b.body_remarks,b.pay_status"))
                // ->select('a.vendor_code','a.vendor_name','a.receive_code','a.receive_day','b.product_code','b.product_name','b.body_num','b.unit_name','b.body_price','b.body_subtotal','b.body_remarks','b.pay_status')
                ->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id');
// dd($CA202data->get());
        $CA202_receivecode = DB::table('CA202_54 as a')
        ->select('a.vendor_code','a.vendor_name','a.receive_code','a.receive_day','b.product_code','b.product_name','b.body_num','b.unit_name','b.body_price','b.body_subtotal','b.body_remarks','b.pay_status')
        ->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id');

        $CA203data = DB::table('CA203_63 as a')
        ->select(DB::raw("a.abort_code,b.body_remarks,b.receive_code as receive_code2,a.vendor_code as vendor_code, a.vendor_name as vendor_name, a.undertakerday as undertakerday, a.abort_day,b.product_code as product_code, b.product_name as product_name ,b.body_num as body_num2,b.unit_name as unit_name2, b.body_price as body_price2 ,CAST( CASE
        WHEN tax = '稅外加'
           THEN b.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) )
        ELSE b.body_subtotal
   END AS decimal(10,2))  as body_subtotal2 ,b.source_receive_code"))
        // ->select('a.abort_code','b.body_remarks','b.receive_code as receive_code2','a.vendor_code as vendor_code', 'a.vendor_name as vendor_name', 'a.undertakerday as undertakerday', 'a.abort_day','b.product_code as product_code', 'b.product_name as product_name' ,'b.body_num as body_num2','b.unit_name as unit_name2', 'b.body_price as body_price2' ,'b.body_subtotal as body_subtotal2' ,'b.source_receive_code')
        ->leftJoin('CA203_64 as b', 'b.parent_id','=','a.id');



        if( !empty($filters['s_receive_day']) ){
			$CA202data = $CA202data->where('receive_day', '>=', $filters['s_receive_day']);
            $CA202_receivecode = $CA202_receivecode->where('receive_day', '>=', $filters['s_receive_day']);
		}

		if( !empty($filters['e_receive_day']) ){
			$CA202data = $CA202data->where('receive_day', '<=', $filters['e_receive_day']);
            $CA202_receivecode = $CA202_receivecode->where('receive_day', '<=', $filters['e_receive_day']);
		}

		if( !empty($filters['s_vendor_code']) ){
			$CA202data = $CA202data->where('vendor_code', '>=', $filters['s_vendor_code']);
            $CA202_receivecode = $CA202_receivecode->where('vendor_code', '>=', $filters['s_vendor_code']);
		}

		if( !empty($filters['e_vendor_code']) ){
			$CA202data = $CA202data->where('vendor_code', '<=', $filters['e_vendor_code']);
            $CA202_receivecode = $CA202_receivecode->where('vendor_code', '<=', $filters['e_vendor_code']);
		}

		if( !empty($filters['s_product_code']) ){
			$CA202data = $CA202data->where('product_code', '>=', $filters['s_product_code']);
            $CA202_receivecode = $CA202_receivecode->where('product_code', '>=', $filters['s_product_code']);
            $CA203data = $CA203data->where('product_code', '>=', $filters['s_product_code']);
		}

		if( !empty($filters['e_product_code']) ){
			$CA202data = $CA202data->where('product_code', '<=', $filters['e_product_code']);
            $CA202_receivecode = $CA202_receivecode->where('product_code', '<=', $filters['e_product_code']);
            $CA203data = $CA203data->where('product_code', '<=', $filters['e_product_code']);
		}
        if( !empty($filters['pay_status']) ){
			$CA202data = $CA202data->where('pay_status', '=', $filters['pay_status']);
            $CA202_receivecode = $CA202_receivecode->where('pay_status', '=', $filters['pay_status']);
		}
        if (VerifyUtil::pageVerifyConfirmation(60)) {
			$CA202data = $CA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
        $CA202_receivecode = $CA202_receivecode
        ->select('a.receive_code')
        ->groupby('receive_code')
        ->pluck('receive_code')
        ->toarray();

        $CA202data = $CA202data->orderBy('vendor_code')->get()->toarray();
        $CA203data = $CA203data->wherein("receive_code",$CA202_receivecode)->get()->toarray();
        foreach($CA203data as $key=>$row ){
            $row->receive_day = null;
        }

        //dd($CA202_receivecode,$CA203data);

        for($i=0;$i<=count($CA203data)-1;$i++){
            array_push($CA202data,$CA203data[$i]);
        }
        foreach ($CA202data as $key => $row) {
            if($row->receive_day){
                $row->datas=$row->receive_day;
            }else{
                $row->datas=$row->abort_day;
            }

        }

        //dd($BA202data);
        $vendor_code = array_column($CA202data, 'vendor_code');
        $product_code  = array_column($CA202data, 'product_code');
        $receive_date = array_column($CA202data, 'receive_date');
        $datas = array_column($CA202data, 'datas');
        foreach ($datas as $key => $part) {

            $sort[$key] = strtotime($part);
       }

       if($CA202data != null){
        array_multisort($vendor_code, SORT_ASC, $sort, SORT_ASC, $CA202data);
        }



        $this->datas = $CA202data;
        $user = User::find(SessionUtil::getUserID())->name;
        foreach($this->datas as $key=>$row ){
            $row->s_receive_day = $filters['s_receive_day'];
            $row->e_receive_day = $filters['e_receive_day'];
            $row->s_vendor_code = $filters['s_vendor_code'];
            $row->e_vendor_code = $filters['e_vendor_code'];
            $row->s_product_code = $filters['s_product_code'];
            $row->e_product_code = $filters['e_product_code'];

            $row->user = $user;
        }
		//dd($this->datas);

    }
}
