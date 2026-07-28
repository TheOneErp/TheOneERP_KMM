<?php

namespace App\Http\Inject\Reports\BA\BA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class BA302{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
        $BA202data = DB::table('BA202_52 as a')
                ->select('a.client_code', 'a.client_name','b.product_code', 'b.product_name' ,'b.body_subtotal','b.cost','b.body_num')
                ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id');

        if( !empty($filters['ship_date_s']) ){
			$BA202data = $BA202data->where('ship_date', '>=', $filters['ship_date_s']);
		}

		if( !empty($filters['ship_date_e']) ){
			$BA202data = $BA202data->where('ship_date', '<=', $filters['ship_date_e']);
		}

		if( !empty($filters['s_client_code']) ){
			$BA202data = $BA202data->where('client_code', '>=', $filters['s_client_code']);
		}

		if( !empty($filters['e_client_code']) ){
			$BA202data = $BA202data->where('client_code', '<=', $filters['e_client_code']);
		}

		if( !empty($filters['s_product_code']) ){
			$BA202data = $BA202data->where('product_code', '>=', $filters['s_product_code']);
        }

        if( !empty($filters['e_product_code']) ){
			$BA202data = $BA202data->where('product_code', '<=', $filters['e_product_code']);
		}

        if (VerifyUtil::pageVerifyConfirmation(59)) {
			$BA202data = $BA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
        $bindings = $BA202data->getBindings();

        $sql = str_replace('?', "'%s'", $BA202data->toSql());

        $sql = sprintf($sql, ...$bindings);

        $save1=DB::table(DB::raw("($sql) as save1"))
        ->selectraw('client_code,client_name,product_code,product_name,sum(body_subtotal) as sumprice,sum(cost*body_num) as sumcost')
        ->groupby('client_code','client_name','product_code','product_name')
        ->orderby('client_code','asc')
        ->orderby('product_code','asc')
        ->get();
        $company_name = "孔媽媽";

        $user = User::find(SessionUtil::getUserID())->name;
        foreach($save1 as $key=>$row ){
            $row->company_name = $company_name;
            $row->ship_date_s = $filters['ship_date_s'];
            $row->ship_date_e = $filters['ship_date_e'];
            $row->s_client_code = $filters['s_client_code'];
            $row->e_client_code = $filters['e_client_code'];
            $row->s_product_code = $filters['s_product_code'];
            $row->e_product_code = $filters['e_product_code'];
            $row->user = $user;
            if($row->sumcost==null){
                $row->sumcost=0;
            }
        }

        $this->datas = $save1;
		//dd($this->datas);

    }
}
