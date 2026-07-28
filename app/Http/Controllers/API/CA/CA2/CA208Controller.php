<?php
namespace App\Http\Controllers\API\CA\CA2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;
use App\Utils\VerifyUtil;

use Carbon\Carbon;
use App\Http\Controllers\CommonController;

/*use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;*/

class CA208Controller extends Controller{
	static public function getChargeOff1(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
        // dd($data);


		DB::beginTransaction();
        foreach($data["source_code"] as $key=>$row ){
            // dd($row);
            $check=DB::table("CA202_54 as a")
            ->leftJoin('CA202_55 as b', 'a.id', '=', 'b.parent_id')
            ->where('receive_code', '=', $row)->get();
            if($check->count() > 0){
                DB::table("CA202_55 as a")
                ->leftJoin('CA202_54 as b', 'b.id', '=', 'a.parent_id')
                ->where('receive_code', '=', $row)
                ->update([
                    'pay_status' => "已付款",
                ]);
                if($data["discount"][$key]!=0){
                    DB::table("CA202_54")
                    ->where('receive_code', '=', $row)
                    ->update([
                        'amt_discount' => $data["discount"][$key],
                        'ototal' => $check[0]->ototal - $data["discount"][$key],
                        'stotal' => $check[0]->stotal - $data["discount"][$key],
                    ]);
                }
            }else{
                DB::rollback();
                $msgArr = ["text" => "警告:沖帳失敗，請洽維護人員","status" => true];
            }

        }
        DB::commit();
        $msgArr = ["text" => "沖帳成功，請儲存沖帳紀錄","status" => false];


            // dd("123");
		return $msgArr;
	}
    static public function getPayable(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

        $orderData = DB::table('CA202_54 as a')
        ->leftJoin('CA202_55 as b', 'a.id', '=', 'b.parent_id')
        ->where('a.vendor_code','=',$data['vendor_code'])
        ->where('b.pay_status','=','未付款');

        // dd($orderData);
        // dd($orderData->count());
        if( !is_null($data['receive_day_s']) && $data['receive_day_s'] != '' ){
			$orderData = $orderData->where('a.receive_day','>=',$data['receive_day_s']);
		}
        if( !is_null($data['receive_day_e']) && $data['receive_day_e'] != '' ){
			$orderData = $orderData->where('a.receive_day','<=',$data['receive_day_e']);
		}
        $orderData = $orderData->select(DB::raw("a.receive_code,a.receive_day,a.ototal,a.amt_paid,isnull(a.amt_discount,0) as amt_discount,(a.ototal - a.amt_paid - isnull(a.amt_discount,0)) as amt_unpaid"))
        ->groupBy('a.receive_code','a.receive_day','a.ototal','a.amt_paid','a.amt_discount')->orderBy('receive_code','asc')->get();
//         $orderData = $orderData->select(DB::raw("a.receive_code,a.receive_day,a.ototal,round(CAST(sum( CASE
//         WHEN tax = '稅外加'
//            THEN b.body_subtotal * ( 1 +  CAST(('0' + taxrate) AS float) )
//         ELSE b.body_subtotal
//    END) AS decimal(10,2)),2) as payable"))->groupBy('a.receive_code','a.receive_day','a.ototal')->orderBy('receive_code','asc')->get();
            // dd("123");
            // dd($orderData);
		return $orderData;
	}



}
?>
