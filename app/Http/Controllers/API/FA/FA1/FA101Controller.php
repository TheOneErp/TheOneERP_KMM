<?php
namespace App\Http\Controllers\API\FA\FA1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\System\SystemController as System;
use App\Utils\UserUtil;
use App\Utils\PageUtil;
use App\Utils\DataUtil;
use App\Utils\SessionUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;
use App\Utils\VerifyUtil;
use App\Http\Controllers\CommonController;

use Carbon\Carbon;

class FA101Controller extends Controller{

	// list
	static public function FA101_list(Request $request, $page_id){
        $requestData = $request->all();
        $paginationCount =  isset($request->paginationCount) && is_numeric($request->paginationCount) ? (int) $request->paginationCount : 10;
        // Get page data
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        if ($pageData == null) {
            abort(404);
        }
        // Get page options w/ custom options
        $pageOptions = PageUtil::getPageOptions($pageData);
        $headTable = $pageOptions['headTable'];

        //審核判斷
		$vtable = DB::table('BA201_40 as a');
		if (VerifyUtil::pageVerifyConfirmation(53)) {
			$vtable = $vtable->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
        $orderdatas = $vtable->select(DB::raw("a.client_order_code,a.client_code,a.client_name,a.currency,a.rate,a.tax,a.taxrate,b.id as no,b.product_code,b.product_name,CAST(b.body_num as DECIMAL(18,2)) as body_num,CAST(b.body_quantity as DECIMAL(18,2)) as body_quantity,b.unit_code,b.unit_name,b.body_rate,b.body_price,b.body_subtotal,b.body_cancel,a.advanceday,b.body_remarks as remarks,b.id as orderno,b.machining_code,b.discount"))->leftJoin('BA201_41 as b', 'a.id', '=', 'b.parent_id');
  


        // Filters
        if (isset($request->filters) && ValidationUtil::isJSONString($request->filters)) {
            $filters = json_decode($request->filters);

            $operators = ['=', '!=', '>', '>=', '<', '<=', 'like', 'not like'];
            $conditions = ['or', 'and'];
            $numberTypes = ['integer', 'decimal', 'boolean',];
            $datetimeTypes = ['time', 'date', 'datetime'];

            $groups = array_unique(array_map(function ($filter) {
                return $filter->group;
            }, $filters));

            foreach ($groups as $group) {
                $groupFilters = array_filter($filters, function ($filter) use ($group) {
                    return $filter->group == $group;
                });

                $orderdatas->orWhere(function ($groupQuery) use ($groupFilters, $pageOptions, $numberTypes, $datetimeTypes, $operators, $conditions) {
                    foreach ($groupFilters as $filter) {
                        if (isset($filter->field) && isset($filter->operator) && isset($filter->value) && isset($filter->condition)) // Check all field wrote.
                            if ((array_key_exists($filter->field, $pageOptions['headForm']['fields']) || $filter->field == "*") && in_array($filter->operator, $operators) && in_array($filter->condition, $conditions)) { // Check is valid data

                                $dataChecker = function ($field, $value) use ($numberTypes, $datetimeTypes, $pageOptions) {
                                    $status = true;
                                    $fieldType = $pageOptions['headForm']['fields'][$field]['field_type'];
                                    if ($fieldType == "button" || $fieldType == "reference_page") {
                                        $status = false;
                                    } else if (in_array($fieldType, $numberTypes)) {
                                        if (!is_numeric($value))
                                            $status = false;
                                    } else if (in_array($fieldType, $datetimeTypes)) {
                                        try {
                                            $result = Carbon::parse($value);
                                        } catch (\Exception $e) {
                                            $status = false;
                                        }
                                    }
                                    return $status;
                                };

                                $doWhere = function () use ($groupQuery, $filter, $pageOptions, $dataChecker) {
                                    if ($filter->field == "*") {
                                        foreach (array_keys($pageOptions['headForm']['fields']) as $key)
                                            if ($dataChecker($key, $filter->value))
                                                if ($filter->condition == "and" && $key != "delivery_status" && $key != "data_options")
                                                    $groupQuery->where($key, $filter->operator, $filter->value);
                                                else if ($filter->condition == "or" && $key != "delivery_status" && $key != "data_options")
                                                    $groupQuery->orWhere($key, $filter->operator, $filter->value);
                                    } else if ($filter->condition == "and") {
                                        if($filter->field == "delivery_status"){
                                            $groupQuery->where(DB::raw("Case when body_quantity<body_num then '未交完' when body_quantity>=body_num then '已交完' else null End"),$filter->operator,$filter->value);
                                        }else{
                                            $groupQuery->where($filter->field, $filter->operator, $filter->value);
                                        }

                                    } else if ($filter->condition == "or") {
                                        if($filter->field == "delivery_status"){
                                            $groupQuery->orWhere(DB::raw("Case when body_quantity<body_num then '未交完' when body_quantity>=body_num then '已交完' else null End"),$filter->operator,$filter->value);
                                        }else{
                                            $groupQuery->orWhere($filter->field, $filter->operator, $filter->value);
                                        }

                                    }
                                };

                                if ($filter->field == "*") {
                                    $doWhere();
                                } else if ($dataChecker($filter->field, $filter->value)) {
                                    $doWhere();
                                }
                            }
                    }
                });
//                dd($orderdatas->get());
            }
        }
        
        $orderdatas = $orderdatas
            ->whereRaw('CAST(b.body_num as DECIMAL(18,2)) > CAST(b.body_quantity as DECIMAL(18,2))')
            ->where('b.body_cancel','<>','Y')
            ->orderBy('a.advanceday','asc')
            ->paginate($paginationCount);
       // dd($orderdatas);
             // dd($orderdatas);
        //總倉產品數量
        $ddopt = DB::table('EA204_79 as d')->select(DB::raw("sum(d.[num]) as total,d.product_code"))
			->groupBy('product_code')
			->get();

        //客戶訂單數量減已交量
        //審核判斷
		$btable = DB::table('BA201_40 as a');
		if (VerifyUtil::pageVerifyConfirmation(53)) {
			$btable = $btable->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
		}
		$oorder = $btable->select(DB::raw("sum( (b.body_num-b.body_quantity)*b.body_rate) as remain_num,b.product_code"))
            ->leftJoin('BA201_41 as b', 'a.id', '=', 'b.parent_id')
			->groupBy('b.product_code')
			->get();


        foreach($orderdatas as $key=>$row ){
            $row->finished_day = '';
            $row->finished_light = null;
            $row->ship_day = '';
            $row->ship_light = null;
            $row->ship_backstatus = false;
            $row->undelvd_num = number_format((float)$row->body_num - (float)$row->body_quantity, 2);
            $sumproduct = $ddopt->where('product_code',$row->product_code)->pluck('total')->first();
            $orderproduct = $oorder->where('b.product_code',$row->product_code)->pluck('remain_num')->first();
            $total = (float)$sumproduct - (float)$orderproduct;
            $row->inventory = number_format($total, 2);
            /***** 完工 *****/
            //完工日期
            $finished = DB::table('DA202_57 as a')->select('a.parent_id','a.machining_code','a.order_no','b.undertakerday')
            ->leftJoin('DA202_56 as b', 'a.parent_id', '=', 'b.id')->where('order_no',$row->orderno);
            if (VerifyUtil::pageVerifyConfirmation(61)) {
                $finished = $finished->where("b.data_options", "LIKE", '%"verify":{%"level":255%');
            }
            $finished = $finished->orderBy('undertakerday', 'desc')->pluck('undertakerday')->first();
            if($finished != null){
                $row->finished_day = $finished;
            }

            //此訂單對應的加工單
            $machining = DB::table('DA201_44 as a')->select('a.machining_finished','b.body_num','b.body_quantity')
            ->leftJoin('DA201_45 as b', 'b.parent_id', '=', 'a.id')->where('b.order_no',$row->orderno);
            if (VerifyUtil::pageVerifyConfirmation(56)) {
                $machining = $machining->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
            }
            $machining = $machining->first();
//            dd((float)$machining->body_quantity);
            if(!empty($machining)){
                if((float)$machining->body_num <= (float)$machining->body_quantity){
                    if($finished <= $machining->machining_finished){
                        $row->finished_light = '2'; //綠
                    }else if($finished > $machining->machining_finished){
                        $row->finished_light = '3'; //黃
                    }
                }else if((float)$machining->body_num > (float)$machining->body_quantity){
                    if(date("Y-m-d") <= $machining->machining_finished){
                        $row->finished_light = '4'; //藍
                    }else if(date("Y-m-d") > $machining->machining_finished){
                        $row->finished_light = '5'; //紅
                    }
                }
            }

            /**************/
            /***** 出貨 *****/
            //出貨日期
            $ship = DB::table('BA202_53 as a')->select('a.parent_id','a.id as ship_no','a.order_no','b.ship_date')
            ->leftJoin('BA202_52 as b', 'a.parent_id', '=', 'b.id')->where('order_no',$row->orderno);
            if (VerifyUtil::pageVerifyConfirmation(59)) {
                $ship = $ship->where("b.data_options", "LIKE", '%"verify":{%"level":255%');
            }
            $ship = $ship->orderBy('ship_date', 'desc')->first();
            if(!empty($ship)){
                $row->ship_day = $ship->ship_date;
                //有對應的出貨退回單出貨的按鈕就要disable
                $shipback = DB::table('BA203_62')->select('*')->where('order_no',$row->orderno)->first();
                //dd($ship->ship_no);
                if(!empty($shipback)){
                    $row->ship_backstatus = true;
                }
            }
            
            if((float)$row->body_num <= (float)$row->inventory){
                
                    if(Carbon::parse($row->ship_day)->lte(Carbon::parse($row->advanceday))){
                        $row->ship_light = '2'; // 綠
                    }else{
                        $row->ship_light = '3'; // 黃
                    }
                    if((float)$row->body_num <= (float)$row->body_quantity){
                    $row->delivery_status = '已交完';
                     }else{
                        $row->delivery_status = '未交完';
                     }
               
            }else if((float)$row->body_num > (float)$row->inventory){
                if(Carbon::parse($row->ship_day)->lte(Carbon::parse($row->advanceday))){
                    $row->ship_light = '4'; //藍
                }else if(date("Y-m-d") > $row->advanceday){
                    $row->ship_light = '5'; //紅deposit
                }
                          if((float)$row->body_num <= (float)$row->body_quantity){
                    $row->delivery_status = '已交完';
                     }else{
                        $row->delivery_status = '未交完';
                     }
            }
            /**************/
        }
        //dd($orderdatas);

		return $orderdatas;
	}
}
?>
