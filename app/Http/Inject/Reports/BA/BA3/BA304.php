<?php

namespace App\Http\Inject\Reports\BA\BA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;
use Illuminate\Support\Facades\DB;

class BA304 {
    public $datas;

    public function __construct($filters) {
        $this->datas = [];

        // 1. 出貨單資料 (BA202)
        $BA202data = DB::table('BA202_52 as a')
            ->select(DB::raw("
                a.client_code,
                a.client_name, 
                a.undertakerday,
                CONVERT(char(10), a.ship_date, 120) as ship_date,
                a.ship_code,
                a.amt_discount,
                b.b_tax as taxname,
                a.ssubtotal,
                a.stax,
                a.stotal,
                
                -- 依照 payment_status 決定 amt_outstanding
                (CASE 
                    WHEN b.payment_status = '未收款' THEN b.body_subtotal 
                    WHEN b.payment_status = '已收款' THEN 0 
                    ELSE a.amt_outstanding   
                END) as amt_outstanding,
                
                a.ototal,
                b.product_code, 
                b.product_name,
                b.body_num,
                b.unit_name, 
                b.body_price, 
                b.body_subtotal,
                b.remarks,
                b.payment_status,
                c.uniform_num,
                a.final_pmt as amt_unrecd,
                2 as sn
            "))
            ->leftJoin('BA202_53 as b', 'b.parent_id', '=', 'a.id')
            ->leftJoin('BA102_37 as c', 'c.client_code', '=', 'a.client_code')
            ->where('yn_cnt_cust', '=', 0);

        // 2. 退貨單資料 (BA203)
        $BA203data = DB::table('BA203_61 as a')
            ->select(DB::raw("
                a.client_code,
                a.client_name, 
                a.undertakerday,
                ('退回項目\n' + CONVERT(char(10), a.back_day, 120)) as ship_date,
                a.back_code as ship_code,
                null as amt_discount,
                a.tax as taxname, 
                -a.ssubtotal as ssubtotal,
                -a.stax as stax,
                -a.stotal as stotal, 
                0 as amt_outstanding, 
                -a.ototal as ototal,
                b.product_code, 
                b.product_name,
                b.body_num,
                b.unit_name,
                b.body_price, 
                -b.body_subtotal as body_subtotal,
                b.body_remarks as remarks,
                null as payment_status,
                c.uniform_num,
                NULL as amt_unrecd,
                3 as sn
            "))
            ->leftJoin('BA203_62 as b', 'b.parent_id', '=', 'a.id')
            ->leftJoin('BA102_37 as c', 'c.client_code', '=', 'a.client_code')
            ->where('yn_cnt_cust', '=', 0);

        // 篩選條件：查詢日期
        if (!empty($filters['s_undertakerday'])) {
            $BA202data = $BA202data->where('ship_date', '>=', $filters['s_undertakerday']);
            $BA203data = $BA203data->where('back_day', '>=', $filters['s_undertakerday']);
        }

        if (!empty($filters['e_undertakerday'])) {
            $BA202data = $BA202data->where('ship_date', '<=', $filters['e_undertakerday']);
            $BA203data = $BA203data->where('back_day', '<=', $filters['e_undertakerday']);
        }

        // 篩選條件：客戶代碼
        if (!empty($filters['s_client_code'])) {
            $BA202data = $BA202data->where('a.client_code', '>=', $filters['s_client_code']);
            $BA203data = $BA203data->where('a.client_code', '>=', $filters['s_client_code']);
        }

        if (!empty($filters['e_client_code'])) {
            $BA202data = $BA202data->where('a.client_code', '<=', $filters['e_client_code']);
            $BA203data = $BA203data->where('a.client_code', '<=', $filters['e_client_code']);
        }

        // 篩選條件：付款狀態
        if (!empty($filters['payment_status'])) {
            $BA202data = $BA202data->where('b.payment_status', '=', $filters['payment_status']);
        }

        // 進行 Union 聯集並排序 (優先依客戶代碼排序，方便 Jasper 進行 Group 切換)
        $resultData = $BA202data
            ->unionAll($BA203data)
            ->orderby('client_code')
            ->orderby('undertakerday')
            ->orderby('sn')
            ->orderby('ship_code')
            ->get();

        $this->datas = $resultData;
        $user = User::find(SessionUtil::getUserID())->name ?? '';
        $check = "";

        // 後處理資料與過濾重複單據欄位
        foreach ($this->datas as $key => $row) {
            $row->s_undertakerday = $filters['s_undertakerday'] ?? '';
            $row->e_undertakerday = $filters['e_undertakerday'] ?? '';
            $row->s_client_code   = $filters['s_client_code'] ?? '';
            $row->e_client_code   = $filters['e_client_code'] ?? '';
            $row->user            = $user;

            if ($check == $row->ship_code) {
                $row->stotal     = null;
                $row->amt_unrecd = null;
            } else {
                $check = $row->ship_code;
            }
        }
    }
}