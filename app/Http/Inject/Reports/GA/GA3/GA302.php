<?php

namespace App\Http\Inject\Reports\GA\GA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class GA302{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
        $BA202data = DB::table('BA202_53 as a')
                ->leftJoin('BA202_52 as b', 'a.parent_id','=','b.id')
                ->selectRaw("a.product_name as name,(case when (b_tax = '稅外加') then body_subtotal * (1+CONVERT(float,b_taxrate)) else body_subtotal end) as body_subtotal,a.b_pmt_date as date");
        $CA202data = DB::table('CA202_55 as a')
                ->leftJoin('CA202_54 as b', 'a.parent_id','=','b.id')
                ->selectRaw("a.product_name as name,(case when (tax = '稅外加') then ( -body_subtotal * (1+CONVERT(float,taxrate))) else ( -body_subtotal) end) as body_subtotal,a.b_pmt_date as date");
        $GA201data = DB::table('GA201_3224 as a')
                ->select(DB::raw("a.exp_item as name,(- a.body_subtotal) as body_subtotal,a.exp_date as date"));
        if( !empty($filters['s_undertakerday']) ){
            $BA202data = $BA202data->where('b_pmt_date', '>=', $filters['s_undertakerday']);
            $CA202data = $CA202data->where('b_pmt_date', '>=', $filters['s_undertakerday']);
            $GA201data = $GA201data->where('exp_date', '>=', $filters['s_undertakerday']);
		}

		if( !empty($filters['e_undertakerday']) ){
            $BA202data = $BA202data->where('b_pmt_date', '<=', $filters['e_undertakerday']);
            $CA202data = $CA202data->where('b_pmt_date', '<=', $filters['e_undertakerday']);
            $GA201data = $GA201data->where('exp_date', '<=', $filters['e_undertakerday']);
		}


        $save1=$BA202data
            ->unionAll($CA202data)
            ->unionAll($GA201data)
            ->orderby('date')
            ->get();

        $user = User::find(SessionUtil::getUserID())->name;

        $this->datas = $save1;
        $plus=0;
        $sub=0;
        foreach($this->datas as $key=>$row ){
            $row->s_undertakerday = $filters['s_undertakerday'];
            $row->e_undertakerday = $filters['e_undertakerday'];
            $row->user = $user;
            if($row->body_subtotal > 0){
                $plus=$plus+$row->body_subtotal;
            }else{
                $sub=$sub+$row->body_subtotal;
            }
            $row->plus=$plus;
            $row->sub=$sub;
        }
    }
}
