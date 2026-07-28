<?php

namespace App\Http\Inject\Reports\BA\BA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

use Illuminate\Support\Facades\DB;

class BA303{
    public $datas;

    public function __construct($filters){
        $this->datas = [];
        $type="";

        $BA202data = DB::table('BA202_52 as a')
        ->select(DB::raw("b.product_code,CAST(b.body_num*b.body_rate AS decimal(10,2)) as num"))
        ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id');

        $BA202data1 = DB::table('BA202_52 as a')
        ->select(DB::raw("d.cont_code as product_code,CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as num"))
        ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
        ->leftJoin('AA204_2224 as c', function($q) use ($type)
        {
            $q->on('b.combi_code', '=', 'c.combi_code')
            ->on('b.product_code', '=', 'c.product_code');
        })
        ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
        ->whereNotNull('b.combi_code');

        $BA203data = DB::table('BA203_61 as a')
        ->select(DB::raw("b.product_code,CAST(-b.body_num*b.body_rate AS decimal(10,2)) as num"))
        ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id') ;

        $BA203data1 = DB::table('BA203_61 as a')
        ->select(DB::raw("d.cont_code as product_code,CAST(- b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as num"))
        ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
        ->leftJoin('AA204_2224 as c', function($q) use ($type)
        {
            $q->on('b.combi_code', '=', 'c.combi_code')
            ->on('b.product_code', '=', 'c.product_code');
        })
        ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
        ->whereNotNull('b.combi_code');

        $BA211data = DB::table('BA211_6263 as a')
        ->select(DB::raw("b.product_code,CAST(b.body_num*b.body_rate AS decimal(10,2)) as num"))
        ->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id');

        $BA211data1 = DB::table('BA211_6263 as a')
        ->select(DB::raw("d.cont_code as product_code,CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as num"))
        ->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id')
        ->leftJoin('AA204_2224 as c', function($q) use ($type)
        {
            $q->on('b.combi_code', '=', 'c.combi_code')
            ->on('b.product_code', '=', 'c.product_code');
        })
        ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
        ->whereNotNull('b.combi_code');

        if( !empty($filters['s_undertakerday']) ){
            $BA202data = $BA202data->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA202data1 = $BA202data1->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA203data = $BA203data->where('back_day', '>=', $filters['s_undertakerday']);
            $BA203data1 = $BA203data1->where('back_day', '>=', $filters['s_undertakerday']);
            $BA211data = $BA211data->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA211data1 = $BA211data1->where('ship_date', '>=', $filters['s_undertakerday']);
        }
        if( !empty($filters['e_undertakerday']) ){
            $BA202data = $BA202data->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA202data1 = $BA202data1->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA203data = $BA203data->where('back_day', '<=', $filters['e_undertakerday']);
            $BA203data1 = $BA203data1->where('back_day', '<=', $filters['e_undertakerday']);
            $BA211data = $BA211data->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA211data1 = $BA211data1->where('ship_date', '<=', $filters['e_undertakerday']);
        }
        if( !empty($filters['gift_options']) ){
            if($filters['gift_options'] == '商品'){
                $BA202data->where(function ($BA202data) use ($filters) {
                    $BA202data=$BA202data->orwhere('gift_options','=',null);
                    $BA202data=$BA202data->orwhere('gift_options','=','');
                });
                $BA202data1->where(function ($BA202data1) use ($filters) {
                    $BA202data1=$BA202data1->orwhere('gift_options','=',null);
                    $BA202data1=$BA202data1->orwhere('gift_options','=','');
                });
                $BA203data->where(function ($BA203data) use ($filters) {
                    $BA203data=$BA203data->orwhere('gift_options','=',null);
                    $BA203data=$BA203data->orwhere('gift_options','=','');
                });
                $BA203data1->where(function ($BA203data1) use ($filters) {
                    $BABA203data1203data=$BA203data1->orwhere('gift_options','=',null);
                    $BA203data1=$BA203data1->orwhere('gift_options','=','');
                });
                $BA211data->where(function ($BA211data) use ($filters) {
                    $BA211data=$BA211data->orwhere('gift_options','=',null);
                    $BA211data=$BA211data->orwhere('gift_options','=','');
                });
                $BA211data1->where(function ($BA211data1) use ($filters) {
                    $BA211data1=$BA211data1->orwhere('gift_options','=',null);
                    $BA211data1=$BA211data1->orwhere('gift_options','=','');
                });
            }else{
                $BA202data = $BA202data->where('gift_options', '=', $filters['gift_options']);
                $BA202data1 = $BA202data1->where('gift_options', '=', $filters['gift_options']);
                $BA203data = $BA203data->where('gift_options', '=', $filters['gift_options']);
                $BA203data1 = $BA203data1->where('gift_options', '=', $filters['gift_options']);
                $BA211data = $BA211data->where('gift_options', '=', $filters['gift_options']);
                $BA211data1 = $BA211data1->where('gift_options', '=', $filters['gift_options']);
            }
        }

        $save = $BA202data->unionAll($BA202data1)->unionAll($BA203data)->unionAll($BA203data1)->unionAll($BA211data)->unionAll($BA211data1);

        $bindings = $save->getBindings();
        $sql = str_replace('?', "'%s'", $save->toSql());
        $sql = sprintf($sql, ...$bindings);

        $save1 = DB::table('AA202_30 as a')
        ->leftjoin(DB::raw("($sql) as g"),'g.product_code','=','a.product_code')
        ->select(DB::raw("DENSE_RANK()Over (order by sum(num) desc) as RK,a.product_code,a.product_name,sum(num) as num,a.unit_name"))
        ->havingRaw("sum(num) > 0")
        ->groupbyraw("a.product_code,a.product_name,a.unit_name")
        ->get();

        // $BA202data = $BA202data->select(DB::raw("b.product_code,sum(convert(decimal(18,2),ISNULL(b.body_num,0)*body_rate)) as num,"))
        //     ->groupby("b.product_code");

        // $bindings = $BA202data->getBindings();
        // $sql = str_replace('?', "'%s'", $BA202data->toSql());
        // $sql = sprintf($sql, ...$bindings);

        // $BA203data = $BA203data->select(DB::raw("d.product_code,sum(convert(decimal(18,2),ISNULL(d.body_num,0)*body_rate)) as num1"))
        //     ->groupby("d.product_code");

        // $bindings1 = $BA203data->getBindings();
        // $sql1 = str_replace('?', "'%s'", $BA203data->toSql());
        // $sql1 = sprintf($sql1, ...$bindings1);

        // $save1 = $AA202data
        //     ->leftjoin(DB::raw("($sql) as g"),'g.product_code','=','a.product_code')
        //     ->leftjoin(DB::raw("($sql1) as f"),'f.product_code','=','a.product_code')
        //     ->select(DB::raw("DENSE_RANK()Over (order by sum(ISNULL(g.num,0))-isnull(f.num1,0) desc) as RK ,a.product_code,a.product_name,sum(ISNULL(g.num,0))-isnull(f.num1,0) as num,sum(ISNULL(g.total,0))-isnull(f.total1,0) as total,a.unit_name,(a.sell_price - a.purchase_price)  as profit,num + total"))
        //     ->havingRaw("sum(ISNULL(g.num,0))-isnull(f.num1,0)+sum(ISNULL(g.total,0))-isnull(f.total1,0)  > 0")
        //     ->groupbyraw("a.product_code,a.product_name,a.unit_name,a.sell_price,a.purchase_price,g.num,f.num1,g.total,f.total1")
        //     ->get();

        // if( empty($filters['s_undertakerday']) && empty($filters['e_undertakerday']) ){
        //     if($filters['yn_price']=="否"){
        //         $BA202data=$BA202data
        //         ->leftJoin(DB::raw("(select b.product_code,sum(convert(decimal(18,2),ISNULL(b.body_num,0)*body_rate)) as num,sum(CAST( CASE WHEN b_tax = '稅外加' THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) ) ELSE b.body_subtotal END AS decimal(10,2))) as total from BA202_53 as b left join BA202_52 as c on b.parent_id = c.id where b.body_price != '0' group by b.product_code) as g "), 'g.product_code','=','a.product_code')
        //         ->leftJoin(DB::raw("(select d.product_code,sum(convert(decimal(18,2),ISNULL(d.body_num,0)*body_rate)) as num1,sum(CAST( CASE WHEN tax = '稅外加' THEN d.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) ) ELSE d.body_subtotal END AS decimal(10,2))) as total1 from BA203_62 as d left join BA203_61 as e on d.parent_id = e.id where d.body_price != 0.00 group by d.product_code) as f "), 'f.product_code','=','a.product_code');
        //     }else{
        //         $BA202data=$BA202data
        //         ->leftJoin(DB::raw("(select b.product_code,sum(convert(decimal(18,2),ISNULL(b.body_num,0)*body_rate)) as num,sum(CAST( CASE WHEN b_tax = '稅外加' THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) ) ELSE b.body_subtotal END AS decimal(10,2))) as total from BA202_53 as b left join BA202_52 as c on b.parent_id = c.id where b.body_price = '0' group by b.product_code) as g "), 'g.product_code','=','a.product_code')
        //         ->leftJoin(DB::raw("(select d.product_code,sum(convert(decimal(18,2),ISNULL(d.body_num,0)*body_rate)) as num1,sum(CAST( CASE WHEN tax = '稅外加' THEN d.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) ) ELSE d.body_subtotal END AS decimal(10,2))) as total1 from BA203_62 as d left join BA203_61 as e on d.parent_id = e.id where d.body_price = 0.00 group by d.product_code) as f "), 'f.product_code','=','a.product_code');
        //     }

        // }else if(!empty($filters['s_undertakerday']) && empty($filters['e_undertakerday'])){
        //     if($filters['yn_price']=="否"){
        //         $BA202data=$BA202data
        //         ->leftJoin(DB::raw("(select b.product_code,sum(convert(decimal(18,2),ISNULL(b.body_num,0)*body_rate)) as num,sum(CAST( CASE WHEN b_tax = '稅外加' THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) ) ELSE b.body_subtotal END AS decimal(10,2))) as total from BA202_53 as b left join BA202_52 as c on b.parent_id = c.id where c.ship_date >= '".$filters['s_undertakerday']."' and b.body_price != '0' group by b.product_code) as g "), 'g.product_code','=','a.product_code')
        //         ->leftJoin(DB::raw("(select d.product_code,sum(convert(decimal(18,2),ISNULL(d.body_num,0)*body_rate)) as num1,sum(CAST( CASE WHEN tax = '稅外加' THEN d.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) ) ELSE d.body_subtotal END AS decimal(10,2))) as total1 from BA203_62 as d left join BA203_61 as e on d.parent_id = e.id where e.back_day >= '".$filters['s_undertakerday']."' and d.body_price != 0.00 group by d.product_code) as f "), 'f.product_code','=','a.product_code');
        //     }else{
        //         $BA202data=$BA202data
        //         ->leftJoin(DB::raw("(select b.product_code,sum(convert(decimal(18,2),ISNULL(b.body_num,0)*body_rate)) as num,sum(CAST( CASE WHEN b_tax = '稅外加' THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) ) ELSE b.body_subtotal END AS decimal(10,2))) as total from BA202_53 as b left join BA202_52 as c on b.parent_id = c.id where c.ship_date >= '".$filters['s_undertakerday']."' and b.body_price = '0' group by b.product_code) as g "), 'g.product_code','=','a.product_code')
        //         ->leftJoin(DB::raw("(select d.product_code,sum(convert(decimal(18,2),ISNULL(d.body_num,0)*body_rate)) as num1,sum(CAST( CASE WHEN tax = '稅外加' THEN d.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) ) ELSE d.body_subtotal END AS decimal(10,2))) as total1 from BA203_62 as d left join BA203_61 as e on d.parent_id = e.id where e.back_day >= '".$filters['s_undertakerday']."' and d.body_price = 0.00 group by d.product_code) as f "), 'f.product_code','=','a.product_code');
        //     }

        // }else if(empty($filters['s_undertakerday']) && !empty($filters['e_undertakerday'])){
        //     if($filters['yn_price']=="否"){
        //         $BA202data=$BA202data
        //         ->leftJoin(DB::raw("(select b.product_code,sum(convert(decimal(18,2),ISNULL(b.body_num,0)*body_rate)) as num,sum(CAST( CASE WHEN b_tax = '稅外加' THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) ) ELSE b.body_subtotal END AS decimal(10,2))) as total from BA202_53 as b left join BA202_52 as c on b.parent_id = c.id where c.ship_date <= '".$filters['e_undertakerday']."' and b.body_price != '0' group by b.product_code) as g "), 'g.product_code','=','a.product_code')
        //         ->leftJoin(DB::raw("(select d.product_code,sum(convert(decimal(18,2),ISNULL(d.body_num,0)*body_rate)) as num1,sum(CAST( CASE WHEN tax = '稅外加' THEN d.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) ) ELSE d.body_subtotal END AS decimal(10,2))) as total1 from BA203_62 as d left join BA203_61 as e on d.parent_id = e.id where e.back_day <= '".$filters['e_undertakerday']."' and d.body_price != 0.00 group by d.product_code) as f "), 'f.product_code','=','a.product_code');
        //     }else{
        //         $BA202data=$BA202data
        //         ->leftJoin(DB::raw("(select b.product_code,sum(convert(decimal(18,2),ISNULL(b.body_num,0)*body_rate)) as num,sum(CAST( CASE WHEN b_tax = '稅外加' THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) ) ELSE b.body_subtotal END AS decimal(10,2))) as total from BA202_53 as b left join BA202_52 as c on b.parent_id = c.id where c.ship_date <= '".$filters['e_undertakerday']."' and b.body_price = '0' group by b.product_code) as g "), 'g.product_code','=','a.product_code')
        //         ->leftJoin(DB::raw("(select d.product_code,sum(convert(decimal(18,2),ISNULL(d.body_num,0)*body_rate)) as num1,sum(CAST( CASE WHEN tax = '稅外加' THEN d.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) ) ELSE d.body_subtotal END AS decimal(10,2))) as total1 from BA203_62 as d left join BA203_61 as e on d.parent_id = e.id where e.back_day <= '".$filters['e_undertakerday']."' and d.body_price = 0.00 group by d.product_code) as f "), 'f.product_code','=','a.product_code');
        //     }

        // }else{
        //     if($filters['yn_price']=="否"){
        //         $BA202data=$BA202data
        //         ->leftJoin(DB::raw("(select b.product_code,sum(convert(decimal(18,2),ISNULL(b.body_num,0)*body_rate)) as num,sum(CAST( CASE WHEN b_tax = '稅外加' THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) ) ELSE b.body_subtotal END AS decimal(10,2))) as total from BA202_53 as b left join BA202_52 as c on b.parent_id = c.id where c.ship_date >= '".$filters['s_undertakerday']."' and c.ship_date <= '".$filters['e_undertakerday']."' and b.body_price != '0' group by b.product_code) as g "), 'g.product_code','=','a.product_code')
        //         ->leftJoin(DB::raw("(select d.product_code,sum(convert(decimal(18,2),ISNULL(d.body_num,0)*body_rate)) as num1,sum(CAST( CASE WHEN tax = '稅外加' THEN d.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) ) ELSE d.body_subtotal END AS decimal(10,2))) as total1 from BA203_62 as d left join BA203_61 as e on d.parent_id = e.id where e.back_day >= '".$filters['s_undertakerday']."'and e.back_day <= '".$filters['e_undertakerday']."' and d.body_price != 0.00 group by d.product_code) as f "), 'f.product_code','=','a.product_code');
        //     }else{
        //         $BA202data=$BA202data
        //         ->leftJoin(DB::raw("(select b.product_code,sum(convert(decimal(18,2),ISNULL(b.body_num,0)*body_rate)) as num,sum(CAST( CASE WHEN b_tax = '稅外加' THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) ) ELSE b.body_subtotal END AS decimal(10,2))) as total from BA202_53 as b left join BA202_52 as c on b.parent_id = c.id where c.ship_date >= '".$filters['s_undertakerday']."' and c.ship_date <= '".$filters['e_undertakerday']."' and b.body_price = '0' group by b.product_code) as g "), 'g.product_code','=','a.product_code')
        //         ->leftJoin(DB::raw("(select d.product_code,sum(convert(decimal(18,2),ISNULL(d.body_num,0)*body_rate)) as num1,sum(CAST( CASE WHEN tax = '稅外加' THEN d.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) ) ELSE d.body_subtotal END AS decimal(10,2))) as total1 from BA203_62 as d left join BA203_61 as e on d.parent_id = e.id where e.back_day >= '".$filters['s_undertakerday']."'and e.back_day <= '".$filters['e_undertakerday']."' and d.body_price = 0.00 group by d.product_code) as f "), 'f.product_code','=','a.product_code');
        //     }

        // }





        // $save1=$BA202data->groupbyraw("a.product_code,a.product_name,a.unit_name,a.sell_price,a.purchase_price,g.num,f.num1,g.total,f.total1")->get();
        $company_name = "孔媽媽";

        $user = User::find(SessionUtil::getUserID())->name;
        foreach($save1 as $key=>$row ){
            $row->company_name = $company_name;
            $row->s_undertakerday = $filters['s_undertakerday'];
            $row->e_undertakerday = $filters['e_undertakerday'];
            $row->user = $user;
        }

        $this->datas = $save1;
		//dd($this->datas);

    }
}
