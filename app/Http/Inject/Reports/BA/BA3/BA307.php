<?php

namespace App\Http\Inject\Reports\BA\BA3;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class BA307 {
    public $datas;

    public function __construct($filters) {
        $this->datas = collect();

        $company_name = "孔媽媽";
        $company_tel  = "07-6285979";
        $company_fax  = "";
        $company_addr = "820高雄市岡山區大莊里大莊路350號";
        $company_mail = "";

        // 1. 出貨單一般產品明細 (與 BA202Controller::printShip 的 $reportData 相同邏輯)
        $mainQuery = DB::table('BA202_52 as a')
            ->select(DB::raw("
                a.ship_code, a.ship_date, a.client_code, a.client_name, a.fax,
                a.otax as otax,
                a.osubtotal as osubtotal,
                ROUND(a.ototal, 0) as ototal,
                a.invoice_num, a.remarks as head_remarks,
                b.product_code, b.product_name,
                b.body_num, b.unit_name, b.body_price, b.body_subtotal,
                b.remarks, b.gift_options,
                '1' as sn, b.id as idid, '1' as con
            "))
            ->leftJoin('BA202_53 as b', 'b.parent_id', '=', 'a.id');

        // 2. 組合品展開明細 (與 BA202Controller::printShip 的 $reportData1 相同邏輯)
        $comboQuery = DB::table('BA202_52 as a')
            ->select(DB::raw("
                a.ship_code, a.ship_date, a.client_code, a.client_name, a.fax,
                a.otax as otax,
                a.osubtotal as osubtotal,
                ROUND(a.ototal, 0) as ototal,
                a.invoice_num, a.remarks as head_remarks,
                d.cont_code as product_code, d.cont_name as product_name,
                CAST(b.body_num*d.body_num*d.body_rate AS decimal(10,2)) as body_num,
                d.unit_name,
                null as body_price, null as body_subtotal,
                null as remarks, null as gift_options,
                '2' as sn, b.id as idid, null as con
            "))
            ->leftJoin('BA202_53 as b', 'b.parent_id', '=', 'a.id')
            ->leftJoin('AA204_2224 as c', function ($q) {
                $q->on('b.combi_code', '=', 'c.combi_code')
                  ->on('b.product_code', '=', 'c.product_code');
            })
            ->leftJoin('AA204_2225 as d', 'd.parent_id', '=', 'c.id')
            ->whereNotNull('b.combi_code');

        // ---- 篩選條件 ----

        // 查詢日期範圍 (依出貨日期)
        if (!empty($filters['s_undertakerday'])) {
            $mainQuery->where('a.ship_date', '>=', $filters['s_undertakerday']);
            $comboQuery->where('a.ship_date', '>=', $filters['s_undertakerday']);
        }
        if (!empty($filters['e_undertakerday'])) {
            $mainQuery->where('a.ship_date', '<=', $filters['e_undertakerday']);
            $comboQuery->where('a.ship_date', '<=', $filters['e_undertakerday']);
        }

        // 客戶代碼 (起/迄兩欄位皆可為「複選」挑選後以分號組成的字串，合併後做 IN 查詢)
        $clientCodes = [];
        foreach (['s_client_code', 'e_client_code'] as $key) {
            if (!empty($filters[$key])) {
                $codes = array_filter(array_map('trim', explode(';', $filters[$key])));
                $clientCodes = array_merge($clientCodes, $codes);
            }
        }
        $clientCodes = array_values(array_unique($clientCodes));
        if (!empty($clientCodes)) {
            $mainQuery->whereIn('a.client_code', $clientCodes);
            $comboQuery->whereIn('a.client_code', $clientCodes);
        }

        // 產品代碼：篩選「有出貨此產品」的出貨單，該張出貨單仍會完整列印全部品項
        if (!empty($filters['product_code'])) {
            $matchShipCodes = DB::table('BA202_52 as a')
                ->select('a.ship_code')
                ->leftJoin('BA202_53 as b', 'b.parent_id', '=', 'a.id')
                ->where('b.product_code', '=', $filters['product_code'])
                ->distinct()
                ->pluck('ship_code')
                ->toArray();

            if (empty($matchShipCodes)) {
                $this->datas = collect();
                return;
            }
            $mainQuery->whereIn('a.ship_code', $matchShipCodes);
            $comboQuery->whereIn('a.ship_code', $matchShipCodes);
        }

        $rows = $mainQuery->unionAll($comboQuery)
            ->orderBy('client_code')
            ->orderBy('ship_code')
            ->orderBy('idid')
            ->orderBy('sn')
            ->get();

        if ($rows->isEmpty()) {
            $this->datas = collect();
            return;
        }

        // 依訂單所屬客戶，逐一撈取客戶資料 (電話/聯絡人/地址/統編/類別)
        // 與 printShip 相同：每個客戶只取第一筆 (avoid BA102_38/BA102_39 一對多 join 造成的重複列)
        $clientCodesInResult = $rows->pluck('client_code')->unique()->values()->all();
        $customerCache = [];
        foreach ($clientCodesInResult as $code) {
            $customerCache[$code] = DB::table('BA102_37 as a')
                ->select(DB::raw("a.client_code,a.uniform_num,b.phone,b.contact,c.addr,a.client_cat"))
                ->leftJoin('BA102_38 as b', 'b.parent_id', '=', 'a.id')
                ->leftJoin('BA102_39 as c', 'c.parent_id', '=', 'a.id')
                ->where('a.client_code', '=', $code)
                ->orderBy('b.id')
                ->first();
        }

        foreach ($rows as $row) {
            $customer = $customerCache[$row->client_code] ?? null;

            $row->phone        = $customer->phone ?? null;
            $row->contact      = $customer->contact ?? null;
            $row->addr         = $customer->addr ?? null;
            $row->client_cat   = $customer->client_cat ?? null;
            $row->uniform_num  = $customer->uniform_num ?? null;
            $row->company_name = $company_name;
            $row->company_tel  = $company_tel;
            $row->company_fax  = $company_fax;
            $row->company_addr = $company_addr;
            $row->company_mail = $company_mail;
        }

        $this->datas = $rows;
    }
}
