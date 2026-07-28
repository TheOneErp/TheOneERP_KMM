<?php
namespace App\Http\Controllers\API\RE\RE2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;

use Carbon\Carbon;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;

class RE203Controller extends Controller{
	static public function checkPayment(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		$getPayment = DB::table('RE202_6233 as a')->select(DB::raw("pamount,RE203_bodyno"))->where('docu_number','=',$data['docu_number'])
			->where('pamount','<>',0);
        if (VerifyUtil::pageVerifyConfirmation(6258)) {
            $getPayment = $getPayment->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
        }
        $getPayment = $getPayment->get();
		return $getPayment;
	}
}
?>
