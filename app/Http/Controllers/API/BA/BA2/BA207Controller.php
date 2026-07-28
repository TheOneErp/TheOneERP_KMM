<?php
namespace App\Http\Controllers\API\BA\BA2;
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

class BA207Controller extends Controller{
	static public function transToOrder(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);
		DB::beginTransaction();
		$pageId = '53';
        //dd($data);
		$docu_number = $data['docu_number'];
		$OrdertData = DB::table('BA207_2198 as a')->select(DB::raw('a.*,a.data_options as dataoptions,b.*'))->leftJoin('BA207_2199 as b', 'b.parent_id','=','a.id')->where('docu_number','=',$docu_number)->whereNull('trans_order_num')->get();
		//dd($OrdertData);
		if( $OrdertData->count() ==  0 ){
			DB::rollback();
			$msgArr = ["text" => "警告:此報價單已轉客戶訂單，請確認","status" => true,"number"=>""];
			return $msgArr;
		}else{
			if (VerifyUtil::pageVerifyConfirmation(53)) {
				$data_options = DataUtil::convertToArray(json_decode($OrdertData->pluck('dataoptions')->first()));
				if( $data_options['verify']['level'] != 255 ){
					DB::rollback();
					$msgArr = ["text" => "警告:此報價單尚未通過審核無法轉出客戶訂單，請確認","status" => true,"number"=>""];
					return $msgArr;
				}
			}
		}

		$editable = '0';
		$number = CommonController::generateDocumentNumber($pageId,'client_order_code');
		DB::table('BA207_2198')->where('docu_number','=',$docu_number)
		->update(array('trans_order_num' => $number));
				//dd($number);
				$dataOptions = DB::table('BA207_2198')->where('docu_number','=',$docu_number)->select('data_options')->value('data_options');
				//dd($OrdertData);
				$id = DB::table('BA201_40')->insertGetId(
					[
                        'client_order_code' => $number,
						'client_code' => $OrdertData[0]->client_code,
						'client_name' => $OrdertData[0]->client_name,
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
						'remarks' => $OrdertData[0]->remarkss,
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
					$id = DB::table('BA201_41')->insertGetId(
						[
							'parent_id' => $headid,
							'product_code' =>$OrdertData[$i]->product_code,
							'product_name' =>$OrdertData[$i]->product_name,
							'body_num' =>$OrdertData[$i]->body_num,
							'body_price' =>$OrdertData[$i]->body_price,
							'o_body_price' =>$OrdertData[$i]->o_body_price,
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
							"updated_by"=> session("user_id")
						]
					);
				}
				//dd($headid);
			DB::commit();
			$msgArr = ["text" => "轉出成功","status" => false,];

		//dd($msgArr);
		return $msgArr;
	}

	static public function printOrder(Request $request){
		if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
			abort(400);
		//dd($data);
		$format = "pdf";
		$jasperName = "BA207";
		$docu_number = $data['docu_number'];


		$reportData = DB::table('BA207_2198 as a')
                ->select(DB::raw("*"))
				->leftJoin('BA207_2199 as b', 'b.parent_id','=','a.id')
				->where('docu_number','=',$docu_number)
				->orderBy('b.id')->get();
		//dd($reportData);
		$client_code = $reportData->pluck('client_code')->first();
		//dd($client_code);
		$customData = DB::table('BA102_37 as a')
		->select(DB::raw("a.client_code,a.client_name,b.phone,c.addr") )
		->leftJoin('BA102_38 as b', 'b.parent_id','=','a.id')
		->leftJoin('BA102_39 as c', 'c.parent_id','=','a.id')
		->where('client_code','=',$client_code)
		->orderBy('b.id')->first();
		//dd($customData);
			$company_name = "孔媽媽";
				$company_tel = "07-6285979";
			$company_fax = "";
			$company_addr = "820高雄市岡山區大莊里大莊路350號";
			$company_mail = "";

        foreach($reportData as $key=>$row ){
			$row->phone = $customData->phone;
			$row->addr = $customData->addr;
			$row->company_name = $company_name;
			$row->company_tel = $company_tel;
			$row->company_fax = $company_fax;
			$row->company_addr = $company_addr;
			$row->company_mail = $company_mail;
		}
		//dd($reportData);
		$reportClass = new ReportController;
		$res = $reportClass->export($format,$jasperName,$reportData);

		return $res;
	}




}
?>
