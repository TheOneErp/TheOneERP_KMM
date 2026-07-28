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

/*use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\DatabaseUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;*/

class BA203Controller extends Controller
{
	static public function getShipOrder(Request $request)
	{
		if (ValidationUtil::isJSONString($request->getContent()))
			$data = DataUtil::convertToArray(json_decode($request->getContent()));
		else
			abort(400);

		$backData = DB::table('BA203_61 as a')
			->select(DB::raw("b.ship_no,b.ship_code,sum(b.body_num * b.body_rate) as body_num"))
			->leftJoin('BA203_62 as b', 'b.parent_id', '=', 'a.id')
			->groupBy('b.ship_code', 'b.ship_no')
			->whereNotNull('b.ship_code')
			->get();
		$vPageId = 59;
		$prefix = 'BA202_52';
		$vtable = DB::table($prefix);
		if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
			$vtable = $vtable->where("{$prefix}.data_options", "LIKE", '%"verify":{%"level":255%');
		}

		$custom = $vtable->select(DB::raw("[choose] = NULL,ship_code, order_no,ship_no = BA202_53.id,ship_date,body_num,o_body_price,body_price,body_rate,body_subtotal,client_order_code,product_code,product_name,BA202_53.remarks,unit_code,unit_name,body_depot_code as depot_code,body_depot_name as depot_name,combi_code,combi_name,BA202_53.discount"))->leftJoin('BA202_53', 'BA202_52.id', '=', 'BA202_53.parent_id');
		//		$custom = $custom->where('BA202_53.body_cancel','=','N')->whereRaw('body_quantity < body_num');

		if (!is_null($data['ship_dateS']) && $data['ship_dateS'] != '') {
			$custom = $custom->where('ship_date', '>=', $data['ship_dateS']);
		}

		if (!is_null($data['ship_dateE']) && $data['ship_dateE'] != '') {
			$custom = $custom->where('ship_date', '<=', $data['ship_dateE']);
		}

		if (!is_null($data['ship_codeS']) && $data['ship_codeS'] != '') {
			$custom = $custom->where('ship_code', '>=', $data['ship_codeS']);
		}

		if (!is_null($data['ship_codeE']) && $data['ship_codeE'] != '') {
			$custom = $custom->where('ship_code', '<=', $data['ship_codeE']);
		}

		if (!is_null($data['product_codeS']) && $data['product_codeS'] != '') {
			$custom = $custom->where('product_code', '>=', $data['product_codeS']);
		}

		if (!is_null($data['product_codeE']) && $data['product_codeE'] != '') {
			$custom = $custom->where('product_code', '<=', $data['product_codeE']);
		}
		$custom = $custom->where('client_code', '=', $data['client_code'])->get();

		foreach ($custom as $key => $value) {
			//$backData
			$backNum = $backData->where('ship_code', '=', $value->ship_code)->where('ship_no', '=', $value->ship_no)->pluck('body_num')->first();
			if ($backNum) {
				$value->body_num = (string)round((($value->body_num *  $value->body_rate) - $backNum) / $value->body_rate, 2);
			}
		}
		return $custom;
	}
}
