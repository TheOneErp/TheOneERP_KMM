<?php
namespace App\Http\Controllers\API\AA\AA2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;
use App\Utils\VerifyUtil;

use Carbon\Carbon;

class AA202Controller extends Controller{
	static public function changeUnitCode(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		$units = DB::table('AA103_21');
		$vPageId = 28;
		if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
			$units = $units->where("AA103_21.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		$unit = $units->where('unit_code', '=', $data['unit'])->get();
		
		$unitsArr = [];
		foreach( $unit as $key=>$value ){
			$unitsArr['unit_code'] = $value->unit_code;
			$unitsArr['unit_name'] = $value->unit_name;
			$unitsArr['remarks'] = $value->remarks;
		}
		return $unitsArr;
		
	}
	
}
?>