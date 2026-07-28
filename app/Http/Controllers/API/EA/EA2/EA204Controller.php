<?php
namespace App\Http\Controllers\API\EA\EA2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;
use App\Utils\VerifyUtil;

use Carbon\Carbon;

class EA204Controller extends Controller{
	static public function getsafenum(Request $request){
        if (ValidationUtil::isJSONString($request->getContent()))
        $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
        abort(400);
        $lastmonth=date("Y-m-d",strtotime("last day of previous month"));
        //dd($lastmonth);
        $shipments = DB::table('BA202_52 as a')
                    ->leftJoin('BA202_53 as b', 'a.id', '=', 'b.parent_id')
                    ->where('product_code','=', $data["product_code"])
                    ->where('ship_date','<=',$lastmonth)
                    ->select(DB::raw('sum(body_num) as sum'),DB::raw("MONTH(ship_date) as month"),DB::raw("YEAR(ship_date) as year"))
                    ->orderby(DB::raw("YEAR(ship_date)"),'DESC')
                    ->orderby(DB::raw("MONTH(ship_date)"),'DESC')
                    ->groupBy(DB::raw("MONTH(ship_date)"),DB::raw("YEAR(ship_date)"))
                    ->limit(12)
                    ->get();
     
      
                
         
        return $shipments;
	}
    
    
	
}
?>