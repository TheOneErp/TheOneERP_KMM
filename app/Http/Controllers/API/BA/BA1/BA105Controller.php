<?php
namespace App\Http\Controllers\API\BA\BA1;

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

class BA105Controller extends Controller{
    static public function getaddr(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

        $orderData = DB::table('BA102_37 as a')
        ->leftJoin('BA102_39 as b', 'a.id', '=', 'b.parent_id')
        ->leftJoin('BA103_82 as c', 'a.client_code', '=', 'c.client_code')
        ->leftJoin('BA102_38 as d', 'a.id', '=', 'd.parent_id');

        if( !is_null($data['enter_addr']) && $data['enter_addr'] != '' ){
			$orderData = $orderData->where('b.addr','like','%'.$data['enter_addr'].'%');
		}
        if( !is_null($data['enter_product']) && $data['enter_product'] != '' ){
			$orderData = $orderData->where('c.product_name','like','%'.$data['enter_product'].'%');
		}
        $orderData = $orderData->select(DB::raw("a.client_code,a.client_name,b.addr,c.product_code,c.product_name,d.phone"))
        ->groupBy('a.client_code','a.client_name','a.client_catname','b.addr','c.product_code','c.product_name','d.phone')
        ->orderBy('a.client_code','asc')
        ->orderBy('c.product_code','asc')
        ->get();
            // dd("123");
            // dd($orderData);
		return $orderData;
	}



}
?>
