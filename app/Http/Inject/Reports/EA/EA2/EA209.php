<?php

namespace App\Http\Inject\Reports\EA\EA2;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class EA209{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
        //dd($filters);
        $EA204data = DB::table('EA204_79 as a')
                ->leftjoin('AA202_30 as b', 'b.product_code','=','a.product_code')
                ->select('a.depot_code', 'a.depot_name', 'a.product_code' ,'a.product_name','a.num','a.unit_name','b.pro_cat_code','b.pro_cat_name');
        // dd($EA204data->get());
        if( !empty($filters['depot_code_s']) ){
			$EA204data = $EA204data->where('a.depot_code', '>=', $filters['depot_code_s']);
		}

		if( !empty($filters['depot_code_e']) ){
			$EA204data = $EA204data->where('a.depot_code', '<=', $filters['depot_code_e']);
		}

		if( !empty($filters['product_code_s']) ){
			$EA204data = $EA204data->where('a.product_code', '>=', $filters['product_code_s']);
		}

		if( !empty($filters['product_code_e']) ){
			$EA204data = $EA204data->where('a.product_code', '<=', $filters['product_code_e']);
		}
        if(!empty($filters['pro_cat_code']) ){
           // dd($filters['pro_cat_code']);
         $proCatCodes = explode(';', rtrim($filters['pro_cat_code'], ';'));

        // 使用 whereIn 查詢 pro_cat_code 在陣列中的資料
        $EA204data = $EA204data->whereIn('pro_cat_code', $proCatCodes);
        }
        $this->datas = $EA204data->orderBy('depot_code')->orderBy('product_code')->get();
        $user = User::find(SessionUtil::getUserID())->name;
        foreach($this->datas as $key=>$row ){
            $row->depot_code_s = $filters['depot_code_s'];
            $row->depot_code_e = $filters['depot_code_e'];
            $row->product_code_s = $filters['product_code_s'];
            $row->product_code_e = $filters['product_code_e'];


             $row->user = $user;
        }
	//	 dd($this->datas);

    }
}
