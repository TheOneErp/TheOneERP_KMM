<?php
namespace App\Http\Controllers\API\OA\OA1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB; 
use App\Utils\DataUtil;
use App\Utils\ValidationUtil;
use App\Utils\VerifyUtil;

use Carbon\Carbon;
use App\Http\Controllers\CommonController;

class OA101Controller extends Controller
{
    public function getOrderData(Request $request) {
        $year = $request->input('year'); // Correctly accessing the request parameter
        
        $orders = DB::table('BA201_40')
            ->whereYear('undertakerday', '=', $year)
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->undertakerday)->format('m'); 
            });

        $ordercount = [];
        $orderArr = [];

        foreach ($orders as $key => $value) {
            $ordercount[(int)$key] = count($value);
        }

        for ($i = 1; $i <= 12; $i++) {
            $orderArr[$i] = $ordercount[$i] ?? 0;
        }

        return response()->json([
            'months' => array_keys($orderArr),
            'orderCounts' => array_values($orderArr)
        ]);
    }

    public function getShipmentData(Request $request) {
        $year = $request->input('year'); // Correctly accessing the request parameter

        $shipments = DB::table('BA202_52')
            ->whereYear('ship_date', '=', $year)
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->ship_date)->format('m'); 
            });

        $shipmentscount = [];
        $shipmentsArr = [];

        foreach ($shipments as $key => $value) {
            $total = 0;
            foreach ($value as $value2) {
                $total += $value2->stotal;
            }
            $shipmentscount[(int)$key] = [count($value), $total];
        }

        for ($i = 1; $i <= 12; $i++) {
            $shipmentsArr[$i] = $shipmentscount[$i] ?? [0, 0];
        }

        return response()->json([
            'months' => array_keys($shipmentsArr),
            'shipmentCounts' => array_column($shipmentsArr, 0),
            'shipmentAmounts' => array_column($shipmentsArr, 1)
        ]);
    }

    public function getExpenseData(Request $request) {
        $year = $request->input('year'); // Correctly accessing the request parameter
        $year = (int)$year;
    
        $allCosts = DB::table('GA201_3224')
            ->whereYear('exp_date', '=', $year)
            ->select('exp_item', DB::raw("SUM(body_subtotal) as sum_money"))
            ->groupBy('exp_item')
            ->get();
    
        $topCosts = $allCosts->sortByDesc('sum_money')->take(10);
    
        // Sum the remaining items
        $remainingSum = $allCosts->sortByDesc('sum_money')->slice(10)->sum(function ($item) {
            return $item->sum_money;
        });
    
        $formattedData = $topCosts->map(function ($item) {
            return [
                '項目' => $item->exp_item,
                '金額' => $item->sum_money,
            ];
        });
    
        if ($allCosts->count() > 10) {
            $formattedData[] = ['項目' => '其他合計', '金額' => $remainingSum];
        }
    
        return response()->json($formattedData);
    }

    public function getLastMonthExpenseData(Request $request) {
        $year = $request->input('year'); // Correctly accessing the request parameter
        $year = (int)$year;

        $allCosts2 = DB::table('GA201_3224')
            ->whereYear('exp_date', '=', $year -1)
            ->select('exp_item', DB::raw("SUM(body_subtotal) as sum_money"))
            ->groupBy('exp_item')
            ->get();

        $topCosts2 = $allCosts2->sortByDesc('sum_money')->take(10);

        // Sum the remaining items
        $remainingSum2 = $allCosts2->sortByDesc('sum_money')->slice(10)->sum(function ($item) {
            return $item->sum_money;
        });

        $formattedData2 = $topCosts2->map(function ($item) {
            return [
                '項目' => $item->exp_item,
                '金額' => $item->sum_money,
            ];
        });

        if ($allCosts2->count() > 10) {
            $formattedData2[] = ['項目' => '其他合計', '金額' => $remainingSum2];
        }

        return response()->json($formattedData2);
    }
}


?>