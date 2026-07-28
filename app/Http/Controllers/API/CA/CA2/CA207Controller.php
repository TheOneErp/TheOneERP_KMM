<?php
namespace App\Http\Controllers\API\CA\CA2;
use App\Http\Controllers\Base\ReportController;
use App\Http\Controllers\CommonController;
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

class CA207Controller extends Controller{
	static public function transToOrder1(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		DB::beginTransaction();
		$pageId = '55';
        //dd($data);
		$docu_number = $data['docu_number'];
		$OrdertData = DB::table('CA207_2222 as a')->select(DB::raw('a.*,a.data_options as dataoptions,b.*'))->leftJoin('CA207_2223 as b', 'b.parent_id','=','a.id')->where('docu_number','=',$docu_number)->whereNull('trans_order_num')->get();
		//dd($OrdertData);
		if( $OrdertData->count() ==  0 ){
			DB::rollback();
			$msgArr = ["text" => "警告:此報價單已轉採購單，請確認","status" => true,"number"=>""];
			return $msgArr;
		}else{
			if (VerifyUtil::pageVerifyConfirmation(55)) {
				$data_options = DataUtil::convertToArray(json_decode($OrdertData->pluck('dataoptions')->first()));
				if( $data_options['verify']['level'] != 255 ){
					DB::rollback();
					$msgArr = ["text" => "警告:此報價單尚未通過審核無法轉出採購單，請確認","status" => true,"number"=>""];
					return $msgArr;
				}
			}
		}

		$editable = '0';
		$number = CommonController::generateDocumentNumber($pageId,'purchase_code');
		DB::table('CA207_2222')->where('docu_number','=',$docu_number)
		->update(array('trans_order_num' => $number));
				//dd($number);
				$dataOptions = DB::table('CA207_2222')->where('docu_number','=',$docu_number)->select('data_options')->value('data_options');
				//dd($OrdertData);
				$id = DB::table('CA201_42')->insertGetId(
					[
                        'purchase_code' => $number,
						'vendor_code' => $OrdertData[0]->vendor_code,
						'vendor_name' => $OrdertData[0]->vendor_name,
						'currency' => $OrdertData[0]->currency,
						'rate' => $OrdertData[0]->rate,
						'tax' => $OrdertData[0]->tax,
						'taxrate' => $OrdertData[0]->taxrate,
						'osubtotal' => $OrdertData[0]->osubtotal,
						'ssubtotal' => $OrdertData[0]->ssubtotal,
						'otax' => $OrdertData[0]->otax,
						'stax' => $OrdertData[0]->stax,
						'ototal' => $OrdertData[0]->ototal,
						'stotal' => $OrdertData[0]->stotal,
						'undertaker' => $OrdertData[0]->undertaker,
						'undertakername' =>  $OrdertData[0]->undertakername,
						'advanceday' => $OrdertData[0]->due_date,
						'remarks' => $OrdertData[0]->remarks,

						'undertakerday' => Carbon::now()->format('Y-m-d'),
						"created_at" =>  Carbon::now(), # new \Datetime()
						"updated_at" => Carbon::now(),  # new \Datetime()
						"created_by"=> session("user_id"),
						"updated_by"=> session("user_id"),
						'data_options' => $dataOptions,
						"source_code" => $OrdertData[0]->docu_number
					]
				);
				$headid=$id;
				//dd($headid);
				for($i=0;$i<=count($OrdertData)-1;$i++)
				{
					$id = DB::table('CA201_43')->insertGetId(
						[
							'parent_id' => $headid,
							'product_code' =>$OrdertData[$i]->product_code,
							'product_name' =>$OrdertData[$i]->product_name,
							'body_num' =>$OrdertData[$i]->body_num,
							'body_price' =>$OrdertData[$i]->body_price,
							'discount' =>$OrdertData[$i]->discount,
							'body_rate' =>$OrdertData[$i]->body_rate,
							'body_remarks' =>$OrdertData[$i]->body_remarks,
							'body_subtotal' =>$OrdertData[$i]->body_subtotal,
							'unit_code' =>$OrdertData[$i]->unit_code,
							'unit_name' =>$OrdertData[$i]->unit_name,
							'body_cancel' => 'N',
							"created_at" =>  Carbon::now(), # new \Datetime()
							"updated_at" => Carbon::now(),  # new \Datetime()
							"created_by"=> session("user_id"),
							"updated_by"=> session("user_id"),

						]
					);
				}
				//dd($headid);
			DB::commit();
			$msgArr = ["text" => "轉出成功","status" => false,];

		//dd($msgArr);
		return $msgArr;
	}






}
?>
