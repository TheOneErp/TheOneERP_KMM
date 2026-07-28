<?php

namespace App\Http\Inject\Reports\EA\EA2;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;
use Illuminate\Support\Facades\DB;

class EA212
{
    public $datas;

    public function __construct($filters)
    {
        $this->datas = [];

        // ====================== 1. 計算歷史期初庫存 (累計到 date_s - 1) ======================
        $openingStock = collect();

        if (!empty($filters['date_s'])) {
            $dateBefore = date('Y-m-d', strtotime($filters['date_s'] . ' -1 day'));

            // ---------- BA202 ----------
            $opBA202 = DB::table('BA202_53 as a')
                ->select(
                    'c.body_depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(a.body_num) as opening_qty')
                )
                ->leftJoin('BA202_52 as c', 'c.id', '=', 'a.parent_id')
                ->where('a.product_kind','!=','費用')
                ->where('a.product_kind','!=','運費')
                ->where('a.product_kind','!=','折扣')
                ->whereNull('a.combi_code')
                ->where('c.ship_date', '<=', $dateBefore)
                ->groupBy('c.body_depot_code', 'a.product_code');

            $opBA202_1 = DB::table('BA202_52 as a')
                ->select(
                    'b.body_depot_code as depot_code',
                    'd.cont_code as product_code',
                    DB::raw('SUM(CAST(b.body_num * d.body_num * d.body_rate AS decimal(18,4))) as opening_qty')
                )
                ->leftJoin('BA202_53 as b', 'b.parent_id', '=', 'a.id')
                ->leftJoin('AA204_2224 as c', function($q){
                    $q->on('b.combi_code', '=', 'c.combi_code')
                      ->on('b.product_code', '=', 'c.product_code');
                })
                ->leftJoin('AA204_2225 as d', 'd.parent_id', '=', 'c.id')
                ->whereNotNull('b.combi_code')
                ->where('a.ship_date', '<=', $dateBefore)
                ->groupBy('b.body_depot_code', 'd.cont_code');

            // ---------- BA211 ----------
            $opBA211 = DB::table('BA211_6264 as a')
                ->select(
                    'c.body_depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(a.body_num) as opening_qty')
                )
                ->leftJoin('BA211_6263 as c', 'c.id', '=', 'a.parent_id')
                ->where('a.product_kind','!=','費用')
                ->where('a.product_kind','!=','運費')
                ->where('a.product_kind','!=','折扣')
                ->whereNull('a.combi_code')
                ->where('c.ship_date', '<=', $dateBefore)
                ->groupBy('c.body_depot_code', 'a.product_code');

            $opBA211_1 = DB::table('BA211_6263 as a')
                ->select(
                    'b.body_depot_code as depot_code',
                    'd.cont_code as product_code',
                    DB::raw('SUM(CAST(b.body_num * d.body_num * d.body_rate AS decimal(18,4))) as opening_qty')
                )
                ->leftJoin('BA211_6264 as b', 'b.parent_id', '=', 'a.id')
                ->leftJoin('AA204_2224 as c', function($q){
                    $q->on('b.combi_code', '=', 'c.combi_code')
                      ->on('b.product_code', '=', 'c.product_code');
                })
                ->leftJoin('AA204_2225 as d', 'd.parent_id', '=', 'c.id')
                ->whereNotNull('b.combi_code')
                ->where('a.ship_date', '<=', $dateBefore)
                ->groupBy('b.body_depot_code', 'd.cont_code');

