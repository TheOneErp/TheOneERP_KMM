<?php
namespace App\Http\Controllers;

use App\Utils\PageUtil;

use App\Http\Controllers\Controller;
use App\Utils\TranslationUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Utils\VerifyUtil;

class CommonController extends Controller
{
    public static function generateDocumentNumber(int $page_id = -1,string $field_code = '',string $value = null,bool $withPrefix = true){
        if(is_null($value)){
            $pageData = PageUtil::getPageData($page_id);
            $result = '';
            $lastNumber = '0000';
            if(!is_null($pageData)){
                $pageCode = $pageData["page"]["page_code"];
                $tableName ="{$pageCode}_{$pageData['forms'][0]['form_id']}";
                $today = date('Ymd');
                $result = $withPrefix ? "$pageCode-" : '';
                if(Schema::hasColumn($tableName, $field_code)){
                    $lastNumber = DB::table($tableName)
                                    ->select($field_code)
                                    ->where($field_code,'like',"$pageCode-$today-%")
                                    ->orderBy($field_code,"DESC")
                                    ->first();
                    $lastNumber = is_null($lastNumber) ? 0 : (int) explode("-",$lastNumber->{$field_code})[2];
                    $lastNumber = sprintf('%04d',++$lastNumber);
                }
            }

            $result .= "$today-$lastNumber";

            return $result;
        }else{
            return $value;
        }

    }

