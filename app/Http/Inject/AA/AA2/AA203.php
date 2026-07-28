<?php

namespace App\Http\Inject\AA\AA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class AA203 extends InjectBase
{
    static public function bfDelete(&$data,$verify = false){
       
        if($verify){
			$page_id = 50;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
        }
        $code = $data['data']['formula_name'];
		
        $results = DB::select(
        "DECLARE @tablename NVARCHAR(50)
        DECLARE @cloumnname NVARCHAR(50)
        DECLARE @store TINYINT

        SET @tablename=''
        SET @cloumnname='formula'

        BEGIN

        SELECT DISTINCT SUBSTRING(so.name,1,CHARINDEX('_',so.name)-1) as 't',so.name as name,sc.name as colname FROM sysobjects so
        INNER JOIN syscolumns sc ON so.id =sc.id
        INNER JOIN systypes st ON st.xtype=sc.xtype
        WHERE (so.type='U'AND st.name <> 'sysname')
        AND
        so.name LIKE '%'+@tablename+'%'
        AND
        sc.name LIKE '%'+@cloumnname+'%'
        
        order by 't'

        END"
        );
//        dd($results);
        $pagecode = '';
        $samet = [];
        foreach($results as $key => $val){
            if($val->t != 'AA203' && $val->t != 'BA204' && $val->t != 'BA205' && $val->t != 'BA206' && $val->t != 'CA204' && $val->t != 'CA205' && $val->t != 'DA203' && $val->t != 'EA204' && $val->t != 'BA301' && $val->t != 'CA301' && $val->t != 'DA301'){
                if( array_key_exists($val->t,$samet) ){	
                    array_push($samet[$val->t],$results[$key]);

                }else{
                    $samet[$val->t] = [];
                    array_push($samet[$val->t],$results[$key]);
                }
            }
        }
        foreach($samet as $key=>$val){
            $indata = DB::table($samet[$key][0]->name)
                    ->select('*')
                    ->where($samet[$key][0]->colname, $code)
                    ->where('product_code', $data['data']['product_code'])
                    ->get();
            if(count($indata) > 0){
                $translations = TranslationUtil::getTranslationByCode($samet[$key][0]->t);
                $pagecode = $pagecode."「".$translations."」";
            }else{
                if(!empty($samet[$key][1])){
                    $indata2 = DB::table($samet[$key][1]->name)
                            ->select('*')
                            ->where($samet[$key][1]->colname, $code)
                            ->where('product_code', $data['data']['product_code'])
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
                            ->where('product_code', $data['data']['product_code'])
                            ->get();
                    if(count($indata2) > 0){
                        $translations = TranslationUtil::getTranslationByCode($samet[$key][2]->t);
                        $pagecode = $pagecode."「".$translations."」";
                    }
                }
                
            }
        }
//        dd($pagecode);
        return array(
            'pagecode' => $pagecode,
            'code' => $code
        );
        // if($pagecode != ''){
        //     //$translations = TranslationUtil::getTranslationByCode('AA101.error.deletefailed');
        //     response()->json(['status' => false , 'message' => '配方名稱：'.$code.'  已被引用於：'.$pagecode.'，故無法刪除'])->send();
        //     die();
        // }
    }
    static public function bfSave(&$data,$verify = false){
        if( !array_key_exists('status',$data) ){
			$page_id = 50;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
        if($data['status'] == 'add'){
            $errorarr = [];
            $olddata = DB::table('AA203_32')
                        ->select('*')
                        ->where('product_code', $data['data']['product_code'])
                        ->where('formula_name', $data['data']['formula_name'])
                        ->get();
            if(count($olddata)>0){
                if( $verify ){
                    foreach( $olddata as $key => $val ){
                        if( $val->id != $data['data']['id'] ){
                            if($verify){
                                $errorarr[] = '警告：成品代碼為 "'.$data['data']['product_code'].'" 配方名稱為"'.$data['data']['formula_name'].'"，已存在相同資料故無法保存，請確認';
                            }else{
                                array_push($errorarr,[
                                    "text"=>'警告：成品代碼為 "'.$data['data']['product_code'].'" 配方名稱為"'.$data['data']['formula_name'].'"，已存在相同資料故無法保存，請確認'
                                ]);
                            }
                            
                        }
                    }
                }else{
                    if($verify){
                        $errorarr[] = '警告：成品代碼為 "'.$data['data']['product_code'].'" 配方名稱為"'.$data['data']['formula_name'].'"，已存在相同資料故無法保存，請確認';
                    }else{
                        array_push($errorarr,[
                            "text"=>'警告：成品代碼為 "'.$data['data']['product_code'].'" 配方名稱為"'.$data['data']['formula_name'].'"，已存在相同資料故無法保存，請確認'
                        ]);
                    }                    
                }
            }
        }else{
            $errorarr = [];
            $olddata = DB::table('AA203_32')
                        ->select('*')
                        ->where('product_code', $data['data']['product_code'])
                        ->where('formula_name', $data['data']['formula_name'])
                        ->get();
            if(count($olddata)>=1){
                if($olddata[0]->architecture_code == $data['data']['architecture_code'] && $olddata[0]->product_code == $data['data']['product_code'] && $olddata[0]->formula_name == $data['data']['formula_name']){
                    
                }else{
                    if($verify){
                        $errorarr[] = '警告：成品代碼為 "'.$data['data']['product_code'].'" 配方名稱為"'.$data['data']['formula_name'].'"，已存在相同資料故無法保存，請確認';
                    }else{
                        array_push($errorarr,[
                            "text"=>'警告：成品代碼為 "'.$data['data']['product_code'].'" 配方名稱為"'.$data['data']['formula_name'].'"，已存在相同資料故無法保存，請確認'
                        ]);
                    }
                    
                }
            }
        }
        return $errorarr;
        
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
        $pageId = $pageData['page']['page_id'];  
		if( array_key_exists('architecture_code',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'architecture_code',$dataset['data']['architecture_code']);
			//dd($number);
			$dataset['data']['architecture_code'] = $number;
		}
		
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
    static public function afterSuccessSave(&$data, &$pageData){}
    static public function afterFailSave(&$data, &$pageData)
    {
    }

    // Delete
    static public function beforeDelete(&$data, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
            $res = self::bfDelete($data);
//            dd($res);
            if($res['pagecode'] != ''){
                response()->json(['status' => false , 'message' => '配方名稱：'.$res['code'].'  已被引用於：'.$res['pagecode'].'，故無法刪除'])->send();
                die();
            }
        }

        
        //dd($data);
    }
    static public function afterDeleteSuccess(&$data, &$pageData)
    {
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
	//執行審核前 $result = array() boolen message//提示訊息
    static public function beforeExecuteVerify(&$data, &$result){}
	//每層成功
    static public function afterSuccessExecuteVerify(&$data, &$result){}
	//255觸發
    static public function afterLastestExecuteVerify(&$data, &$result){
		$tmpArr = self::bfSave($data,true);
		if( !empty($tmpArr) ){
			$result["messages"] = $tmpArr;
			$result["success"] = false;
		}
	}
	static public function afterFailedExecuteVerify(&$data, &$result){}
	//從255退回
	static public function afterLastestReturnVerify(&$data, &$result){
        $tmpArr = self::bfDelete($data,true);
        if($tmpArr['pagecode'] != ''){           
            $result["messages"] = ['配方名稱：'.$tmpArr['code'].'  已被引用於：'.$tmpArr['pagecode'].'，故無法退回'];
            $result["success"] = false;
        }
    }
    static public function beforeInitVerify(&$data, &$result){}
    static public function afterInitVerify(&$data, &$result){}
    //255重置
	static public function afterLastestInitVerify(&$data, &$result){
		$tmpArr = self::bfDelete($data,true);
        if($tmpArr['pagecode'] != ''){           
            $result["messages"] = ['配方名稱：'.$tmpArr['code'].'  已被引用於：'.$tmpArr['pagecode'].'，故無法重置'];
            $result["success"] = false;
        }
	}
}
