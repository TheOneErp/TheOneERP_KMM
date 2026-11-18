<?php
namespace App\Http\Controllers\API\BA\BA2;

use App\Http\Controllers\Base\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Utils\DataUtil;
use App\Utils\ValidationUtil;

class BA213Controller extends Controller
{
    // 取回出貨單小視窗：搜尋一般出貨單 BA202（排除已被請款單取回者）
    static public function getShipForInvoice(Request $request)
    {
        if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

        $q = DB::table('BA202_52 as a')
            ->join('BA202_53 as b', 'b.parent_id', '=', 'a.id')
            ->select(DB::raw("[choose] = NULL, a.ship_code, CONVERT(varchar(10), a.ship_date, 23) as ship_date,
                b.product_code, b.product_name, b.body_num, b.unit_code, b.unit_name, b.body_price, b.body_subtotal,
                b.remarks as body_remarks, b.id as source_body_id"))
            ->where('a.client_code', '=', $data['client_code'])
            ->whereNotNull('b.product_code')->where('b.product_code', '<>', '')
            ->whereNotExists(function ($s) {
                $s->select(DB::raw(1))->from('BA213_6274 as i')->whereRaw('i.source_body_id = b.id');
            });

        $map = [
            ['ship_codeS', 'a.ship_code', '>='], ['ship_codeE', 'a.ship_code', '<='],
            ['ship_dateS', 'a.ship_date', '>='], ['ship_dateE', 'a.ship_date', '<='],
            ['product_codeS', 'b.product_code', '>='], ['product_codeE', 'b.product_code', '<='],
        ];
        foreach ($map as [$k, $col, $op]) {
            if (!empty($data[$k])) $q->where($col, $op, $data[$k]);
        }
        return $q->orderBy('a.ship_code')->orderBy('b.id')->get();
    }

    // 列印請款單（收款對帳明細表）
    static public function printInvoice(Request $request)
    {
        if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

        $head = DB::table('BA213_6273')->where('invoice_code', '=', $data['invoice_code'])->first();
        if (!$head) return ['status' => false, 'messages' => ['找不到請款單，請先儲存']];

        $body = DB::table('BA213_6274')->where('parent_id', '=', $head->id)
            ->whereNotNull('ship_code')->orderBy('ship_date')->orderBy('ship_code')->orderBy('id')->get();

        $cust = DB::table('BA102_37 as a')
            ->select(DB::raw("a.client_code,a.client_name,a.uniform_num,a.fax,b.phone,b.contact,c.addr"))
            ->leftJoin('BA102_38 as b', 'b.parent_id', '=', 'a.id')
            ->leftJoin('BA102_39 as c', 'c.parent_id', '=', 'a.id')
            ->where('a.client_code', '=', $head->client_code)->orderBy('b.id')->first();

        // 結帳日期：表身最早到最晚出貨日期，民國年 YYYMMDD（例 1151001到1151030）
        $dates = $body->pluck('ship_date')->filter()->sort()->values();
        $roc = function ($d) {
            $t = strtotime($d);
            return $t ? sprintf('%03d%02d%02d', date('Y', $t) - 1911, date('m', $t), date('d', $t)) : '';
        };
        $closing = count($dates) ? $roc($dates->first()) . '到' . $roc($dates->last()) : '';

        $sum = 0;
        foreach ($body as $r) $sum += (float)$r->body_subtotal;
        $tax = (float)$head->otax;
        $periodTotal = $sum + $tax;

        $rows = [];
        foreach ($body as $r) {
            $rows[] = (object)[
                'client_code' => $head->client_code,
                'client_name' => $head->client_name,
                'contact' => $cust->contact ?? '',
                'uniform_num' => $cust->uniform_num ?? '',
                'phone' => $cust->phone ?? '',
                'fax' => $cust->fax ?? '',
                'addr' => $cust->addr ?? '',
                'print_date' => date('Y/m/d'),
                'print_user' => session('user_name'),
                'closing_date' => $closing,
                'ship_date' => $r->ship_date ? date('Y/m/d', strtotime($r->ship_date)) : '',
                'product_name' => $r->product_name,
                'body_num' => $r->body_num,
                'unit_name' => $r->unit_name,
                'body_price' => $r->body_price,
                'body_subtotal' => $r->body_subtotal,
                'remarks' => $r->remarks,
                'tax' => $tax,
                'period_total' => $periodTotal,
                'company_name' => '孔媽媽生活事業商行',
            ];
        }
        if (!count($rows)) return ['status' => false, 'messages' => ['請款單無明細']];

        $reportClass = new ReportController;
        return $reportClass->export('pdf', 'BA213', $rows);
    }
}
