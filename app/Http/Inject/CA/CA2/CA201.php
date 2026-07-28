<?php

namespace App\Http\Inject\CA\CA2;

use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Utils\TranslationUtil;
use App\Http\Controllers\CommonController;
use App\Utils\VerifyUtil;
use App\Utils\PageUtil;
//新增新的CLASS需到CMD下指令：D:\xampp\htdocs\haishang>composer dumpautoload
class CA201 extends InjectBase
{
    static public function bfDelete(&$data, $verify = false)
    {
        if($data['data']["source_code"] != null){
            DB::table('CA207_2222 as a')->select(DB::raw('a.*,a.data_options as dataoptions,b.*'))->leftJoin('CA207_2223 as b', 'b.parent_id','=','a.id')->where('docu_number','=',$data['data']["source_code"])
            ->update(array('trans_order_num' => null));
         }
        if ($verify) {
            $page_id = 55;
            $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
            // Get data
            $data = PageUtil::getData($pageData, $data['id']);
            $warm = [];
        } else {
            $warm = '';
        }
        $tmpArr = [];
        foreach ($data['subData'][43] as $key => $val) {
            $olddata = DB::table('CA202_55 as a')
                ->leftJoin('CA202_54 as b', 'b.id', '=', 'a.parent_id')
                ->select('a.*')
                ->where('a.purchase_no', $data['subData'][43][$key]['data']['id']);
            $olddata = $olddata->get();

            if (count($olddata) > 0) {
                if ($verify) {
                    $tmpArr[] = '單據號碼：' . $data['data']['purchase_code'] . '  已有對應的進貨單，故無法刪除';
                } else {
                    array_push($tmpArr, [
                        "text" => '單據號碼：' . $data['data']['purchase_code'] . '  已有對應的進貨單，故無法退回'
                    ]);
                }
            }
        }
        return $tmpArr;
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
        if (array_key_exists('purchase_code', $dataset['data'])) {
            $number = CommonController::generateDocumentNumber($pageId, 'purchase_code', $dataset['data']['purchase_code']);
            $dataset['data']['purchase_code'] = $number;
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
    }
    static public function afterFailSave(&$data, &$pageData)
    {
    }

    // Delete
    static public function beforeDelete(&$data, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
        if (array_key_exists('ship_code', $data['data'])) {
            $number = CommonController::generateDocumentNumber($pageId, 'ship_code', $data['data']['ship_code']);
            $data['data']['ship_code'] = $number;
        }
        if (!VerifyUtil::pageVerifyConfirmation($pageId)) {
            DB::beginTransaction();
            $tmpArr = self::bfDelete($data);
            $errorMsg["errors"] = $tmpArr;
            if (!empty($errorMsg["errors"])) {
                response()->json($errorMsg)->send();
                DB::rollback();
                die();
            } else {
                DB::commit();
            }
        }
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
    static public function afterLastestExecuteVerify(&$data, &$result){}
	static public function afterFailedExecuteVerify(&$data, &$result){}
	//從255退回
	static public function afterLastestReturnVerify(&$data, &$result){
        $tmpArr = self::bfDelete($data,true);
		if( !empty($tmpArr) ){
			$result["messages"] = $tmpArr;
			$result["success"] = false;
		}
	}
	//退回前
    static public function beforeReturnVerify(&$data, &$result){}
	//退回後
	static public function afterReturnVerify(&$data, &$result){}
	//255重置
	static public function afterLastestInitVerify(&$data, &$result){

		$tmpArr = self::bfDelete($data,true);
		if( !empty($tmpArr) ){
			$result["messages"] = $tmpArr;
			$result["success"] = false;
		}
	}
}
