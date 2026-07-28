<?php

namespace App\Http\Inject\Reports\GA\GA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class GA301{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
        $BA202data = DB::table('BA202_52 as a')
                ->select('a.ship_date','a.stotal');
        $CA202data = DB::table('BA202_53 as a')
                ->select('b.ship_date','a.cost')
                ->leftJoin('BA202_52 as b', 'a.parent_id','=','b.id');
        $GA201data = DB::table('GA201_3224 as a')
                ->select('a.exp_date','a.body_subtotal')
                ->leftJoin('GA201_3223 as b', 'a.parent_id','=','b.id');;
        if( !empty($filters['date_s']) ){
			$BA202data = $BA202data->where('ship_date', '>=', $filters['date_s']);
            $CA202data = $CA202data->where('ship_date', '>=', $filters['date_s']);
            $GA201data = $GA201data->where('exp_date', '>=', $filters['date_s']);
		}

		if( !empty($filters['date_e']) ){
			$BA202data = $BA202data->where('ship_date', '<=', $filters['date_e']);
            $CA202data = $CA202data->where('ship_date', '<=', $filters['date_e']);
            $GA201data = $GA201data->where('exp_date', '<=', $filters['date_e']);
		}

        if (VerifyUtil::pageVerifyConfirmation(59)) {
			$BA202data = $BA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
        if (VerifyUtil::pageVerifyConfirmation(60)) {
			$CA202data = $CA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
        if (VerifyUtil::pageVerifyConfirmation(3247)) {
			$GA201data = $GA201data->where("b.data_options", "LIKE", '%"verify":{%"level":255%');
		}
        $ba=$BA202data->sum('stotal');
        $ca=$CA202data->sum('cost');
        $ga=$GA201data->sum('body_subtotal');
        $gp=round($ba-$ca,2);
        $np=round($ba-$ca-$ga,2);
        // dd($this->datas);
        $user = User::find(SessionUtil::getUserID())->name;
        // $this->datas=["BA"=>$a]
        $this->datas=array("BA"=>$ba,"CA"=>$ca,"GA"=>$ga,"date_s"=>$filters['date_s'],"date_e"=>$filters['date_e'],"user"=>$user,'GP'=>$gp,'NP'=>$np);
		// dd($this->datas);

    }
}
