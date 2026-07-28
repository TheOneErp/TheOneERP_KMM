<?php

namespace App\Http\Inject\Reports\CA\CA3;

use App\Models\User;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;
use Illuminate\Support\Facades\DB;

class CA302
{
    public $datas;

    public function __construct($filters)
    {
        $this->datas = [];

        $CA202data = DB::table('CA202_54 as a')
            ->select(
                'a.vendor_code',
                'a.vendor_name',
                'a.receive_day',
                'a.receive_code',
                'a.tax as taxname',
                'a.ssubtotal',
                'a.stax',
                'a.stotal',
                'a.ototal',
                'a.amt_paid',
                'a.amt_unpaid',

                // 總付款狀態
                DB::raw("
                    CASE 
                        WHEN a.amt_paid IS NULL OR a.amt_paid = 0 OR a.amt_paid = '0' 
                            THEN '未付款'
                        WHEN a.amt_unpaid IS NULL OR a.amt_unpaid = 0 OR a.amt_unpaid = '0' 
                            THEN '已付款'
                        ELSE '部份付款'
                    END as h_pay_status
                "),

                // 可選：把明細商品簡單串接起來顯示（如果需要）
                DB::raw("STRING_AGG(CONCAT(b.product_name, '(', b.body_num, b.unit_name, ')'), '、') as product_summary")
            )
            ->leftJoin('CA202_55 as b', 'b.parent_id', '=', 'a.id')
            ->leftJoin('CA102_27 as c', 'c.vendor_code', '=', 'a.vendor_code')

            // === 只取每個 receive_code 最新一筆（只看 receive_day）===
            ->whereIn('a.id', function ($sub) {
                $sub->select('id')
                    ->from(function ($q) {
                        $q->select('id')
                          ->selectRaw('ROW_NUMBER() OVER (PARTITION BY receive_code ORDER BY receive_day DESC) as rn')
                          ->from('CA202_54 as t');
                    }, 'ranked')
                    ->where('rn', '=', 1);
            })

            ->groupBy(
                'a.id',
                'a.vendor_code',
                'a.vendor_name',
                'a.receive_day',
                'a.receive_code',
                'a.tax',
                'a.ssubtotal',
                'a.stax',
                'a.stotal',
                'a.ototal',
                'a.amt_paid',
                'a.amt_unpaid'
            );

        // ====================== 篩選條件 ======================
        if (!empty($filters['s_receive_day'])) {
            $CA202data->where('a.receive_day', '>=', $filters['s_receive_day']);
        }
        if (!empty($filters['e_receive_day'])) {
            $CA202data->where('a.receive_day', '<=', $filters['e_receive_day']);
        }
        if (!empty($filters['s_vendor_code'])) {
            $CA202data->where('a.vendor_code', '>=', $filters['s_vendor_code']);
        }
        if (!empty($filters['e_vendor_code'])) {
            $CA202data->where('a.vendor_code', '<=', $filters['e_vendor_code']);
        }

        if (!empty($filters['pay_status'])) {
            $CA202data->having('h_pay_status', '=', $filters['pay_status']);
        }

        if (VerifyUtil::pageVerifyConfirmation(60)) {
            $CA202data->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
        }

        // 最終查詢
        $this->datas = $CA202data
            ->orderBy('a.vendor_code')
            ->orderBy('a.receive_code')
            ->orderBy('a.receive_day', 'desc')
            ->get();

        // 附加報表資訊
        $user = User::find(SessionUtil::getUserID())->name ?? '';

        foreach ($this->datas as $row) {
            $row->s_receive_day = $filters['s_receive_day'] ?? '';
            $row->e_receive_day = $filters['e_receive_day'] ?? '';
            $row->s_vendor_code = $filters['s_vendor_code'] ?? '';
            $row->e_vendor_code = $filters['e_vendor_code'] ?? '';
            $row->user = $user;
        }
    }
}