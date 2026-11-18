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
            // 請款單(BA213)沖帳：金額於儲存沖帳紀錄時回寫請款單，這裡不異動出貨單
            if (strpos($row, 'BA213') === 0) continue;
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
    // 應收沖帳：改抓請款單(BA213)金額；日期條件為「請款日期」起迄
    static public function getReceivable(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

        $q = DB::table('BA213_6273 as a')
            ->where('a.client_code','=',$data['client_code']);
        if( !is_null($data['ship_date_s']) && $data['ship_date_s'] != '' ){
			$q->where('a.invoice_date','>=',$data['ship_date_s']);
		}
        if( !is_null($data['ship_date_e']) && $data['ship_date_e'] != '' ){
			$q->where('a.invoice_date','<=',$data['ship_date_e']);
		}
        $disc = "(SELECT ISNULL(SUM(d.amt_discount),0) FROM BA208_4225 d WHERE d.source_code = a.invoice_code AND d.choose = 1)";
        $q->select(DB::raw("a.invoice_code as ship_code, CONVERT(varchar(10), a.invoice_date, 23) as ship_date,
            ISNULL(a.ototal,0) as ototal, ISNULL(a.amt_recd,0) as amt_recd, $disc as amt_discount,
            (ISNULL(a.ototal,0) - ISNULL(a.amt_recd,0) - $disc) as amt_outstanding"))
          ->whereRaw("(ISNULL(a.ototal,0) - ISNULL(a.amt_recd,0) - $disc) > 0");
        return $q->orderBy('a.invoice_code','asc')->get();
	}


}
?>
