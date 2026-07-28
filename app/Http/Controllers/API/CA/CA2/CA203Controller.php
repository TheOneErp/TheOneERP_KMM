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

/*use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;*/

class CA203Controller extends Controller{
	static public function getCompanyReceive(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
			abort(400);
		$vPageId = 60;
		$prefix = 'CA202_54';
		$vtable = DB::table($prefix);
		if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
			$vtable = $vtable->where("{$prefix}.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		$custom = $vtable->select(DB::raw("[choose] = NULL, CA202_55.id as receive_no,       receive_code,receive_day,product_code,product_name,body_num,unit_code,unit_name,body_rate,body_price,body_subtotal,body_depot_code as depot_code,body_depot_name as depot_name,body_remarks as remarks,purchase_code,purchase_no,CA202_55.discount"))->leftJoin('CA202_55', 'CA202_54.id', '=', 'CA202_55.parent_id');

		if( !is_null($data['receive_codeS']) && $data['receive_codeS'] != '' ){
			$custom = $custom->where('receive_code', '>=', $data['receive_codeS']);
		}
		
		if( !is_null($data['receive_codeE']) && $data['receive_codeE'] != '' ){
			$custom = $custom->where('receive_code', '<=', $data['receive_codeE']);
		}
		
		if( !is_null($data['receive_dayS']) && $data['receive_dayS'] != '' ){
			$custom = $custom->where('receive_day', '>=', $data['receive_dayS']);
		}
		
		if( !is_null($data['receive_dayE']) && $data['receive_dayE'] != '' ){
			$custom = $custom->where('receive_day', '<=', $data['receive_dayE']);
		}
				
		if( !is_null($data['product_codeS']) && $data['product_codeS'] != '' ){
			$custom = $custom->where('product_code', '>=', $data['product_codeS']);
		}
		
		if( !is_null($data['product_codeE']) && $data['product_codeE'] != '' ){
			$custom = $custom->where('product_code', '<=', $data['product_codeE']);
		}
		$custom = $custom->where('vendor_code', '=', $data['vendor_code'])->get();
		return $custom;
	}
	
}
?>