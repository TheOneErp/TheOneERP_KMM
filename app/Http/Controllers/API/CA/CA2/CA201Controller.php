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

class CA201Controller extends Controller{
	static public function changeProductCode(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
			abort(400);
			
		$vPageId = 49;
		$vtable = DB::table('AA202_30');
		if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
			$vtable = $vtable->where("AA202_30.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		$product = $vtable->where('product_code', '=', $data['product_code'])->get();
		
		$productArr = [];
		foreach( $product as $key=>$value ){
			$productArr['product_code'] = $value->product_code;
			$productArr['product_name'] = $value->product_name;
			$productArr['product_kind'] = $value->product_kind;
			$productArr['unit_code'] = $value->unit_code;
			$productArr['unit_name'] = $value->unit_name;
			$productArr['pro_cat_code'] = $value->pro_cat_code;
			$productArr['pro_cat_name'] = $value->pro_cat_name;
			$productArr['vendor_code'] = $value->vendor_code;
			$productArr['vendor_name'] = $value->vendor_name;
			$productArr['depot_code'] = $value->depot_code;
			$productArr['depot_name'] = $value->depot_name;
			$productArr['sell_price'] = $value->sell_price;
			$productArr['purchase_price'] = $value->purchase_price;
		}
		return $productArr;
	}
	
}
?>