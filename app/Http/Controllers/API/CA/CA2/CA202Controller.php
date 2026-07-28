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

class CA202Controller extends Controller{
	static public function getCompanyOrder(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

		$vPageId = 55;
		$prefix = 'CA201_42';
		$vtable = DB::table($prefix);
		//dd($data);
		if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
			$vtable = $vtable->where("{$prefix}.data_options", "LIKE", '%"verify":{%"level":255%');
		}

		$custom = $vtable->select(DB::raw("[choose] = NULL, CA201_43.id as purchase_no,purchase_code,advanceday,product_code,product_name,body_num,unit_code,unit_name,body_rate,body_quantity,body_price,body_subtotal,body_depot_code as depot_code,body_depot_name as depot_name,body_remarks as remarks,CA201_43.discount"))->leftJoin('CA201_43', 'CA201_42.id', '=', 'CA201_43.parent_id');
        $custom = $custom->where('CA201_43.body_cancel','=','N')->whereRaw('body_quantity < body_num');


		if( !is_null($data['advancedayS']) && $data['advancedayS'] != '' ){
			$custom = $custom->where('advanceday', '>=', $data['advancedayS']);
		}

		if( !is_null($data['advancedayE']) && $data['advancedayE'] != '' ){
			$custom = $custom->where('advanceday', '<=', $data['advancedayE']);
		}

		if( !is_null($data['purchase_codeS']) && $data['purchase_codeS'] != '' ){
			$custom = $custom->where('purchase_code', '>=', $data['purchase_codeS']);
		}

		if( !is_null($data['purchase_codeE']) && $data['purchase_codeE'] != '' ){
			$custom = $custom->where('purchase_code', '<=', $data['purchase_codeE']);
		}

		if( !is_null($data['product_codeS']) && $data['product_codeS'] != '' ){
			$custom = $custom->where('product_code', '>=', $data['product_codeS']);
		}

		if( !is_null($data['product_codeE']) && $data['product_codeE'] != '' ){
			$custom = $custom->where('product_code', '<=', $data['product_codeE']);
		}
		if( !is_null($data['product_codeE']) && $data['product_codeE'] != '' ){
			$custom = $custom->where('product_code', '<=', $data['product_codeE']);
		}
			$custom = $custom->where('vendor_code', '=', $data['vendor_code'])->get();
		return $custom;

	}
    static public function getProduct1(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		// dd($data);
		/**判斷是否需要審核 */
		$vPageId = 77;
		$prefix = 'AA202_30';
		$vtable = DB::table($prefix);
		$Product = $vtable->select(DB::raw("[choose] = NULL,product_code,product_name,unit_code,unit_name,1 as body_rate,pro_cat_code,pro_cat_name,purchase_price as body_price"));
		$Product = $Product->where('disable','<>','1');
        $Product->where(function($Product) use ($data){
            return $Product->where('product_code', 'like', '%'.$data['serach'].'%')
            ->orwhere('product_name', 'like', '%'.$data['serach'].'%')
            ->orwhere('unit_code', 'like', '%'.$data['serach'].'%')
            ->orwhere('unit_name', 'like', '%'.$data['serach'].'%')
            ->orwhere('pro_cat_code', 'like', '%'.$data['serach'].'%')
            ->orwhere('pro_cat_name', 'like', '%'.$data['serach'].'%');
            });
        $Product=$Product->get();


		return $Product;

	}
    static public function getVendorOrder1(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		// dd($data);
		/**判斷是否需要審核 */
		$vPageId = 77;
		// $prefix = 'BA103_82';
		// $vtable = DB::table($prefix);
		// $custom = $vtable->select(DB::raw("[choose] = NULL,body_price,body_rate,product_code,product_name,unit_code,unit_name"));
		// $custom = $custom->where('client_code', '=', $data['client_code'])->get();

        $getdata=DB::table('CA103_83')
        ->selectraw('*,ROW_NUMBER() over (partition by product_code order by receive_code DESC) sn')
        ->where('vendor_code', '=', $data['vendor_code']);
        $bindings = $getdata->getBindings();

            $sql = str_replace('?', "'%s'", $getdata->toSql());

            $sql = sprintf($sql, ...$bindings);

            $save1=DB::table(DB::raw("($sql) as R"))
            ->selectraw('[choose] = NULL,body_price,body_rate,product_code,product_name,unit_code,unit_name,receive_day')
            ->where('R.sn', '=', 1)->get();
                // dd($save1);
		return $save1;

	}

    static public function havecited(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
        $receiveBack = DB::table('CA203_64')->get();
		$receiveBackData = $receiveBack
                     ->where('receive_code', $data['headid'])
                     ->all();
		$editable = '0';
		if(count($receiveBackData)>0){
			$editable =  '1';
		}
		return array("status"=>$editable,"res"=>$receiveBackData);

	}

}
?>
