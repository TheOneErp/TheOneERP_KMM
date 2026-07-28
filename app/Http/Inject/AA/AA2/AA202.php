<?php

namespace App\Http\Inject\AA\AA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class AA202 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
        if( !array_key_exists('status',$data) ){
			$page_id = 49;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
        $tmpArr = [];


        //如果是修改的話
        if($data['status'] == 'update'){
            //記憶現在的產品代碼
            $product_code = $data['data']['product_code'];
            //撈該表頭 id的舊資料
            $olddata = DB::table('AA202_30')->where('id', $data['data']['id'])->first();
            //如果現在的表頭 的 產品代碼 與 舊資料 的 產品代碼 不同， 底下的單據都要更新產品代碼。
            if($product_code != $olddata->product_code){
                DB::table('AA204_2224')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('AA204_2225')->where('cont_code', $olddata->product_code)
                ->update( [
                    'cont_code' => $product_code,
                ]);
                DB::table('BA103_82')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('BA105_6251')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('BA207_2199')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('BA201_41')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('BA202_53')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('BA210_5225')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('BA204_66')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('BA209_4229')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('BA203_62')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('BA206_74')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('CA103_83')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('CA201_43')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('CA202_55')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('CA210_5227')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('CA204_76')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('CA209_4231')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('CA203_64')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('CA205_78')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('EA201_51')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('EA202_60')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('EA204_79')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('EA301_6247')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
                DB::table('EA301_6248')->where('product_code', $olddata->product_code)
                ->update( [
                    'product_code' => $product_code,
                ]);
            }
        }
        return $tmpArr;
    }
    static public function atSave(&$data,$verify = false){
        if($verify){
			$page_id = 49;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
            $data = PageUtil::getData($pageData, $data['id']);
            $data['status'] = 'add';
			$warm = [];
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        //dd($data);
        $depotdata = DB::table('EA101_26')
                 ->select('depot_code','depot_name')
                 ->get();
        if($data['status'] == 'add' && $data['data']['product_kind'] == '產品'){
            foreach($depotdata as $key => $val){
                DB::table('EA204_79')
                    ->insert([
                        'product_code' => $data['data']['product_code'],
                        'product_name' => $data['data']['product_name'],
                        'num' => '0',
                        'unit_code' => $data['data']['unit_code'],
                        'unit_name' => $data['data']['unit_name'],
                        'depot_code' => $val->depot_code,
                        'depot_name' => $val->depot_name,
                        'safe_num' => '0',
                    ]);
            }

        }else if($data['status'] == 'update' && $data['data']['product_kind'] == '產品'){
            DB::table('EA204_79')
                ->where('product_code', $data['data']['product_code'])
                ->update([
                    'product_name' => $data['data']['product_name'],
                    'unit_code' => $data['data']['unit_code'],
                    'unit_name' => $data['data']['unit_name'],
                ]);
        }
    }
    static public function bfDelete(&$data,$verify = false){

        if($verify){
			$page_id = 49;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
        }
        $code = $data['data']['product_code'];
        $results = DB::select(
        "DECLARE @tablename NVARCHAR(50)
        DECLARE @cloumnname NVARCHAR(50)
        DECLARE @othername NVARCHAR(50)
        DECLARE @store TINYINT

        SET @tablename=''
        SET @cloumnname='product_code'
        SET @othername='component_code'

        BEGIN

        SELECT DISTINCT SUBSTRING(so.name,1,CHARINDEX('_',so.name)-1) as 't',so.name FROM sysobjects so
        INNER JOIN syscolumns sc ON so.id =sc.id
        INNER JOIN systypes st ON st.xtype=sc.xtype
        WHERE (so.type='U'AND st.name <> 'sysname')
        AND
        so.name LIKE '%'+@tablename+'%'
        AND
        (sc.name LIKE '%'+@cloumnname+'%' or sc.name LIKE '%'+@othername+'%')

        order by 't'

        END"
        );
        $pagecode = '';
        foreach($results as $key => $val){
            if($val->t != 'AA202' && $val->t != 'AA202' && $val->t != 'EA204' && $val->t != 'BA204' && $val->t != 'BA205' && $val->t != 'BA210'  && $val->t != 'CA210'&& $val->t != 'BA206' && $val->t != 'CA204' && $val->t != 'CA205' && $val->t != 'DA203' && $val->t != 'BA301' && $val->t != 'CA301' && $val->t != 'DA301'&&$val->t != 'BA302'&&$val->t != 'CA303'&&$val->t != 'EA209'&&$val->t != 'EA210'&&$val->t != 'EA212'){
                if($val->name == 'AA203_33'){
                    $indata = DB::table($val->name)
                            ->select('*')
                            ->where('component_code', $code)
                            ->get();
                    if(count($indata) > 0){
                        $translations = TranslationUtil::getTranslationByCode($val->t);
                        $pagecode = $pagecode."「".$translations."」";
                    }
                }else if($val->name == 'DA201_46'){
                    $indata = DB::table($val->name)
                            ->select('*')
                            ->where('component_code', $code)
                            ->get();
                    if(count($indata) > 0){
                        $translations = TranslationUtil::getTranslationByCode($val->t);
                        $pagecode = $pagecode."「".$translations."」";
                    }
                }else if($val->name == 'DA202_58'){
                    $indata = DB::table($val->name)
                            ->select('*')
                            ->where('component_code', $code)
                            ->get();
                    if(count($indata) > 0){
                        $translations = TranslationUtil::getTranslationByCode($val->t);
                        $pagecode = $pagecode."「".$translations."」";
                    }
                }else{
                    $indata = DB::table($val->name)
                            ->select('*')
                            ->where('product_code', $code)
                            ->get();
                    if(count($indata) > 0){
                        $translations = TranslationUtil::getTranslationByCode($val->t);
                        $pagecode = $pagecode."「".$translations."」";
                    }
                }
            }
        }
        return array(
            'pagecode' => $pagecode,
            'code' => $code
        );
    }


    // View
    static public function beforeView(&$id, &$pageData)
    {
    }
    static public function afterView(&$id, &$data, &$pageData)
    {
    }

    // Save
    static public function beforeSave(&$data, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
			DB::beginTransaction();
			$tmpArr = self::bfSave($data);
			$errorMsg["errors"] = $tmpArr;
			if( !empty($errorMsg["errors"]) ){
				response()->json($errorMsg)->send();
				DB::rollback();
				die();
			}else{
				DB::commit();
			}
		}
    }
    static public function beforeDatasetValidation(&$dataset, &$schema, &$rules, &$pageData)
    {
    }
    static public function afterDatasetValidationSuccess(&$dataset, &$schema, &$rules, &$validationResult, &$pageData)
    {
    }
    static public function afterDatasetValidationFail(&$dataset, &$schema, &$rules, &$validationResult, &$pageData)
    {
    }
    static public function beforeDatasetInsert(&$dataset, &$schema, &$insertData, &$pageData)
    {
    }
    static public function afterDatasetInsert(&$dataset, &$schema, &$insertData, &$pageData)
    {
    }
    static public function beforeDatasetUpdate(&$dataset, &$schema, &$updateData, &$pageData)
    {
    }
    static public function afterDatasetUpdate(&$dataset, &$schema, &$updateData, &$pageData)
    {
    }
    static public function afterSuccessSave(&$data, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
			self::atSave($data);
        }
    }
    static public function afterFailSave(&$data, &$pageData)
    {
    }

    // Delete
    static public function beforeDelete(&$data, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
            $res = self::bfDelete($data);
            if($res['pagecode'] != ''){
                response()->json(['status' => false , 'message' => '產品代碼：'.$res['code'].'  已被引用於：'.$res['pagecode'].'，故無法刪除'])->send();
                die();
            }
        }

        //dd($data);
    }
    static public function afterDeleteSuccess(&$data, &$pageData)
    {
        //dd($data);
        DB::table('EA204_79')->where('product_code', $data['data']['product_code'])->delete();
    }
    static public function afterDeleteFail(&$data, &$pageData)
    {
    }

    // Filter
    static public function beforeFilter(&$requestData, &$pageData)
    {
    }
    static public function afterFilter(&$requestData, &$filterResult, &$pageData)
    {
    }

    // List
    static public function beforeList(&$pageData)
    {
    }
    // Verify
    static public function beforeExecuteVerify(&$data, &$result){}
    static public function afterSuccessExecuteVerify(&$data, &$result){}
    static public function afterLastestExecuteVerify(&$data, &$result){
        self::atSave($data,true);
    }
    static public function afterFailedExecuteVerify(&$data, &$result){}
    static public function beforeReturnVerify(&$data, &$result){}
    static public function afterReturnVerify(&$data, &$result){}
    static public function afterLastestReturnVerify(&$data, &$result){
        $result["messages"] = ["此產品資料已被審核過，故無法退回"];
        $result["success"] = false;
    }
    static public function beforeInitVerify(&$data, &$result){}
    static public function afterInitVerify(&$data, &$result){}
    //255重置
	static public function afterLastestInitVerify(&$data, &$result){
		$result["messages"] = ["此產品資料已被審核過，故無法重置"];
        $result["success"] = false;
	}
}
