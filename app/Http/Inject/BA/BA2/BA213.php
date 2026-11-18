<?php

namespace App\Http\Inject\BA\BA2;
use Illuminate\Support\Facades\DB;
use App\Http\Inject\InjectBase;
use App\Http\Controllers\CommonController;

//新增新的CLASS需到CMD下指令：composer dumpautoload
// 請款單 BA213（表身 form 6274）
class BA213 extends InjectBase
{
    const BODY_FORM = 6274;

    // View
    static public function beforeView(&$id, &$pageData){}
    static public function afterView(&$id, &$data, &$pageData){}

    // Save：單據號碼 BA213-日期-流水號；表頭金額依表身重新計算
    static public function beforeSave(&$data, &$pageData)
    {
        $pageId = $pageData['page']['page_id'];
        if (array_key_exists('invoice_code', $data['data'])) {
            $data['data']['invoice_code'] = CommonController::generateDocumentNumber($pageId, 'invoice_code', $data['data']['invoice_code']);
        }
        $sub = 0;
        $rows = isset($data['subData'][self::BODY_FORM]) ? $data['subData'][self::BODY_FORM] : [];
        foreach ($rows as $r) {
            $d = isset($r['data']) ? $r['data'] : [];
            if (!empty($d['ship_code']) && isset($d['body_subtotal']) && is_numeric($d['body_subtotal'])) {
                $sub += (float)$d['body_subtotal'];
            }
        }
        $rate = (isset($data['data']['taxrate']) && is_numeric($data['data']['taxrate'])) ? (float)$data['data']['taxrate'] : 0;
        $tax = round($sub * $rate, 2);
        $data['data']['osubtotal'] = round($sub, 2);
        $data['data']['otax'] = $tax;
        $data['data']['ototal'] = round($sub + $tax, 2);
        // 已收金額暫不串應收沖帳：未收 = 合計 - 已收
        $recd = (isset($data['data']['amt_recd']) && is_numeric($data['data']['amt_recd'])) ? (float)$data['data']['amt_recd'] : 0;
        $data['data']['amt_recd'] = $recd;
        $data['data']['amt_outstanding'] = round($sub + $tax - $recd, 2);
    }
    static public function beforeDatasetValidation(&$dataset, &$schema, &$rules, &$pageData){}
    static public function afterDatasetValidationSuccess(&$dataset, &$schema, &$rules, &$validationResult, &$pageData){}
    static public function afterDatasetValidationFail(&$dataset, &$schema, &$rules, &$validationResult, &$pageData){}
    static public function beforeDatasetInsert(&$dataset, &$schema, &$insertData, &$pageData){}
    static public function afterDatasetInsert(&$dataset, &$schema, &$insertData, &$pageData){}
    static public function beforeDatasetUpdate(&$dataset, &$schema, &$updateData, &$pageData){}
    static public function afterDatasetUpdate(&$dataset, &$schema, &$updateData, &$pageData){}
    static public function afterSuccessSave(&$data, &$pageData){}
    static public function afterFailSave(&$data, &$pageData){}

    // Delete
    static public function beforeDelete(&$data, &$pageData){}
    static public function afterDeleteSuccess(&$data, &$pageData){}
    static public function afterDeleteFail(&$data, &$pageData){}

    // Filter / List
    static public function beforeFilter(&$requestData, &$pageData){}
    static public function afterFilter(&$requestData, &$filterResult, &$pageData){}
    static public function beforeList(&$pageData){}

    // Verify
    static public function beforeExecuteVerify(&$data, &$result){}
    static public function afterSuccessExecuteVerify(&$data, &$result){}
    static public function afterLastestExecuteVerify(&$data, &$result){}
    static public function afterFailedExecuteVerify(&$data, &$result){}
    static public function afterLastestReturnVerify(&$data, &$result){}
    static public function beforeReturnVerify(&$data, &$result){}
    static public function afterReturnVerify(&$data, &$result){}
    static public function afterLastestInitVerify(&$data, &$result){}
}