	//批號
	public static function deleteBatchCode($type,$batch_code,$batch_no,$sourceBatchCode,$id){
		if( $type == "CA" ){
			$BatchTable = ['CA206_2071','CA206_2072'];
			$whereField = ['receive_code','receive_no'];
			$destTable = ['CA202_54','CA202_55'];
		}else{
			$BatchTable = ['DA204_2073','DA204_2074'];
			$whereField = ['finished_code','finished_no'];
			$destTable = ['DA202_56','DA202_57'];
		}
		//小視窗需刪除的資料
		$BatchBodyId = DB::table("{$BatchTable[0]} as a")
			 ->leftJoin("{$BatchTable[1]} as b", 'b.parent_id','=','a.id')
			 ->where('b.batch_code', '=', $sourceBatchCode)->where('b.batch_no', '=', $id)->get();
		//小視窗的ID
		foreach( $BatchBodyId as $bacthKey=>$batchVal ){
			DB::table($BatchTable[1])->where('id', '=', $batchVal->id)->delete();
		}
		// $purchaseData = DB::table("{$BatchTable[1]} as a")->where('a.parent_id', '=', $source_batch_code)->get();
		foreach( $BatchBodyId as $key => $val ){
			$lessNum = DB::table($BatchTable[1])->where('parent_id',$val->parent_id)->get();
			if( $type == "CA" ){
				if( count($lessNum) == 0 ){
					DB::table($destTable[1])->where('id',$val->receive_no)->update(['batch' => null]);
				}
			}else{
				if( count($lessNum) == 0 ){
					DB::table($destTable[1])->where('id',$val->finished_no)->update(['batch' => null]);
				}
			}
		}
	}
	//批號分批 出貨單需分完工進貨單
	/* public static function generateBatchCode($type,$product_code,$datas,$oder,$batch="unknow"){
		$batch_code = "";
		$batch_no = "";
		if( $type == "DA" ){
			$vPageId = 61;
			$prefix = "DA202_56";
			$batchTable = "DA202_57";
			$vtable = DB::table("{$prefix} as a");
			if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
				$vtable = $vtable->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
			}
			$purchaseData = $vtable
				->select(DB::raw("b.product_code,a.created_at,a.finished_code as batch_code,b.id as batch_no ,b.body_num,b.body_rate,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum,b.source_batch_code"))
				 ->leftJoin('DA202_57 as b', 'b.parent_id','=','a.id')
				 ->leftJoin('DA204_2073 as c', 'b.source_batch_code','=','c.id')
				 ->leftJoin('DA204_2074 as d', 'd.parent_id','=','c.id')
				->where('b.product_code','=',$product_code)
				->whereNotNull('b.source_batch_code')
				->groupBy('a.created_at','a.finished_code','b.id','b.body_num','b.body_rate','b.product_code','b.source_batch_code')
				->orderBy('a.created_at',$oder)->get();
		}else{
			$vPageId = 60;
			$prefix = "CA202_54";
			$batchTable = "CA202_55";
			$vtable = DB::table("{$prefix} as a");
			if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
				$vtable = $vtable->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
			}
			$purchaseData = $vtable
				->select(DB::raw("b.product_code,a.created_at,a.receive_code  as batch_code,b.id as batch_no,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum,b.source_batch_code"))
				 ->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
				 ->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
				 ->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
				->where('b.product_code','=',$product_code)
				->whereNotNull('b.source_batch_code')
				->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code','b.source_batch_code')
				->orderBy('a.created_at',$oder	)->get();
		}
		// dd($purchaseData);
		$index = 0;
		foreach( $purchaseData as $puKey=>$puVal ){
			if( $datas['num'] != 0 ){
				if( $puVal->diifnum != 0 ){
					$source_batch_code = self::checkBatchExist( $type,$puVal->batch_code,$puVal->batch_no,$product_code );
					if( $puVal->diifnum >= $datas['num']){
						// dd("s");
						DB::table($datas['littleTable'])
						->insert([
							'batch_code' => $datas['batch_code'], //出貨單號
							'batch_no' => $datas['batch_no'], //表身NO
							'undertakerday' => $datas['undertakerday'], //承辦時間
							'num' => $datas['num'], //數量 * 換算率
							'parent_id' => $source_batch_code //小視窗表頭ID
						]);
						$datas['num'] = 0;
						$batch_code = $puVal->batch_code;
						$batch_no = $puVal->batch_no;
						$TablePrefix= mb_substr($datas['batch_code'],0,2);
						// dd($batch_code,$batch_no,$TablePrefix,$puKey,$batch);
						if( $index == 0 && $batch == "unknow"){ //如果是第一筆且指定批號為空白
							switch ($TablePrefix) { //更新指定批號
								case "BA": //出貨單
									DB::table("BA202_53")
									->where('id', '=', $datas['batch_no'])
									->update([
										'batch_code' => $puVal->batch_code,
										'batch_no' => $puVal->batch_no,
									]);
									break;
								case "DA": //完工單
									DB::table("DA202_58")
									->where('id', '=', $datas['batch_no'])
									->update([
										'batch_code' => $puVal->batch_code,
										'batch_no' => $puVal->batch_no,
									]);
									break;
							}
						}else{
							switch ($TablePrefix) { //更新指定批號
								case "BA": //出貨單
									DB::table("BA202_53")
									->where('id', '=', $datas['batch_no'])
									->update([
										'batch_code' => null,
										'batch_no' => null,
									]);
									break;
								case "DA": //完工單
									DB::table("DA202_58")
									->where('id', '=', $datas['batch_no'])
									->update([
										'batch_code' => null,
										'batch_no' => null,
									]);
									break;
							}
						}
					}else{
						// dd("sf");
						$datas['num'] = $datas['num'] - $puVal->diifnum;
						DB::table($datas['littleTable'])
						->insert([
							'batch_code' => $datas['batch_code'], //出貨單號
							'batch_no' => $datas['batch_no'], //表身NO
							'undertakerday' => $datas['undertakerday'], //承辦時間
							'num' => $puVal->diifnum, //數量 * 換算率
							'parent_id' => $source_batch_code //小視窗表頭ID
						]);
						$batch = "batch";
					}
					//更新此張表單批號管理狀態
					// dd($batchTable,$puVal->batch_no,$source_batch_code);
					DB::table($batchTable)
					->where('id', '=', $puVal->batch_no)
					->update([
						'batch' => 'Y',
						'source_batch_code'=>$source_batch_code
					]);
					$index++;
				}
			}else{
				break;
			}

		}
		// dd("eee");
	} */
	//批號分批 出貨單不需分完工進貨單
	public static function generateBatchCode($isBA,$product_code,$datas,$depot,$oder,$batch="unknow"){
		$batch_code = "";
		$batch_no = "";
		/* 是否為出貨單 */
		if( $isBA ){
			$batchTable = "CA202_55";
			$first = DB::table("CA202_54 as a")
				->select(DB::raw("b.product_code,a.created_at,a.receive_code  as batch_code,b.id as batch_no,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum,b.source_batch_code"))
				->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
				->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
				->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
				->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
				->where('b.product_code','=',$product_code)
				->where('b.body_depot_code','=',$depot)
				// ->whereNotNull('b.source_batch_code')
				->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code','b.source_batch_code');

			$batchTable = "DA202_57";
			$purchaseData = DB::table("DA202_56 as a")
				->select(DB::raw("b.product_code,a.created_at,a.finished_code as batch_code,b.id as batch_no ,b.body_num,b.body_rate,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum,b.source_batch_code"))
				->leftJoin('DA202_57 as b', 'b.parent_id','=','a.id')
				->leftJoin('DA204_2073 as c', 'b.source_batch_code','=','c.id')
				->leftJoin('DA204_2074 as d', 'd.parent_id','=','c.id')
				->where('b.product_code','=',$product_code)
				->where('b.depot_code','=',$depot)
				// ->whereNotNull('b.source_batch_code')
				->groupBy('a.created_at','a.finished_code','b.id','b.body_num','b.body_rate','b.product_code','b.source_batch_code')
				->union($first)->orderBy('created_at',$oder)->get();
		}else{
			$vPageId = 60;
			$prefix = "CA202_54";
			$batchTable = "CA202_55";
			$vtable = DB::table("{$prefix} as a");
			if (VerifyUtil::pageVerifyConfirmation($vPageId)) {
				$vtable = $vtable->where("a.data_options", "LIKE", '%"verify":{%"level":255%');
			}
			$purchaseData = $vtable
				->select(DB::raw("b.product_code,a.created_at,a.receive_code  as batch_code,b.id as batch_no,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum,b.source_batch_code"))
				 ->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
				 ->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
				 ->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
				->where('b.product_code','=',$product_code)
				->where('b.body_depot_code','=',$depot)
				->whereNotNull('b.source_batch_code')
				->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code','b.source_batch_code')
				->orderBy('a.created_at',$oder	)->get();
		}
		$index = 0;
		foreach( $purchaseData as $puKey=>$puVal ){
			if( $datas['num'] != 0 ){
				if( $puVal->diifnum != 0 ){
					$type = $batchPrefix= mb_substr($puVal->batch_code,0,2);
					$source_batch_code = self::checkBatchExist( $type,$puVal->batch_code,$puVal->batch_no,$product_code );
					if( $type ==  'CA' ){ //指定為進貨單
						$littleTable = "CA206_2072";
						$batchTable = "CA202_55";
					}else{
						$littleTable = "DA204_2074";
						$batchTable = "DA202_57";
					}

					if( $puVal->diifnum >= $datas['num']){
						// dd("s");
						DB::table($littleTable)
						->insert([
							'batch_code' => $datas['batch_code'], //出貨單號
							'batch_no' => $datas['batch_no'], //表身NO
							'undertakerday' => $datas['undertakerday'], //承辦時間
							'num' => $datas['num'], //數量 * 換算率
							'parent_id' => $source_batch_code //小視窗表頭ID
						]);
						$datas['num'] = 0;
						$batch_code = $puVal->batch_code;
						$batch_no = $puVal->batch_no;
						$TablePrefix= mb_substr($datas['batch_code'],0,2);
						if( $index == 0 && $batch == "unknow"){ //如果是第一筆且指定批號為空白
							switch ($TablePrefix) { //更新指定批號
								case "BA": //出貨單
									DB::table("BA202_53")
									->where('id', '=', $datas['batch_no'])
									->update([
										'batch_code' => $puVal->batch_code,
										'batch_no' => $puVal->batch_no,
									]);
									break;
								case "DA": //完工單
									DB::table("DA202_58")
									->where('id', '=', $datas['batch_no'])
									->update([
										'batch_code' => $puVal->batch_code,
										'batch_no' => $puVal->batch_no,
									]);
									break;
							}
						}else{
							switch ($TablePrefix) { //更新指定批號
								case "BA": //出貨單
									DB::table("BA202_53")
									->where('id', '=', $datas['batch_no'])
									->update([
										'batch_code' => null,
										'batch_no' => null,
									]);
									break;
								case "DA": //完工單
									DB::table("DA202_58")
									->where('id', '=', $datas['batch_no'])
									->update([
										'batch_code' => null,
										'batch_no' => null,
									]);
									break;
							}
						}
					}else{
						// dd("sf");
						$datas['num'] = $datas['num'] - $puVal->diifnum;
						DB::table($littleTable)
						->insert([
							'batch_code' => $datas['batch_code'], //出貨單號
							'batch_no' => $datas['batch_no'], //表身NO
							'undertakerday' => $datas['undertakerday'], //承辦時間
							'num' => $puVal->diifnum, //數量 * 換算率
							'parent_id' => $source_batch_code //小視窗表頭ID
						]);
						$batch = "batch";
					}
					//更新此張表單批號管理狀態
					// dd($batchTable,$puVal->batch_no,$source_batch_code);
					DB::table($batchTable)
					->where('id', '=', $puVal->batch_no)
					->update([
						'batch' => 'Y',
						'source_batch_code'=>$source_batch_code
					]);
					$index++;
				}
			}else{
				break;
			}

		}
		// dd();
	}
	//批號分批 出貨單需分完工進貨單
	/* public static function saveBatchCode($type,$batch_code,$batch_no,$product_code,$data_code,$data_id,$undertakerday,$totalnum){
		$source_batch_code = "";
		$errorText = [];
		if( $type ==  'CA' ){
			$littleTable = "CA206_2072";
			$batchTable = "CA202_55";
			$purchaseData =DB::table("CA202_54 as a")
			->select(DB::raw("b.id,b.product_code,a.created_at,a.receive_code,b.id as receive_no ,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
			->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
			->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
			->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
			->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
			->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code')
			->orderBy('a.created_at','asc');

			// ->get();
			$purchaseData2 =DB::table("CA202_54 as a")
			->select(DB::raw("b.id,b.product_code,a.created_at,a.receive_code,b.id as receive_no ,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
			->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
			->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
			->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
			->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
			->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code')
			->orderBy('a.created_at','asc');
			$BatchCodeField = "a.receive_code";
		}else{ //完工
			$littleTable = "DA204_2074";
			$batchTable = "DA202_57";
			//根據批號抓取完工單小視窗ID
			$purchaseData = DB::table("DA202_56 as a")
			->select(DB::raw("b.id,b.product_code,a.created_at,a.finished_code,b.id as finished_no,b.body_num,b.body_rate,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
			->leftJoin('DA202_57 as b', 'b.parent_id','=','a.id')
			->leftJoin('DA204_2073 as c', 'b.source_batch_code','=','c.id')
			->leftJoin('DA204_2074 as d', 'd.parent_id','=','c.id')
			->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
			->groupBy('a.created_at','a.finished_code','b.id','b.body_num','b.body_rate','b.product_code')
			->orderBy('a.created_at','asc');
			// ->get();
			$purchaseData2 = DB::table("DA202_56 as a")
			->select(DB::raw("b.id,b.product_code,a.created_at,a.finished_code,b.id as finished_no,b.body_num,b.body_rate,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
			->leftJoin('DA202_57 as b', 'b.parent_id','=','a.id')
			->leftJoin('DA204_2073 as c', 'b.source_batch_code','=','c.id')
			->leftJoin('DA204_2074 as d', 'd.parent_id','=','c.id')
			->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
			->groupBy('a.created_at','a.finished_code','b.id','b.body_num','b.body_rate','b.product_code')
			->orderBy('a.created_at','asc');
			$BatchCodeField = "a.finished_code";
		}
		if( !empty($batch_code) ){
			$num = $totalnum;//要被存進小視窗的數量
			$singleData = $purchaseData->where($BatchCodeField,'=',$batch_code)->where('b.id','=',$batch_no)->first();
			// dd((float)$singleData->diifnum,$totalnum);
			//表身數量
			if( (float)$singleData->diifnum < (float)$totalnum ){
				$totalnum = $totalnum - $singleData->diifnum;
				$num = $singleData->diifnum;
				$sum = $purchaseData2->where('b.product_code','=',$product_code)->where($BatchCodeField,'<>',$batch_code)->where('b.id','<>',$batch_no)->get()->sum('diifnum');
				// dd($sum);
				if( $sum < $totalnum ){
					$errorText['text'] = '警告：此產品"'.$product_code.'"目前批號總數量不足';
					return $errorText;
				}else{//分批
					$datas = [
						'littleTable' => $littleTable,
						'batch_code' => $data_code, //表頭ID
						'batch_no' => $data_id, //表身ID
						'undertakerday' => $undertakerday, //承辦時間
						'num' => $totalnum, //數量 * 換算率
					];
					$source_batch_code = self::checkBatchExist( $type,$batch_code,$batch_no,$product_code );
					DB::table($littleTable)
					->insert([
						'batch_code' => $data_code, //表頭ID
						'batch_no' => $data_id, //表身ID
						'undertakerday' => $undertakerday, //承辦時間
						'num' => $num, //數量 * 換算率
						'parent_id' => $source_batch_code //小視窗表頭ID
					]);
					self::generateBatchCode($type,$product_code,$datas,"ASC","batch");

					//進貨單表身批號管理改為Y
					DB::table($batchTable)
					->where('id', '=', $batch_no)
					->update([
						'batch' => 'Y',
						'source_batch_code'=>$source_batch_code
					]);
				}
			}else{
				$source_batch_code = self::checkBatchExist( $type,$batch_code,$batch_no,$product_code );
				//小視窗表身新增一筆
				DB::table($littleTable)
				->insert([
					'batch_code' => $data_code, //表頭ID
					'batch_no' => $data_id, //表身ID
					'undertakerday' => $undertakerday, //承辦時間
					'num' => $num, //數量 * 換算率
					'parent_id' => $source_batch_code //小視窗表頭ID
				]);
				//進貨單表身批號管理改為Y
				DB::table($batchTable)
				->where('id', '=', $batch_no)
				->update([
					'batch' => 'Y',
					'source_batch_code'=>$source_batch_code
				]);
			}


		}else{
			$sum = $purchaseData2->where('b.product_code','=',$product_code)->get()->sum('diifnum');
			if( $sum < $totalnum ){

				$errorText['text'] = '警告：此產品"'.$product_code.'"目前批號總數量不足';
				return $errorText;
			}else{

				$datas = [
					'littleTable' => $littleTable,
					'batch_code' => $data_code, //表頭ID
					'batch_no' => $data_id, //表身ID
					'undertakerday' => $undertakerday, //承辦時間
					'num' => $totalnum, //數量 * 換算率
				];
				self::generateBatchCode($type,$product_code,$datas,"ASC");
			}
		}
		return $errorText;
	} */
	//批號分批 出貨單不需分完工進貨單
	public static function saveBatchCode($type,$batch_code,$batch_no,$product_code,$data_code,$data_id,$undertakerday,$totalnum,$depot,$isBA = false){
		$source_batch_code = "";
		$errorText = [];
		DB::beginTransaction();

		if( !empty($batch_code) ){
			if( $type ==  'CA' ){ //指定為進貨單
				$littleTable = "CA206_2072";
				$batchTable = "CA202_55";
				$singleData =DB::table("CA202_54 as a")
				->select(DB::raw("b.body_depot_code as depot,b.id,b.product_code,a.created_at,a.receive_code,b.id as receive_no ,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
				->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
				->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
				->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
				->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
				->where("a.receive_code",'=',$batch_code)->where('b.id','=',$batch_no)
				// ->where('b.body_depot_code','=',$depot)
				->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code','b.body_depot_code')
				->orderBy('a.created_at','asc')->first();
			}else{
				$littleTable = "DA204_2074";
				$batchTable = "DA202_57";
				//根據批號抓取完工單小視窗ID
				$singleData = DB::table("DA202_56 as a")
				->select(DB::raw("b.depot_code as depot,b.id,b.product_code,a.created_at,a.finished_code,b.id as finished_no,b.body_num,b.body_rate,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
				->leftJoin('DA202_57 as b', 'b.parent_id','=','a.id')
				->leftJoin('DA204_2073 as c', 'b.source_batch_code','=','c.id')
				->leftJoin('DA204_2074 as d', 'd.parent_id','=','c.id')
				->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
				->where("a.finished_code",'=',$batch_code)->where('b.id','=',$batch_no)
				// ->where('b.depot_code','=',$depot)
				->groupBy('a.created_at','a.finished_code','b.id','b.body_num','b.body_rate','b.product_code','b.depot_code')
				->orderBy('a.created_at','asc')->first();
			};

			$num = $totalnum;//要被存進小視窗的數量
			// dd((float)$singleData->diifnum,$totalnum);
			if( $singleData->depot == $depot ){
				/* 指定批號數量不足時 */
				if( (float)$singleData->diifnum < (float)$totalnum ){
					$totalnum = $totalnum - $singleData->diifnum;
					$num = $singleData->diifnum;
					/* 用來算剩餘數量的 */
					//是出貨單
					if( $isBA ){
						if( $type ==  'CA' ){
							$littleTable = "CA206_2072";
							$batchTable = "CA202_55";
						}else{
							$littleTable = "DA204_2074";
							$batchTable = "DA202_57";
						}
						$first = DB::table("CA202_54 as a")
						->select(DB::raw("b.body_depot_code as depot,b.id,b.product_code,a.created_at,a.receive_code as batch_code,b.id as receive_no ,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
						->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
						->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
						->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
						->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
						->where('b.product_code','=',$product_code)->where('a.receive_code','<>',$batch_code)->where('b.id','<>',$batch_no)
						->where('b.body_depot_code','=',$depot)
						->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code','b.body_depot_code');
						$sum =DB::table("DA202_56 as a")
						->select(DB::raw("b.depot_code as depot,b.id,b.product_code,a.created_at,a.finished_code as batch_code,b.id as finished_no,b.body_num,b.body_rate,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
						->leftJoin('DA202_57 as b', 'b.parent_id','=','a.id')
						->leftJoin('DA204_2073 as c', 'b.source_batch_code','=','c.id')
						->leftJoin('DA204_2074 as d', 'd.parent_id','=','c.id')
						->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
						->where('b.product_code','=',$product_code)->where('a.finished_code','<>',$batch_code)->where('b.id','<>',$batch_no)
						->where('b.depot_code','=',$depot)
						->groupBy('a.created_at','a.finished_code','b.id','b.body_num','b.body_rate','b.product_code','b.depot_code')
						->union($first)->get()->sum('diifnum');

					}else{
						$littleTable = "CA206_2072";
						$batchTable = "CA202_55";
						$sum =DB::table("CA202_54 as a")
						->select(DB::raw("b.id,b.product_code,a.created_at,a.receive_code,b.id as receive_no ,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
						->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
						->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
						->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
						->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
						->where('b.product_code','=',$product_code)->where('a.receive_code','<>',$batch_code)->where('b.id','<>',$batch_no)
						->where('b.body_depot_code','=',$depot)
						->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code')
						->orderBy('a.created_at','asc')->get()->sum('diifnum');
						// $sum = $purchaseData2->where('b.product_code','=',$product_code)->where($BatchCodeField,'<>',$batch_code)->where('b.id','<>',$batch_no)->get()->sum('diifnum');
					}
					if( $sum < $totalnum ){
						$errorText['text'] = '警告：此產品"'.$product_code.'"目前批號總數量不足';
						return $errorText;
					}else{//分批
						/* 現有的指定批號中小視窗表身新增一筆 */
						$source_batch_code = self::checkBatchExist( $type,$batch_code,$batch_no,$product_code );
						DB::table($littleTable)
						->insert([
							'batch_code' => $data_code, //單據號碼 BA202...
							'batch_no' => $data_id, //表身ID
							'undertakerday' => $undertakerday, //承辦時間
							'num' => $num, //數量 * 換算率
							'parent_id' => $source_batch_code //小視窗表頭ID
						]);
						//進貨單表身批號管理改為Y
						DB::table($batchTable)
						->where('id', '=', $batch_no)
						->update([
							'batch' => 'Y',
							'source_batch_code'=>$source_batch_code
						]);

						$datas = [
							'littleTable' => $littleTable,
							'batch_code' => $data_code, //單據號碼 BA202....
							'batch_no' => $data_id, //表身ID
							'undertakerday' => $undertakerday, //承辦時間
							'num' => $totalnum, //數量 * 換算率
						];
						self::generateBatchCode($isBA,$product_code,$datas,$depot,"ASC","batch");
					}
				}else{
					/* 現有的指定批號中小視窗表身新增一筆 */
					$source_batch_code = self::checkBatchExist( $type,$batch_code,$batch_no,$product_code );
					DB::table($littleTable)
					->insert([
						'batch_code' => $data_code, //表頭ID
						'batch_no' => $data_id, //表身ID
						'undertakerday' => $undertakerday, //承辦時間
						'num' => $num, //數量 * 換算率
						'parent_id' => $source_batch_code //小視窗表頭ID
					]);
					//進貨單表身批號管理改為Y
					DB::table($batchTable)
					->where('id', '=', $batch_no)
					->update([
						'batch' => 'Y',
						'source_batch_code'=>$source_batch_code
					]);
				}
			}else{
				$errorText['text'] = "警告：指定批號{$batch_code},批號NO{$batch_no}倉庫:與表身倉庫不同";
				return $errorText;
			}

		}else{ /* 沒有指定批號 */
			//是出貨單
			if( $isBA ){
				$littleTable = "DA204_2074";
				$batchTable = "DA202_57";
				$first = DB::table("CA202_54 as a")
				->select(DB::raw("b.id,b.product_code,a.created_at,a.receive_code as batch_code,b.id as receive_no ,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
				->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
				->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
				->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
				->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
				->where('b.product_code','=',$product_code)->where('a.receive_code','<>',$batch_code)->where('b.id','<>',$batch_no)
				->where('b.body_depot_code','=',$depot)
				->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code');
				$sum =DB::table("DA202_56 as a")
				->select(DB::raw("b.id,b.product_code,a.created_at,a.finished_code as batch_code,b.id as finished_no,b.body_num,b.body_rate,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
				->leftJoin('DA202_57 as b', 'b.parent_id','=','a.id')
				->leftJoin('DA204_2073 as c', 'b.source_batch_code','=','c.id')
				->leftJoin('DA204_2074 as d', 'd.parent_id','=','c.id')
				->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
				->where('b.product_code','=',$product_code)->where('a.finished_code','<>',$batch_code)->where('b.id','<>',$batch_no)
				->where('b.depot_code','=',$depot)
				->groupBy('a.created_at','a.finished_code','b.id','b.body_num','b.body_rate','b.product_code')
				->union($first)->get()->sum('diifnum');

			}else{
				$littleTable = "CA206_2072";
				$batchTable = "CA202_55";
				$sum =DB::table("CA202_54 as a")
				->select(DB::raw("b.id,b.product_code,a.created_at,a.receive_code,b.id as receive_no ,b.body_num,sum(d.num) as num,((b.body_num*b.body_rate) - sum(ISNULL(d.num,'0'))) as diifnum"))
				->leftJoin('CA202_55 as b', 'b.parent_id','=','a.id')
				->leftJoin('CA206_2071 as c', 'b.source_batch_code','=','c.id')
				->leftJoin('CA206_2072 as d', 'd.parent_id','=','c.id')
				->where("a.data_options", "LIKE", '%"verify":{%"level":255%')
				->where('b.product_code','=',$product_code)->where('a.receive_code','<>',$batch_code)->where('b.id','<>',$batch_no)
				->where('b.body_depot_code','=',$depot)
				->groupBy('a.created_at','a.receive_code','b.id','b.body_num','b.body_rate','b.product_code')
				->orderBy('a.created_at','asc')->get()->sum('diifnum');
				// $sum = $purchaseData2->where('b.product_code','=',$product_code)->where($BatchCodeField,'<>',$batch_code)->where('b.id','<>',$batch_no)->get()->sum('diifnum');
			}
			// $sum = $purchaseData2->where('b.product_code','=',$product_code)->get()->sum('diifnum');
			if( $sum < $totalnum ){
				$errorText['text'] = '警告：此產品"'.$product_code.'"目前批號總數量不足';
				return $errorText;
			}else{
				$datas = [
					'littleTable' => $littleTable,
					'batch_code' => $data_code, //表頭ID
					'batch_no' => $data_id, //表身ID
					'undertakerday' => $undertakerday, //承辦時間
					'num' => $totalnum, //數量 * 換算率
				];
				self::generateBatchCode($isBA,$product_code,$datas,$depot,"ASC");
			}
		}
		if(!empty($errorText)){
			DB::rollback();
			die();
		}else{
			DB::commit();
		}

		return $errorText;
	}
	//檢查小視窗是否存在 出貨單需分完工進貨單
	/* public static function checkBatchExist( $type,$batch_code,$batch_no,$product_code ){
		$error = "";
		if( $type ==  'CA' ){
			$source_batch_code = DB::table("CA202_54 as a")->leftJoin('CA202_55 as b', 'b.parent_id', '=' , 'a.id')
			->where('a.receive_code', '=', $batch_code)->where('b.id', '=', $batch_no)->where("a.data_options", "LIKE", '%"verify":{%"level":255%')->pluck('source_batch_code')->first();
			if( empty($source_batch_code) ){
				// $error = "此批號{$batch_code},NO{$batch_no}目前無法使用，請確認";
				$source_batch_code = DB::table("CA206_2071")
				->insertGetId([
					'receive_code' => $batch_code,
					'receive_no' => $batch_no,
					'product_code' => $product_code
				]);
			}else{
				$batchReceiveCode = DB::table('CA206_2071')->where('id', $source_batch_code)->pluck('receive_code')->first();
				if( is_null( $batchReceiveCode) ){
					DB::table('CA206_2071')
					->where('id', $source_batch_code)
					->update([
						'receive_code' => $batch_code,
						'receive_no' => $batch_no,
						'product_code' => $product_code
					]);
				}
			}
		}else{
			$source_batch_code = DB::table("DA202_56 as a")->leftJoin('DA202_57 as b', 'b.parent_id', '=' , 'a.id')
			->where('a.finished_code', '=', $batch_code)->where('b.id', '=', $batch_no)->where("a.data_options", "LIKE", '%"verify":{%"level":255%')->pluck('source_batch_code')->first();
			if( empty($source_batch_code) ){//因為小視窗沒有生成
				// $error = "此批號{$batch_code},NO{$batch_no}目前無法使用，請確認";//確認提示是否要做更改
				$source_batch_code = DB::table("DA204_2073")
				->insertGetId([
					'finished_code' => $batch_code,
					'finished_no' => $batch_no,
					'product_code' => $product_code
				]);
			}else{
				$batchReceiveCode = DB::table('DA204_2073')->where('id', $source_batch_code)->pluck('finished_no')->first();
				if( is_null( $batchReceiveCode) ){ //填入表頭的直
					//新增小視窗表頭
					DB::table('DA204_2073')
					->where('id', $source_batch_code)
					->update([
						'finished_code' => $batch_code,
						'finished_no' => $batch_no,
						'product_code' => $product_code
					]);
				}
			}
		}
		return $source_batch_code;
	} */
	//出貨單不需分完工進貨單
	public static function checkBatchExist( $type,$batch_code,$batch_no,$product_code ){
		$error = "";
		if( $type ==  'CA' ){
			$source_batch_code = DB::table("CA202_54 as a")->leftJoin('CA202_55 as b', 'b.parent_id', '=' , 'a.id')
			->where('a.receive_code', '=', $batch_code)->where('b.id', '=', $batch_no)->where("a.data_options", "LIKE", '%"verify":{%"level":255%')->pluck('source_batch_code')->first();
			if( empty($source_batch_code) ){
				// $error = "此批號{$batch_code},NO{$batch_no}目前無法使用，請確認";
				$source_batch_code = DB::table("CA206_2071")
				->insertGetId([
					'receive_code' => $batch_code,
					'receive_no' => $batch_no,
					'product_code' => $product_code
				]);
			}else{
				$batchReceiveCode = DB::table('CA206_2071')->where('id', $source_batch_code)->pluck('receive_code')->first();
				if( is_null( $batchReceiveCode) ){
					DB::table('CA206_2071')
					->where('id', $source_batch_code)
					->update([
						'receive_code' => $batch_code,
						'receive_no' => $batch_no,
						'product_code' => $product_code
					]);
				}
			}
		}else{
			$source_batch_code = DB::table("DA202_56 as a")->leftJoin('DA202_57 as b', 'b.parent_id', '=' , 'a.id')
			->where('a.finished_code', '=', $batch_code)->where('b.id', '=', $batch_no)->where("a.data_options", "LIKE", '%"verify":{%"level":255%')->pluck('source_batch_code')->first();
			if( empty($source_batch_code) ){//因為小視窗沒有生成
				// $error = "此批號{$batch_code},NO{$batch_no}目前無法使用，請確認";//確認提示是否要做更改
				$source_batch_code = DB::table("DA204_2073")
				->insertGetId([
					'finished_code' => $batch_code,
					'finished_no' => $batch_no,
					'product_code' => $product_code
				]);
			}else{
				$batchReceiveCode = DB::table('DA204_2073')->where('id', $source_batch_code)->pluck('finished_no')->first();
				if( is_null( $batchReceiveCode) ){ //填入表頭的直
					//新增小視窗表頭
					DB::table('DA204_2073')
					->where('id', $source_batch_code)
					->update([
						'finished_code' => $batch_code,
						'finished_no' => $batch_no,
						'product_code' => $product_code
					]);
				}
			}
		}
		return $source_batch_code;
	}

	//庫存
	public static function updateDepot($product_code,$depot_code,$operation,$totalnum,$combi = null){
        if($combi == null){
		DB::beginTransaction();
    	try {


			$oldDepot = DB::table('EA204_79')->where('product_code',$product_code)->where('depot_code', $depot_code)->lockForUpdate()->first();
			//加回來
			if( $operation == "addition" ){
				$oldnum = $oldDepot->num;
				$newnum = (float)round($oldnum + $totalnum,2);
				DB::table('EA204_79')
					->where('product_code',$product_code)
					->where('depot_code', $depot_code)
					->update(['num' => $newnum]);
			}else{ //減回來  subtraction
				$depotId = $oldDepot->id;
				$depotnum = $oldDepot->num;
				if( !is_null($depotnum) ){
					DB::table('EA204_79')
					->where('id', $depotId)
					->update([
						'num' => (float)round($depotnum - $totalnum,2),
					]);
				}
			}

	 	}catch (\Throwable $th) {
			DB::rollback();
		}
		DB::commit();
    }else{
        $data1 = DB::table('AA204_2224')->where('product_code',$product_code)->where('combi_code',$combi)->lockForUpdate()->first();
        $subdata =DB::table('AA204_2225')
        ->select('*')
        ->where('parent_id', $data1->id)
        ->get();
        foreach($subdata as $key => $val){
            $prototalnum = $val->body_num*$val->body_rate*$totalnum;
            if( $operation == "addition" ){
                CommonController::updateDepot($val->cont_code,$depot_code,"addition",$prototalnum);
            }else{
                CommonController::updateDepot($val->cont_code,$depot_code,"subtraction",$prototalnum);
            }

        }

    }
	}
    public static function AreUsedfordelete($code,string $table_code = '',string $field_code = '',string $pagecode = ''){
        $results = DB::select(
        "SELECT DISTINCT SUBSTRING(so.TABLE_NAME,1,CHARINDEX('_',so.TABLE_NAME)-1) as 't',so.TABLE_NAME as name,sc.COLUMN_NAME as colname FROM INFORMATION_SCHEMA.TABLES so
        INNER JOIN INFORMATION_SCHEMA.COLUMNS sc ON so.TABLE_NAME =sc.TABLE_NAME
        WHERE so.table_type='BASE TABLE'
        AND
        so.TABLE_NAME LIKE '%%'
        AND
        so.TABLE_NAME not LIKE '%".$table_code."%'
        AND
        sc.COLUMN_NAME LIKE '%".$field_code."%'"
        );
//        dd($results);
        $samet = [];
        foreach($results as $key => $val){
            if($val->t != 'HR399' && $val->t != 'HR398' && $val->t != 'HR397' && $val->t != 'GA299'){
                if( array_key_exists($val->t,$samet) ){
                    array_push($samet[$val->t],$results[$key]);
                }else{
                    $samet[$val->t] = [];
                    array_push($samet[$val->t],$results[$key]);
                }
            }
        }
        foreach($samet as $key=>$val){
            //dd($samet[$key][0]->name);
            $indata = DB::table($samet[$key][0]->name)
                    ->select('*')
                    ->where($samet[$key][0]->colname, $code)
                    ->get();
            if(count($indata) > 0){
                $translations = TranslationUtil::getTranslationByCode($samet[$key][0]->t);
                $pagecode = $pagecode."「".$translations."」";
            }else{
                if(!empty($samet[$key][1])){
                    $indata2 = DB::table($samet[$key][1]->name)
                            ->select('*')
                            ->where($samet[$key][1]->colname, $code)
                            ->get();
                    if(count($indata2) > 0){
                        $translations = TranslationUtil::getTranslationByCode($samet[$key][1]->t);
                        $pagecode = $pagecode."「".$translations."」";
                    }
                }
                if(!empty($samet[$key][2])){
                    $indata2 = DB::table($samet[$key][2]->name)
                            ->select('*')
                            ->where($samet[$key][2]->colname, $code)
                            ->get();
                    if(count($indata2) > 0){
                        $translations = TranslationUtil::getTranslationByCode($samet[$key][2]->t);
                        $pagecode = $pagecode."「".$translations."」";
                    }
                }

            }
        }
        return $pagecode;
    }
    public static function UsedHouse(string $table_code = '',$newcontract,$oldcontract){
        if($oldcontract == null){
            $wherestr = "WHERE a.created_at >=  d.created_at and d.contract_id = '".$newcontract."'";
        }else{
            $wherestr = "WHERE a.created_at >=  d.created_at and (d.contract_id = '".$newcontract."' or d.contract_id = '".$oldcontract."')";
        }
        //合約對應的房屋有被引用時需跳警告
        $used = DB::select( DB::raw("
        SELECT IIF( EXISTS(
             SELECT *
             FROM RE202_6231 as a
             left join (
                SELECT b.contract_id,c.house_id,b.created_at
                FROM ".$table_code." as b
                left join RE202_6231 as c on b.contract_id = c.contract_id
             ) as d on a.house_id = d.house_id ".$wherestr."
             ), 1, 0) as used
        ") );
        return $used[0]->used;
    }
}

?>
