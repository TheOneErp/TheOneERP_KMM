<?php
namespace App\Http\Controllers\API\DA\DA2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;
use App\Utils\VerifyUtil;

use Carbon\Carbon;

/*use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;*/

class DA202Controller extends Controller{
	static public function getStation(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);		
        $vPageId = 56;
		$prefix = 'DA201_44';
		$vtable = DB::table($prefix);
		if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
			$vtable = $vtable->where("{$prefix}.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		
		$station = $vtable->select(DB::raw("[choose] = NULL,DA201_45.id as machining_no,machining_code,machining_finished,product_code,product_name,body_num,unit_code,unit_name,body_rate,body_quantity,depot_code,depot_name,body_remarks,client_order_code,order_no"))->leftJoin('DA201_45', 'DA201_44.id', '=', 'DA201_45.parent_id');
        $station = $station->where('DA201_45.body_cancel','=','N')->whereRaw('body_quantity < body_num');
		
		if( !is_null($data['client_order_codeS']) && $data['client_order_codeS'] != '' ){
			$station = $station->where('client_order_code', '>=', $data['client_order_codeS']);
		}
		
		if( !is_null($data['client_order_codeE']) && $data['client_order_codeE'] != '' ){
			$station = $station->where('client_order_code', '<=', $data['client_order_codeE']);
		}
		
		if( !is_null($data['machining_codeS']) && $data['machining_codeS'] != '' ){
			$station = $station->where('machining_code', '>=', $data['machining_codeS']);
		}
		
		if( !is_null($data['machining_codeE']) && $data['machining_codeE'] != '' ){
			$station = $station->where('machining_code', '<=', $data['machining_codeE']);
		}
				
		if( !is_null($data['machining_finishedS']) && $data['machining_finishedS'] != '' ){
			$station = $station->where('machining_finished', '>=', $data['machining_finishedS']);
		}
		
		if( !is_null($data['machining_finishedE']) && $data['machining_finishedE'] != '' ){
			$station = $station->where('machining_finished', '<=', $data['machining_finishedE']);
		}
        
        if( !is_null($data['product_codeS']) && $data['product_codeS'] != '' ){
			$station = $station->where('product_code', '>=', $data['product_codeS']);
		}
		
		if( !is_null($data['product_codeE']) && $data['product_codeE'] != '' ){
			$station = $station->where('product_code', '<=', $data['product_codeE']);
		}
		$station = $station->where('station_code', '=', $data['station_code'])->get();
		return $station;
	}
    
    static public function getStationComponent(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		
		$vPageId = 56;
		$prefix = 'DA201_44';
		$vtable = DB::table($prefix);
		if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
			$vtable = $vtable->where("{$prefix}.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		$component = $vtable->select(DB::raw("[choose] = NULL,DA201_45.id,component_code,component_name,component_num,component_unit,component_unitname,component_rate,component_depot,component_depotname,component_remarks"))->leftJoin('DA201_45', 'DA201_44.id', '=', 'DA201_45.parent_id')->leftJoin('DA201_46', 'DA201_45.id', '=', 'DA201_46.parent_id');
		
        $component = $component->where('DA201_45.id', '=', $data['station_id'])->get();

		return $component;

	}
    
    static public function getkeginfo(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

		$already = [];
		$ba205keg = DB::table('BA205_72')->select('*')->where('keg', $data['keg'])->get();
		$da202keg = DB::table('DA202_57')->select('*')->where('keg', $data['keg'])->get();
		$ea206keg = DB::table('EA206_1070')->select('*')->where('keg', $data['keg'])->get();
		if(count($ba205keg)>0){
            array_push($already,'【出貨單】');
        }
        if(count($da202keg)>1){
            array_push($already,'【完工單】');
        }
        if(count($ea206keg)>0){
            array_push($already,'【桶號庫存調整單】');
        }
//dd($already);
		return $already;

	}
	
    //看板轉出完工
    static public function addfinished(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
//		dd($data);
		//將此客戶訂單對應的加工單抓出來
		$vPageId = 56;
		$vtable = DB::table('DA201_44 as a');
		if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
			$vtable = $vtable->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}

        $machiningdata = $vtable
                    ->select('a.station_code','a.station_name','a.machining_code','b.id as machining_no','b.product_code','b.product_name','b.body_num','b.body_quantity','b.unit_code','b.unit_name','b.body_rate','b.depot_code','b.depot_name','b.body_remarks','b.client_order_code','b.order_no')
                    ->leftJoin('DA201_45 as b', 'a.id', '=', 'b.parent_id')
                    ->leftJoin('DA201_46 as c', 'b.id', '=', 'c.parent_id')
                    ->where('b.order_no',$data['orderno'])
                    ->get();
//        dd($machiningdata);
        $Arr = [];
        if(count($machiningdata)>0){
            $Arr['status'] = 1;
            $Arr['machiningdata'] = $machiningdata;

            $machiningcomp = DB::table('DA201_46')
                    ->select('*')
                    ->where('parent_id',$machiningdata[0]->machining_no)
                    ->get();
            if(count($machiningcomp)>0){
                $Arr['machiningcomp'] = $machiningcomp;
            }
        }else{
            $Arr['status'] = 0;
        }

		return $Arr;

	}
}
?>