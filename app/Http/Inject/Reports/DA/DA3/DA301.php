<?php

namespace App\Http\Inject\Reports\DA\DA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class DA301{
    public $datas;

    public function __construct($filters){
        $this->datas = [];

        $DA202data = DB::table('DA202_56 as a')
        ->select(DB::raw("a.finished_code,b.id as no,a.undertakerday,d.batch_code,d.batch_no,e.product_code,e.product_name,f.specification as pro_cat_name,b.body_num as da_num,b.body_rate as da_rate,g.body_num as da2_body_num,g.body_rate as da2_body_rate,i.batch_code as da_batch_code,i.batch_no as da_batch_no,e.body_price as ba_price,ea.undertakerday as ba_undertakerday,ea.client_code + '-' + ea.client_name as client,(e.body_num*e.body_rate) as body_num,e.body_price,ea.transport_name,e.remarks,((h.body_price/h.body_rate)*i.component_num*i.component_rate) as ca_price"))               
        ->leftjoin ('DA202_57 as b', 'a.id', '=', 'b.parent_id')
        ->leftjoin ('DA204_2073 as c', 'b.source_batch_code', '=', 'c.id')
        ->leftjoin ('DA204_2074 as d', 'c.id', '=', 'd.parent_id')
        ->leftjoin ('BA202_53 as e', 'e.id', '=', 'd.batch_no')
        ->leftjoin ('BA202_52 as ea', 'e.parent_id', '=', 'ea.id')
        ->leftjoin ('AA202_30 as f', 'e.product_code', '=', 'f.product_code')
        ->leftjoin ('DA201_45 as g', 'g.id', '=', 'b.machining_no')
        ->leftjoin ('DA202_58 as i', 'b.id', '=', 'i.parent_id')
        ->leftjoin ('CA202_55 as h', 'h.id', '=', 'i.batch_no')
        ->whereNotNull('d.batch_code');
//        dd($DA202data->toSql());

        if( !empty($filters['s_undertakerday']) ){
            $DA202data = $DA202data->where('a.undertakerday', '>=', $filters['s_undertakerday']);
        }
        
        if( !empty($filters['e_undertakerday']) ){
            $DA202data = $DA202data->where('a.undertakerday', '<=', $filters['e_undertakerday']);
        }
        
        if( !empty($filters['s_ship_code']) ){
            $DA202data = $DA202data->where('ea.ship_code', '>=', $filters['s_ship_code']);
        }
        
        if( !empty($filters['e_ship_code']) ){
            $DA202data = $DA202data->where('ea.ship_code', '<=', $filters['e_ship_code']);
        }
                
        if( !empty($filters['s_product_code']) ){
            $DA202data = $DA202data->where('b.product_code', '>=', $filters['s_product_code']);
        }
        
        if( !empty($filters['e_product_code']) ){
            $DA202data = $DA202data->where('b.product_code', '<=', $filters['e_product_code']);
        }

        if( !empty($filters['s_finished_code']) ){
            $DA202data = $DA202data->where('a.finished_code', '>=', $filters['s_finished_code']);
        }
        
        if( !empty($filters['e_finished_code']) ){
            $DA202data = $DA202data->where('a.finished_code', '<=', $filters['e_finished_code']);
        }
        
        if (VerifyUtil::pageVerifyConfirmation(61)) {
			$DA202data = $DA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
        $result = $DA202data->orderBy('a.finished_code','ASC')
            ->orderBy('d.batch_code','ASC')->orderBy('d.batch_no','ASC')->get();
        $grouped = $result->map(function ($item, $key) {
            return $item->finished_code;
        });
//        dd($grouped);
        $grouped = array_unique($grouped->toArray());
        $sum_array = [];
        
        foreach( $grouped as $k=>$v){
            
            $loop = $result->where('finished_code','=',$v)->all();
            $temp = [];
            $num = [];
            foreach($loop as $key=>$value){
                if( !in_array($value->da_batch_no,$temp) ){
                    $temp[] = $value->da_batch_no;
                    if( array_key_exists ( $value->product_code , $num ) ){
                        $num[$value->product_code] = $num[$value->product_code] + (float)$value->ca_price;
                    }else{
                        $num[$value->product_code] = 0 + (float)$value->ca_price;
                    }
                }
                
            }
            $sum_array[$v] = $num;
        }
        $this->datas = $result;
        
        $user = User::find(SessionUtil::getUserID())->name;
        foreach($this->datas as $key=>$row ){
            if( empty($row->da_batch_no) ){
                $row->ca_price = null;
            }else{
                $row->ca_price = $sum_array[$row->finished_code][$row->product_code];
            }
            $row->s_undertakerday = $filters['s_undertakerday'];
            $row->e_undertakerday = $filters['e_undertakerday'];
            $row->s_ship_code = $filters['s_ship_code'];
            $row->e_ship_code = $filters['e_ship_code'];
            $row->s_product_code = $filters['s_product_code'];
            $row->e_product_code = $filters['e_product_code'];
            $row->s_finished_code = $filters['s_finished_code'];
            $row->e_finished_code = $filters['e_finished_code'];
            $row->user = $user;
        }
    }
}
