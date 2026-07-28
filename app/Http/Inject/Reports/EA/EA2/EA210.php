<?php

namespace App\Http\Inject\Reports\EA\EA2;

use App\Models\User;
use App\Utils\SessionUtil;
use Illuminate\Support\Facades\DB;

class EA210
{
    public $datas;

    public function __construct($filters)
    {
        $this->datas = [];

        $EA204data = DB::table('EA204_79 as a')
            ->leftJoin('AA202_30 as b', 'b.product_code', '=', 'a.product_code')
            ->select(
                'a.product_code',
                'a.product_name',
                DB::raw('SUM(a.num) AS num'), // 對 num 求和
                'a.unit_name',
                'b.pro_cat_code',
                'b.pro_cat_name'
            );

        if (!empty($filters['product_code_s'])) {
            $EA204data = $EA204data->where('a.product_code', '>=', $filters['product_code_s']);
        }

        if (!empty($filters['product_code_e'])) {
            $EA204data = $EA204data->where('a.product_code', '<=', $filters['product_code_e']);
        }

        if (!empty($filters['pro_cat_code'])) {
            $proCatCodes = explode(';', rtrim($filters['pro_cat_code'], ';'));
            $EA204data = $EA204data->whereIn('b.pro_cat_code', $proCatCodes);
        }

        $this->datas = $EA204data
            ->groupBy(
                'a.product_code',
                'a.product_name',
                'a.unit_name',
                'b.pro_cat_code',
                'b.pro_cat_name'
            )
            ->orderBy('a.product_code')
            ->get();
          //  dd($this->datas);
        $user = User::find(SessionUtil::getUserID())->name;
        foreach ($this->datas as $row) {
            $row->product_code_s = $filters['product_code_s'] ?? '';
            $row->product_code_e = $filters['product_code_e'] ?? '';
            $row->user = $user;
        }
    }
}