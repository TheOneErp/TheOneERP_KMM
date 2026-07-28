<?php
namespace App\Http\Controllers\API\BA\BA2;

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

class BA208Controller extends Controller{
	static public function getChargeOff(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
        // dd($data);


		DB::beginTransaction();
        foreach($data["source_code"] as $key=>$row ){
            // dd($row);
            $check=DB::table("BA202_52 as a")
            ->leftJoin('BA202_53 as b', 'a.id', '=', 'b.parent_id')
            ->where('ship_code', '=', $row)->get();
            // dd($check[0]->ototal);
            if($check->count() > 0){
                DB::table("BA202_53 as a")
                    ->leftJoin('BA202_52 as b', 'b.id', '=', 'a.parent_id')
                    ->where('ship_code', '=', $row)
                    ->update([
                        'payment_status' => "已收款",
                    ]);
                if($data["discount"][$key]!=0){
                    DB::table("BA202_52")
                    ->where('ship_code', '=', $row)
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
    static public function getReceivable(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

        $orderData = DB::table('BA202_52 as a')
        ->leftJoin('BA202_53 as b', 'a.id', '=', 'b.parent_id')
        ->where('a.client_code','=',$data['client_code'])
        ->where('b.payment_status','=','未收款');

        // dd($orderData);
        // dd($orderData->count());
        if( !is_null($data['ship_date_s']) && $data['ship_date_s'] != '' ){
			$orderData = $orderData->where('a.ship_date','>=',$data['ship_date_s']);
		}
        if( !is_null($data['ship_date_e']) && $data['ship_date_e'] != '' ){
			$orderData = $orderData->where('a.ship_date','<=',$data['ship_date_e']);
		}
        $orderData = $orderData->select(DB::raw("a.ship_code,a.ship_date,a.ototal,a.amt_recd,isnull(a.amt_discount,0) as amt_discount ,(a.ototal - a.amt_recd - isnull(a.amt_discount,0)) as amt_outstanding"))
        ->groupBy('a.ship_code','a.ship_date','a.ototal','a.amt_recd','a.amt_discount')->orderBy('ship_code','asc')->get();
//         $orderData = $orderData->select(DB::raw("a.ship_code,a.ototal,round(CAST(sum( CASE
//         WHEN b_tax = '稅外加'
//            THEN b.body_subtotal * ( 1 +  CAST(('0' + b_taxrate) AS float) )
//         ELSE b.body_subtotal
//    END) AS decimal(10,2)),2) as receivable"))->groupBy('a.ship_code','a.ototal')->orderBy('ship_code','asc')->get();
            // dd("123");
          //  dd($orderData);
		return $orderData;
	}



}
?>