            // ---------- BA203 ----------
            $opBA203 = DB::table('BA203_62 as a')
                ->select(
                    'c.body_depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(a.body_num) as opening_qty')
                )
                ->leftJoin('BA203_61 as c', 'c.id', '=', 'a.parent_id')
                ->where('a.product_kind','!=','費用')
                ->where('a.product_kind','!=','運費')
                ->where('a.product_kind','!=','折扣')
                ->whereNull('a.combi_code')
                ->where('c.back_day', '<=', $dateBefore)
                ->groupBy('c.body_depot_code', 'a.product_code');

            $opBA203_1 = DB::table('BA203_61 as a')
                ->select(
                    'b.body_depot_code as depot_code',
                    'd.cont_code as product_code',
                    DB::raw('SUM(CAST(b.body_num * d.body_num * d.body_rate AS decimal(18,4))) as opening_qty')
                )
                ->leftJoin('BA203_62 as b', 'b.parent_id', '=', 'a.id')
                ->leftJoin('AA204_2224 as c', function($q){
                    $q->on('b.combi_code', '=', 'c.combi_code')
                      ->on('b.product_code', '=', 'c.product_code');
                })
                ->leftJoin('AA204_2225 as d', 'd.parent_id', '=', 'c.id')
                ->whereNotNull('b.combi_code')
                ->where('a.back_day', '<=', $dateBefore)
                ->groupBy('b.body_depot_code', 'd.cont_code');

            // ---------- CA202 ----------
            $opCA202 = DB::table('CA202_55 as a')
                ->select(
                    'c.body_depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(a.body_num) as opening_qty')
                )
                ->leftJoin('CA202_54 as c', 'c.id', '=', 'a.parent_id')
                ->where('a.product_kind','!=','費用')
                ->where('a.product_kind','!=','運費')
                ->where('a.product_kind','!=','折扣')
                ->where('c.receive_day', '<=', $dateBefore)
                ->groupBy('c.body_depot_code', 'a.product_code');

            // ---------- CA203 ----------
            $opCA203 = DB::table('CA203_64 as a')
                ->select(
                    'c.body_depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(-a.body_num) as opening_qty')
                )
                ->leftJoin('CA203_63 as c', 'c.id', '=', 'a.parent_id')
                ->where('a.product_kind','!=','費用')
                ->where('a.product_kind','!=','運費')
                ->where('a.product_kind','!=','折扣')
                ->where('c.abort_day', '<=', $dateBefore)
                ->groupBy('c.body_depot_code', 'a.product_code');

            // ---------- EA202 ----------
            $opEA202_out = DB::table('EA202_60 as a')
                ->select(
                    'a.body_depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(-a.body_num) as opening_qty')
                )
                ->leftJoin('EA202_59 as b', 'b.id', '=', 'a.parent_id')
                ->where('b.undertakerday', '<=', $dateBefore)
                ->groupBy('a.body_depot_code', 'a.product_code');

            $opEA202_in = DB::table('EA202_60 as a')
                ->select(
                    'b.transfer_depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(a.body_num) as opening_qty')
                )
                ->leftJoin('EA202_59 as b', 'b.id', '=', 'a.parent_id')
                ->where('b.undertakerday', '<=', $dateBefore)
                ->groupBy('b.transfer_depot_code', 'a.product_code');

            // ---------- EA201 ----------
            $opEA201 = DB::table('EA201_51 as a')
                ->select(
                    'b.depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(a.body_num) as opening_qty')
                )
                ->leftJoin('EA201_50 as b', 'b.id', '=', 'a.parent_id')
                ->where('b.undertakerday', '<=', $dateBefore)
                ->groupBy('b.depot_code', 'a.product_code');

            // ---------- EA301 ----------
            $opEA301_out = DB::table('EA301_6247 as a')
                ->select(
                    'a.depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(-a.body_num) as opening_qty')
                )
                ->leftJoin('EA301_6246 as b', 'b.id', '=', 'a.parent_id')
                ->where('b.work_date', '<=', $dateBefore)
                ->groupBy('a.depot_code', 'a.product_code');

            $opEA301_in = DB::table('EA301_6248 as a')
                ->select(
                    'a.depot_code as depot_code',
                    'a.product_code',
                    DB::raw('SUM(a.body_num) as opening_qty')
                )
                ->leftJoin('EA301_6246 as b', 'b.id', '=', 'a.parent_id')
                ->where('b.work_date', '<=', $dateBefore)
                ->groupBy('a.depot_code', 'a.product_code');

            // 合併所有期初查詢
            $allOpening = $opBA202->unionAll($opBA202_1)
                ->unionAll($opBA211)->unionAll($opBA211_1)
                ->unionAll($opBA203)->unionAll($opBA203_1)
                ->unionAll($opCA202)
                ->unionAll($opCA203)
                ->unionAll($opEA202_out)->unionAll($opEA202_in)
                ->unionAll($opEA201)
                ->unionAll($opEA301_out)->unionAll($opEA301_in);

            $openingStock = DB::table(DB::raw("({$allOpening->toSql()}) as opening"))
                ->mergeBindings($allOpening)
                ->select('depot_code', 'product_code', DB::raw('SUM(opening_qty) as opening_qty'))
                ->groupBy('depot_code', 'product_code')
                ->get()
                ->keyBy(function($item) {                 
                    return $item->depot_code . '_' . $item->product_code;
                });
        }

        // ====================== 2. 主要交易明細查詢 ======================
        $type = "";

        $BA202data = DB::table('BA202_53 as a')
            ->select(DB::raw("c.ship_code as from_code,c.ship_date as date,c.client_code as code,c.client_name as name, a.product_code,a.product_name,null as add_num, a.body_num as sub_num ,a.unit_name,a.body_depot_code as depot_code,a.body_depot_name as depot_name,a.updated_at"))
            ->leftJoin('AA202_30 as b', 'a.product_code','=','b.product_code')
            ->leftJoin('BA202_52 as c', 'c.id','=','a.parent_id')
            ->where('product_kind','!=','費用')
            ->where('product_kind','!=','運費')
            ->where('product_kind','!=','折扣')
            ->whereNull('a.combi_code');

        $BA202data1 = DB::table('BA202_52 as a')
            ->select(DB::raw("a.ship_code as from_code,a.ship_date as date,a.client_code as code,a.client_name as name,d.cont_code as product_code,d.cont_name as product_name,null as add_num,CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as sub_num,d.unit_name,b.body_depot_code as depot_code,b.body_depot_name as depot_name,a.updated_at"))
            ->leftJoin('BA202_53 as b', 'b.parent_id','=','a.id')
            ->leftJoin('AA204_2224 as c', function($q) use ($type){
                $q->on('b.combi_code', '=', 'c.combi_code')
                ->on('b.product_code', '=', 'c.product_code');
            })
            ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
            ->leftJoin('AA202_30 as e', 'e.product_code','=','b.product_code')
            ->whereNotNull('b.combi_code');

        $BA211data = DB::table('BA211_6264 as a')
            ->select(DB::raw("c.ship_code as from_code,c.ship_date as date,c.client_code as code,c.client_name as name, a.product_code,a.product_name,null as add_num, a.body_num as sub_num ,a.unit_name,a.body_depot_code as depot_code,a.body_depot_name as depot_name,a.updated_at"))
            ->leftJoin('AA202_30 as b', 'a.product_code','=','b.product_code')
            ->leftJoin('BA211_6263 as c', 'c.id','=','a.parent_id')
            ->where('product_kind','!=','費用')
            ->where('product_kind','!=','運費')
            ->where('product_kind','!=','折扣')
            ->whereNull('a.combi_code');

        $BA211data1 = DB::table('BA211_6263 as a')
            ->select(DB::raw("a.ship_code as from_code,a.ship_date as date,a.client_code as code,a.client_name as name,d.cont_code as product_code,d.cont_name as product_name,null as add_num,CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as sub_num,d.unit_name,b.body_depot_code as depot_code,b.body_depot_name as depot_name,a.updated_at"))
            ->leftJoin('BA211_6264 as b', 'b.parent_id','=','a.id')
            ->leftJoin('AA204_2224 as c', function($q) use ($type){
                $q->on('b.combi_code', '=', 'c.combi_code')
                ->on('b.product_code', '=', 'c.product_code');
            })
            ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
            ->leftJoin('AA202_30 as e', 'e.product_code','=','b.product_code')
            ->whereNotNull('b.combi_code');

        $BA203data = DB::table('BA203_62 as a')
            ->select(DB::raw("c.back_code as from_code,c.back_day as date,c.client_code as code,c.client_name as name,a.product_code,a.product_name,a.body_num as add_num,null as sub_num,a.unit_name,a.body_depot_code as depot_code,a.body_depot_name as depot_name,a.updated_at"))
            ->leftJoin('AA202_30 as b', 'a.product_code','=','b.product_code')
            ->leftJoin('BA203_61 as c', 'c.id','=','a.parent_id')
            ->where('product_kind','!=','費用')
            ->where('product_kind','!=','運費')
            ->where('product_kind','!=','折扣')
            ->whereNull('a.combi_code');

        $BA203data1 = DB::table('BA203_61 as a')
            ->select(DB::raw("a.back_code as from_code,a.back_day as date,a.client_code as code,a.client_name as name,d.cont_code as product_code,d.cont_name as product_name,CAST( b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as add_num,null as sub_num,d.unit_name,b.body_depot_code as depot_code,b.body_depot_name as depot_name,a.updated_at"))
            ->leftJoin('BA203_62 as b', 'b.parent_id','=','a.id')
            ->leftJoin('AA204_2224 as c', function($q) use ($type){
                $q->on('b.combi_code', '=', 'c.combi_code')
                ->on('b.product_code', '=', 'c.product_code');
            })
            ->leftJoin('AA204_2225 as d', 'd.parent_id','=','c.id')
            ->leftJoin('AA202_30 as e', 'e.product_code','=','b.product_code')
            ->whereNotNull('b.combi_code');

        $CA202data = DB::table('CA202_55 as a')
            ->select(DB::raw("c.receive_code as from_code,c.receive_day as date,c.vendor_code as code,c.vendor_name as name,a.product_code,a.product_name,body_num as add_num,null as sub_num,a.unit_name,a.body_depot_code as depot_code,a.body_depot_name as depot_name,a.updated_at"))
            ->leftJoin('AA202_30 as b', 'a.product_code','=','b.product_code')
            ->leftJoin('CA202_54 as c', 'c.id','=','a.parent_id')
            ->where('product_kind','!=','費用')
            ->where('product_kind','!=','運費')
            ->where('product_kind','!=','折扣');

        $CA203data = DB::table('CA203_64 as a')
            ->select(DB::raw("c.abort_code as from_code,c.abort_day as date,c.vendor_code as code,c.vendor_name as name,a.product_code,a.product_name,null as add_num,body_num as sub_num,a.unit_name,a.body_depot_code as depot_code,a.body_depot_name as depot_name,a.updated_at"))
            ->leftJoin('AA202_30 as b', 'a.product_code','=','b.product_code')
            ->leftJoin('CA203_63 as c', 'c.id','=','a.parent_id')
            ->where('product_kind','!=','費用')
            ->where('product_kind','!=','運費')
            ->where('product_kind','!=','折扣');

        $EA202outdata = DB::table('EA202_60 as a')
            ->select(DB::raw("b.transfer_code as from_code,b.undertakerday as date,null as code,null as name,a.product_code,a.product_name,null as add_num,body_num as sub_num,a.unit_name,a.body_depot_code as depot_code,a.body_depot_name as depot_name,a.updated_at"))
            ->leftJoin('EA202_59 as b', 'b.id','=','a.parent_id');

        $EA202indata = DB::table('EA202_60 as a')
            ->select(DB::raw("b.transfer_code as from_code,b.undertakerday as date,null as code,null as name,a.product_code,a.product_name,body_num as add_num,null as sub_num,a.unit_name,b.transfer_depot_code as depot_code,b.transfer_depot_name as depot_name,a.updated_at"))
            ->leftJoin('EA202_59 as b', 'b.id','=','a.parent_id');

        $EA201data = DB::table('EA201_51 as a')
            ->select(DB::raw("b.adjust_code as from_code,b.undertakerday as date,null as code,null as name,a.product_code,a.product_name,case when body_num > 0 THEN body_num ELSE null END as add_num,case when body_num < 0 THEN -body_num ELSE null END as sub_num,a.unit_name,b.depot_code as depot_code,b.depot_name as depot_name,a.updated_at"))
            ->leftJoin('EA201_50 as b', 'b.id','=','a.parent_id');

        $EA301data = DB::table('EA301_6247 as a')
            ->leftJoin('EA301_6246 as b', 'b.id','=','a.parent_id')
            ->select(DB::raw("b.docu_number as from_code,b.work_date as date,null as code,null as name,a.product_code,a.product_name,null as add_num,body_num as sub_num,a.unit_name,a.depot_code as depot_code,a.depot_name as depot_name,a.updated_at"));

        $EA301data_2 = DB::table('EA301_6248 as a')
            ->leftJoin('EA301_6246 as b', 'b.id','=','a.parent_id')
            ->select(DB::raw("b.docu_number as from_code,b.work_date as date,null as code,null as name,a.product_code,a.product_name,body_num  as add_num,null as sub_num,a.unit_name,a.depot_code as depot_code,a.depot_name as depot_name,a.updated_at"));

        // ====================== 日期與產品範圍過濾 ======================
        if (!empty($filters['date_s'])) {
            $BA202data = $BA202data->where('c.ship_date', '>=', $filters['date_s']);
            $BA202data1 = $BA202data1->where('a.ship_date', '>=', $filters['date_s']);
            $BA211data = $BA211data->where('c.ship_date', '>=', $filters['date_s']);
            $BA211data1 = $BA211data1->where('a.ship_date', '>=', $filters['date_s']);
            $BA203data = $BA203data->where('c.back_day', '>=', $filters['date_s']);
            $BA203data1 = $BA203data1->where('a.back_day', '>=', $filters['date_s']);
            $CA202data = $CA202data->where('c.receive_day', '>=', $filters['date_s']);
            $CA203data = $CA203data->where('c.abort_day', '>=', $filters['date_s']);
            $EA202outdata = $EA202outdata->where('b.undertakerday', '>=', $filters['date_s']);
            $EA202indata = $EA202indata->where('b.undertakerday', '>=', $filters['date_s']);
            $EA201data = $EA201data->where('b.undertakerday', '>=', $filters['date_s']);
            $EA301data = $EA301data->where('b.work_date', '>=', $filters['date_s']);
            $EA301data_2 = $EA301data_2->where('b.work_date', '>=', $filters['date_s']);
        }

        if (!empty($filters['date_e'])) {
            $BA202data = $BA202data->where('c.ship_date', '<=', $filters['date_e']);
            $BA202data1 = $BA202data1->where('a.ship_date', '<=', $filters['date_e']);
            $BA211data = $BA211data->where('c.ship_date', '<=', $filters['date_e']);
            $BA211data1 = $BA211data1->where('a.ship_date', '<=', $filters['date_e']);
            $BA203data = $BA203data->where('c.back_day', '<=', $filters['date_e']);
            $BA203data1 = $BA203data1->where('a.back_day', '<=', $filters['date_e']);
            $CA202data = $CA202data->where('c.receive_day', '<=', $filters['date_e']);
            $CA203data = $CA203data->where('c.abort_day', '<=', $filters['date_e']);
            $EA202outdata = $EA202outdata->where('b.undertakerday', '<=', $filters['date_e']);
            $EA202indata = $EA202indata->where('b.undertakerday', '<=', $filters['date_e']);
            $EA201data = $EA201data->where('b.undertakerday', '<=', $filters['date_e']);
            $EA301data = $EA301data->where('b.work_date', '<=', $filters['date_e']);
            $EA301data_2 = $EA301data_2->where('b.work_date', '<=', $filters['date_e']);
        }

        if (!empty($filters['product_code_s'])) {
            $BA202data = $BA202data->where('a.product_code', '>=', $filters['product_code_s']);
            $BA202data1 = $BA202data1->where('d.cont_code', '>=', $filters['product_code_s']);
            $BA211data = $BA211data->where('a.product_code', '>=', $filters['product_code_s']);
            $BA211data1 = $BA211data1->where('d.cont_code', '>=', $filters['product_code_s']);
            $BA203data = $BA203data->where('a.product_code', '>=', $filters['product_code_s']);
            $BA203data1 = $BA203data1->where('d.cont_code', '>=', $filters['product_code_s']);
            $CA202data = $CA202data->where('a.product_code', '>=', $filters['product_code_s']);
            $CA203data = $CA203data->where('a.product_code', '>=', $filters['product_code_s']);
            $EA202outdata = $EA202outdata->where('a.product_code', '>=', $filters['product_code_s']);
            $EA202indata = $EA202indata->where('a.product_code', '>=', $filters['product_code_s']);
            $EA201data = $EA201data->where('a.product_code', '>=', $filters['product_code_s']);
            $EA301data = $EA301data->where('a.product_code', '>=', $filters['product_code_s']);
            $EA301data_2 = $EA301data_2->where('a.product_code', '>=', $filters['product_code_s']);
        }

        if (!empty($filters['product_code_e'])) {
            $BA202data = $BA202data->where('a.product_code', '<=', $filters['product_code_e']);
            $BA202data1 = $BA202data1->where('d.cont_code', '<=', $filters['product_code_e']);
            $BA211data = $BA211data->where('a.product_code', '<=', $filters['product_code_e']);
            $BA211data1 = $BA211data1->where('d.cont_code', '<=', $filters['product_code_e']);
            $BA203data = $BA203data->where('a.product_code', '<=', $filters['product_code_e']);
            $BA203data1 = $BA203data1->where('d.cont_code', '<=', $filters['product_code_e']);
            $CA202data = $CA202data->where('a.product_code', '<=', $filters['product_code_e']);
            $CA203data = $CA203data->where('a.product_code', '<=', $filters['product_code_e']);
            $EA202outdata = $EA202outdata->where('a.product_code', '<=', $filters['product_code_e']);
            $EA202indata = $EA202indata->where('a.product_code', '<=', $filters['product_code_e']);
            $EA201data = $EA201data->where('a.product_code', '<=', $filters['product_code_e']);
            $EA301data = $EA301data->where('a.product_code', '<=', $filters['product_code_e']);
            $EA301data_2 = $EA301data_2->where('a.product_code', '<=', $filters['product_code_e']);
        }

        if (VerifyUtil::pageVerifyConfirmation(59)) {
            $BA202data = $BA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
        }

        // ====================== 3. 取得所有交易 ======================
        $transactions = $BA202data
            ->unionAll($BA202data1)
            ->unionAll($BA211data)
            ->unionAll($BA211data1)
            ->unionAll($BA203data)
            ->unionAll($BA203data1)
            ->unionAll($CA202data)
            ->unionAll($CA203data)
            ->unionAll($EA202outdata)
            ->unionAll($EA202indata)
            ->unionAll($EA201data)
            ->unionAll($EA301data)
            ->unionAll($EA301data_2)
            ->orderBy('depot_code','asc')
            ->orderBy('product_code','asc')
            ->orderBy('date','asc')
            ->orderBy('updated_at','asc')
            ->orderBy('from_code','asc')
            ->get();

        // ====================== 4. 計算 Running Stock ======================
        $running = [];
        $result = [];

        foreach ($transactions as $row) {
            $key = $row->depot_code . '_' . $row->product_code;

            if (!isset($running[$key])) {
                $running[$key] = $openingStock->has($key) 
                    ? (float)$openingStock[$key]->opening_qty 
                    : 0.0;
            }

            $movement = 0.0;
            if (!empty($row->add_num))  $movement += (float)$row->add_num;
            if (!empty($row->sub_num))  $movement -= (float)$row->sub_num;

            $before = $running[$key];
            $running[$key] += $movement;

            $row->opening_stock = round($before, 4);
            $row->movement      = round($movement, 4);
            $row->running_stock = round($running[$key], 4);

            $result[] = $row;
        }

        // ====================== 5. 加入報表資訊 ======================
        $user = User::find(SessionUtil::getUserID())->name ?? '系統';

        foreach ($result as $row) {
            $row->date_s         = $filters['date_s'] ?? '';
            $row->date_e         = $filters['date_e'] ?? '';
            $row->product_code_s = $filters['product_code_s'] ?? '';
            $row->product_code_e = $filters['product_code_e'] ?? '';
            $row->user           = $user;
            $row->report_date    = now()->format('Y-m-d H:i');
        }

        $this->datas = $result;
    }
}