<?php

namespace App\Http\Inject\BA\BA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\System\NotificationController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class BA201 extends InjectBase
{
    static public function bfDelete(&$data,$verify = false){
        //$test=$data->data;
        //dd($data);
         if($data['data']["source_code"] != null){
            DB::table('BA207_2198 as a')->select(DB::raw('a.*,a.data_options as dataoptions,b.*'))->leftJoin('BA207_2199 as b', 'b.parent_id','=','a.id')->where('docu_number','=',$data['data']["source_code"])
            ->update(array('trans_order_num' => null));
         }
		if($verify){
			$page_id = 53;
			$pageData = TranslationUtil::getPageDataWithTranslation($page_id);
			// Get data
			$data = PageUtil::getData($pageData, $data['id']);
		}
        $code = $data['data']['client_order_code'];
        $results = DB::select(
        "DECLARE @tablename NVARCHAR(50)
        DECLARE @cloumnname NVARCHAR(50)
        DECLARE @store TINYINT

        SET @tablename=''
        SET @cloumnname='order_code'

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
            if( $val->t != 'FA101' && $val->t != 'BA201' && $val->t != 'BA204' && $val->t != 'BA205' && $val->t != 'BA206' && $val->t != 'CA204' && $val->t != 'CA205' && $val->t != 'DA203' && $val->t != 'BA301' && $val->t != 'CA301' && $val->t != 'DA301'){
                if( $val->name !='BA202_52' && $val->name !='BA211_6263'){
                $sfield = $val->name == 'BA202_53' || $val->name == 'BA211_6264' || $val->name == 'BA203_62' || $val->name == 'DA201_45' || $val->name == 'DA202_57' ? 'client_order_code' : 'order_code';
                //dd($val->name);
                $indata = DB::table($val->name)
                        ->select('*')
                        ->where($sfield, $code)
                        ->get();

                //var_dump($indata);
                if(count($indata) > 0){
                    $translations = TranslationUtil::getTranslationByCode($val->t);
                    $pagecode = $pagecode."「".$translations."」";
                }
            }
            }
        }//dd('aaa');
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
    }
    static public function beforeDatasetValidation(&$dataset, &$schema, &$rules, &$pageData)
    {
    }
    static public function afterDatasetValidationSuccess(&$dataset, &$schema, &$rules, &$validationResult, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
		if( array_key_exists('client_order_code',$dataset['data']) ){
			$number = CommonController::generateDocumentNumber($pageId,'client_order_code',$dataset['data']['client_order_code']);
        	$dataset['data']['client_order_code'] = $number;
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
        if($data['status'] == 'add'){
            NotificationController::notification_setting_add($pageId,"insert");
        }else if($data['status'] == 'update'){
            NotificationController::notification_setting_add($pageId,"update");
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
        $pageId = $pageData['page']['page_id'];
        NotificationController::notification_setting_add($pageId,"delete");
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
	}
	static public function afterFailedExecuteVerify(&$data, &$result){}
	//從255退回
	static public function afterLastestReturnVerify(&$data, &$result){
        $tmpArr = self::bfDelete($data,true);
        if($tmpArr['pagecode'] != ''){
            // response()->json(['status' => false , 'message' => '單據號碼：'.$tmpArr['code'].'  已被引用於：'.$tmpArr['pagecode'].'，故無法刪除'])->send();

            $result["messages"] = ['單據號碼：'.$tmpArr['code'].'  已被引用於：'.$tmpArr['pagecode'].'，故無法退回'];
            $result["success"] = false;
            //  die();
        }

	}
	//退回前
    static public function beforeReturnVerify(&$data, &$result){}
	//退回後
    static public function afterReturnVerify(&$data, &$result){}
    //255重置
	static public function afterLastestInitVerify(&$data, &$result){

		$tmpArr = self::bfDelete($data,true);
        if($tmpArr['pagecode'] != ''){
            $result["messages"] = ['單據號碼：'.$tmpArr['code'].'  已被引用於：'.$tmpArr['pagecode'].'，故無法重置'];
            $result["success"] = false;
        }
	}
}
