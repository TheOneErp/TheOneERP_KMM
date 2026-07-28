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

class RE204Controller extends Controller{
	static public function getRentDetails(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

		$getDetailData = DB::table('RE202_6231 as a')->select(DB::raw("TOP (100) PERCENT RE202_6232.id as r_a_no,a.contract_id,a.house_id,a.house_name,a.lessee,RE202_6232.rdate,('租金') as item,RE202_6232.rent_rout,RE202_6232.rtax,RE202_6232.rent_rin,ISNULL(RE202_6232.pamount,0) as pamount,RE202_6232.remarks"))
		->leftJoin('RE202_6232', 'a.id', '=', 'RE202_6232.parent_id')
		->leftJoin('RE201_6230', 'RE201_6230.house_id', '=', 'a.house_id')
		// ->leftJoin('RE201_47', 'RE201_46.id', '=', 'RE201_47.parent_id')
		->where('a.contract_st', '=', '承租中');

		$getChargeData = DB::table('RE202_6231 as a')->select(DB::raw("TOP (100) PERCENT RE202_6233.id as r_a_no,a.contract_id,a.house_id,a.house_name,a.lessee,RE202_6233.rdate,concat(RE202_6233.item_id,RE202_6233.item_name) as item,RE202_6233.rent_rout,RE202_6233.rtax,RE202_6233.rent_rin,ISNULL(RE202_6233.pamount,0) as pamount,RE202_6233.remarks"))
		->leftJoin('RE202_6233', 'a.id', '=', 'RE202_6233.parent_id')
		->leftJoin('RE201_6230', 'RE201_6230.house_id', '=', 'a.house_id')
		// ->leftJoin('RE201_47', 'RE201_46.id', '=', 'RE201_47.parent_id')
        ->where('a.contract_st', '=', '承租中')->orderBy('contract_id', 'ASC');

		if( !isset($data['type']) ){
			$getDetailData = $getDetailData
            // ->where('RE201_47.user_id','=',session("username"))
				->whereNotNull('RE202_6232.rdate')->whereRaw('(RE202_6232.rent_rin <> RE202_6232.pamount or RE202_6232.pamount is null or RE202_6232.pamount = 0 )');
			$getChargeData->whereNotNull('RE202_6233.rdate')
            // ->where('RE201_47.user_id','=',session("username"))
				->whereRaw('(RE202_6233.rent_rin <> RE202_6233.pamount or RE202_6233.pamount is null or RE202_6233.pamount = 0)');
		}

		if( !is_null($data['charge_fdate']) && $data['charge_fdate'] != '' ){
			$getDetailData = $getDetailData->where('rdate', '>=', $data['charge_fdate']);
			$getChargeData = $getChargeData->where('rdate', '>=', $data['charge_fdate']);
		}

		if( !is_null($data['charge_tdate']) && $data['charge_tdate'] != '' ){
			$getDetailData = $getDetailData->where('rdate', '<=', $data['charge_tdate']);
			$getChargeData = $getChargeData->where('rdate', '<=', $data['charge_tdate']);
		}

		if( !is_null($data['contract_fno']) && $data['contract_fno'] != '' ){
			$getDetailData = $getDetailData->where('contract_id', '>=', $data['contract_fno']);
			$getChargeData = $getChargeData->where('contract_id', '>=', $data['contract_fno']);
		}

		if( !is_null($data['contract_tno']) && $data['contract_tno'] != '' ){
			$getDetailData = $getDetailData->where('contract_id', '<=', $data['contract_tno']);
			$getChargeData = $getChargeData->where('contract_id', '<=', $data['contract_tno']);
		}

		if( !is_null($data['house_fid']) && $data['house_fid'] != '' ){
			$getDetailData = $getDetailData->where('a.house_id', '>=', $data['house_fid']);
			$getChargeData = $getChargeData->where('a.house_id', '>=', $data['house_fid']);
		}

		if( !is_null($data['house_tid']) && $data['house_tid'] != '' ){
			$getDetailData = $getDetailData->where('a.house_id', '<=', $data['house_tid']);
			$getChargeData = $getChargeData->where('a.house_id', '<=', $data['house_tid']);
		}
        if (VerifyUtil::pageVerifyConfirmation(6258)) {
            $getDetailData = $getDetailData->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
            $getChargeData = $getChargeData->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
        }
		$getChargeData = $getChargeData->union($getDetailData)->get();
		return $getChargeData;

	}


}
?>
