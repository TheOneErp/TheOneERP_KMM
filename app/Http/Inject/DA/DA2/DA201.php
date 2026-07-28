<?php

namespace App\Http\Inject\DA\DA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class DA201 extends InjectBase
{
    static public function bfSave(&$data,$verify = false){
		if( !array_key_exists('status',$data) ){
			$page_id = 56;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
			$data['status'] = 'add';
		}else{
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
        }
        $tmpArr = [];
        if($data['status'] == 'update'){
            $oldbodydata = DB::table('DA201_45')
                    ->select('*')
                    ->where('parent_id', $data['data']['id'])
                    ->get();
            if(count($oldbodydata)>0){
                foreach($oldbodydata as $key => $val){
                    //將對應的客戶訂單表身工單號碼清除
                    $bodyorder = DB::table('BA201_41')
                            ->where('id', $val->order_no)
                            ->get();
                    if(count($bodyorder)>0){
                        foreach($bodyorder as $bmkey => $bmval){
                            DB::table('BA201_41')
                                    ->where('id', $bmval->id)
                                    ->update(['machining_code' => null]);
                        }
                    }
                }
            }
        }
        if( $verify ){
			self::atSave($data,true);
		}
    }
    static public function atSave(&$data,$verify = false)
	{
		if( !$verify ){
			$data['status'] = $data['status'] == "" ? "update" : $data['status'];
		}
        if($data['status'] != 'add'){
            //將對應的客戶訂單表身工單號碼加回去
            foreach($data['subData'][45] as $bodykey => $bodyval){
                $bodyorder = DB::table('BA201_41')
                        ->where('id', $data['subData'][45][$bodykey]['data']['order_no'])
                        ->get();
                if(count($bodyorder)>0){
                    foreach($bodyorder as $bmkey => $bmval){
                        DB::table('BA201_41')
                                ->where('id', $bmval->id)
                                ->update(['machining_code' => $data['data']['machining_code']]);
                    }
                }
            }
        }
    }
    static public function bfDelete(&$data,$verify = false){
        if($verify){
			$page_id = 56;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
        }
        $code = $data['data']['machining_code'];
        $results = DB::select(
        "DECLARE @tablename NVARCHAR(50)
        DECLARE @cloumnname NVARCHAR(50)
        DECLARE @store TINYINT

        SET @tablename=''
        SET @cloumnname='machining_code'

        BEGIN

        SELECT DISTINCT SUBSTRING(so.name,1,CHARINDEX('_',so.name)-1) as 't',so.name FROM sysobjects so
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
        //dd($results);
        $pagecode = '';
        foreach($results as $key => $val){
            if($val->t != 'BA201' && $val->t != 'DA201' && $val->t != 'DA201' && $val->t != 'DA201' && $val->t != 'BA204' && $val->t != 'BA205' && $val->t != 'BA206' && $val->t != 'CA204' && $val->t != 'CA205' && $val->t != 'DA203' && $val->t != 'BA301' && $val->t != 'CA301' && $val->t != 'DA301'){
                $indata = DB::table($val->name)
                        ->select('*')
                        ->where('machining_code', $code)
                        ->get();
                if(count($indata) > 0){
                    $translations = TranslationUtil::getTranslationByCode($val->t);
                    $pagecode = $pagecode."「".$translations."」";
                }
            }
        }
        if($pagecode == ''){
//          //客戶訂單 加工單號取消
			DB::table('BA201_41')
            ->where('machining_code', $code)
            ->update([
                'machining_code' => null,
            ]);
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
		if( array_key_exists('ship_code',$data['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'ship_code',$data['data']['ship_code']);
			$data['data']['ship_code'] = $number;
		}
		if ( !VerifyUtil::pageVerifyConfirmation($pageId) ) {
			DB::beginTransaction();
			self::bfSave($data);
			DB::commit();
		}
        
    }
    static public function beforeDatasetValidation(&$dataset, &$schema, &$rules, &$pageData)
    {
    }
    static public function afterDatasetValidationSuccess(&$dataset, &$schema, &$rules, &$validationResult, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
		if( array_key_exists('machining_code',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'machining_code',$dataset["data"]["machining_code"]);
        	$dataset['data']['machining_code'] = $number;
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
			// DB::beginTransaction();
            $res = self::bfDelete($data);
            if($res['pagecode'] != ''){
                response()->json(['status' => false , 'message' => '單據號碼：'.$res['code'].'  已被引用於：'.$res['pagecode'].'，故無法刪除'])->send();
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
        $res = self::bfDelete($data,true);
        if($res['pagecode'] != ''){
            $result["messages"] = ['單據號碼：'.$res['code'].'  已被引用於：'.$res['pagecode'].'，故無法退回'];
            $result["success"] = false;
        }
	}
	//退回前
    static public function beforeReturnVerify(&$data, &$result){}
	//退回後
	static public function afterReturnVerify(&$data, &$result){}
	//255重置
	static public function afterLastestInitVerify(&$data, &$result){
		
		$res = self::bfDelete($data,true);
		if($res['pagecode'] != ''){
            $result["messages"] = ['單據號碼：'.$res['code'].'  已被引用於：'.$res['pagecode'].'，故無法重置'];
            $result["success"] = false;
        }
	}
}
