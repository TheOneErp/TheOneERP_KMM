<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use Carbon\Carbon;

use App\Http\Inject\InjectBase;

use App\Utils\TranslationUtil;
use App\Utils\PermissionUtil;
use App\Utils\ValidationUtil;
use App\Utils\PageUtil;
use App\Utils\DataUtil;
use App\Utils\FileUtil;
use App\Utils\LogUtil;
use App\Utils\SessionUtil;
use App\Utils\VerifyUtil;

class PageController extends Controller
{
    public function checkMethodExists($className, $method)
    {
        if (class_exists("App\\Http\\Controllers\\" + $className)) {
            return method_exists("App\\Http\\Controllers\\" + $className, $method);
        } else {
            return false;
        }
    }

    public function list(Request $request, $page_id) // list frontEnd (no data)
    {
        // Check permission
        $permission = PermissionUtil::getCurrentUserPagePermission($page_id);
        if (!$permission['permission_read'])
            abort(403);

        // Get page data
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        if ($pageData == null) {
            abort(404);
        }

        session(["PAGE_ID" => $page_id]);
        //dd($pageData["page"]["page_list_template"]);
        // Inject functions
     
        $class = 'App\\Http\\Inject\\' . $pageData['path'];
        $injectClass = class_exists($class) ? new $class() : new InjectBase;
        //dd($injectClass);
        $fetch_data = $injectClass->beforeList($pageData);
        //dd($fetch_data);
       // dd($injectClass);
        // System redirect
        $pageModule = explode("_", $pageData['page']['page_code'])[0];
        if (in_array($pageModule, ['SY', 'DT'])) {
            $redirectTo = explode("_", $pageData['page']['page_code']);
            array_shift($redirectTo);
            $redirectTo = strtolower(implode("_", $redirectTo)) . "_list";
            if (Route::has($redirectTo)) {
                return redirect()->route($redirectTo/* , ['page_id' => $page_id] */);
            }
        }
              // Get page options w/ custom options
        $pageOptions = PageUtil::getPageOptions($pageData);
        $dataKey = $pageOptions['dataKey'];
        //dd($pageModule);
        if($pageData["page"]["page_list_template"] !="chart" ){
          
    
            return view($pageOptions['listView'], compact('fetch_data'))
                ->with("pageData", $pageData)
                ->with("pageOptions", $pageOptions)
                ->with("dataKey", $dataKey)
                ->with("permission", $permission)
                ->with("routes", [
                    "filter" => "system.page.filter.get",
                    "save" => "system.page.save.post",
                    "view" => "system.page.view.get",
                    "delete" => "system.page.delete.delete",
                    "verify" => "system.verify",
                    "report" => "system.report.output"
                ]);
        
        }
        $orders = DB::table('BA201_40')
        ->whereYear('undertakerday', '=', date("Y"))
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->undertakerday)->format('m'); 
        });
        $ordercount = [];
        $orderArr = [];
        
        foreach ($orders as $key => $value) {
            $ordercount[(int)$key] = count($value);
        }
        
        for($i = 1; $i <= 12; $i++){
            if(!empty($ordercount[$i])){
                $orderArr[$i] = $ordercount[$i];    
            }else{
                $orderArr[$i] = 0;    
            }
        }

        $past_orders = DB::table('BA201_40')
        ->whereYear('undertakerday', '=', date("Y")-1)
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->undertakerday)->format('m'); 
        });
        $ordercount2 = [];
        $orderArr2 = [];
        
        foreach ($past_orders as $key => $value) {
            $ordercount2[(int)$key] = count($value);
        }
        
        for($i = 1; $i <= 12; $i++){
            if(!empty($ordercount2[$i])){
                $orderArr2[$i] = $ordercount2[$i];    
            }else{
                $orderArr2[$i] = 0;    
            }
        }

        $shipments = DB::table('BA202_52')
        ->whereYear('ship_date', '=', date("Y"))
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->ship_date)->format('m'); 
        });
        //dd($shipments);
        $shipmentscount = [];
        $shipmentsArr = [];
        
        foreach ($shipments as $key => $value) {
           $total = 0;
           foreach($value as $key2 =>$value2){
             $total = $total+$value2->stotal;
           }
            $shipmentscount[(int)$key] = [count($value),$total];
        }
        //dd($shipmentscount);
        for($i = 1; $i <= 12; $i++){
            if(!empty($shipmentscount[$i])){
                $shipmentsArr[$i] = $shipmentscount[$i];    
            }else{
                $shipmentsArr[$i] = [0,0];    
            }
        }

        $past_shipments = DB::table('BA202_52')
        ->whereYear('ship_date', '=', date("Y")-1)
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->ship_date)->format('m'); 
        });
        //dd($shipments);
        $shipmentscount2 = [];
        $shipmentsArr2 = [];
        
        foreach ($past_shipments as $key => $value) {
           $total = 0;
           foreach($value as $key2 =>$value2){
             $total = $total+$value2->stotal;
           }
            $shipmentscount2[(int)$key] = [count($value),$total];
        }
        //dd($shipmentscount);
        for($i = 1; $i <= 12; $i++){
            if(!empty($shipmentscount2[$i])){
                $shipmentsArr2[$i] = $shipmentscount2[$i];    
            }else{
                $shipmentsArr2[$i] = [0,0];    
            }
        }
        
        $allCosts = DB::table('GA201_3224')
        ->whereYear('exp_date', '=', date("Y"))
        //->whereMonth('exp_date', '=', date("m"))
        ->select('exp_item',DB::raw("SUM(body_subtotal) as sum_money"))
        ->groupby('exp_item')
        ->get();

        $topCosts = $allCosts->sortByDesc('sum_money')->take(10);

        // Sum the remaining items
        $remainingSum = $topCosts->slice(10)->sum('sum_money');


       // dd($topCosts,$remainingSum);
        $formattedData = $topCosts->map(function ($item) {
            return [
                '項目' => $item->exp_item,
                '金額' => $item->sum_money,
            ];
        });

        if ($allCosts->count() > 10) {
            $formattedData[] = ['項目' => '其他合計', '金額' => $remainingSum];
        }


        $allCosts2 = DB::table('GA201_3224')
         ->whereMonth('exp_date', '=', date("m", strtotime("-1 month")))
        ->select('exp_item',DB::raw("SUM(body_subtotal) as sum_money"))
        ->groupby('exp_item')
        ->get();

        $topCosts2 = $allCosts2->sortByDesc('sum_money')->take(10);

        // Sum the remaining items
        $remainingSum2 = $topCosts2->slice(10)->sum('sum_money');


       // dd($topCosts,$remainingSum);
        $formattedData2 = $topCosts2->map(function ($item) {
            return [
                '項目' => $item->exp_item,
                '金額' => $item->sum_money,
            ];
        });

        if ($allCosts2->count() > 10) {
            $formattedData2[] = ['項目' => '其他合計', '金額' => $remainingSum2];
        }
        $currentYear = Carbon::now()->year;
        $years = range($currentYear, $currentYear - 10);



   
  

        return view($pageOptions['listView'], compact('ordercount','ordercount2','orderArr','orderArr2','shipmentsArr','shipmentsArr2','formattedData','formattedData2','years'))
            ->with("pageData", $pageData)
            ->with("pageOptions", $pageOptions)
            ->with("dataKey", $dataKey)
            ->with("permission", $permission)
            ->with("routes", [
                "filter" => "system.page.filter.get",
                "save" => "system.page.save.post",
                "view" => "system.page.view.get",
                "delete" => "system.page.delete.delete",
                "verify" => "system.verify",
                "report" => "system.report.output"
            ]);


        
   
    }

    public function filter(Request $request, $page_id) // List API w/ sort , filters
    {
        $requestData = $request->all();
        $paginationCount =  isset($request->paginationCount) && is_numeric($request->paginationCount) ? (int) $request->paginationCount : 10;

        // Check permission
        $permission = PermissionUtil::getCurrentUserPagePermission($page_id);
        if (!$permission['permission_read'])
            abort(403);

        // Get page data
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        if ($pageData == null) {
            abort(404);
        }

        // Get page options w/ custom options
        $pageOptions = PageUtil::getPageOptions($pageData);
        $headTable = $pageOptions['headTable'];

        // Inject functions
        $class = 'App\\Http\\Inject\\' . $pageData['path'];
        $injectClass = class_exists($class) ? new $class() : new InjectBase;

        $injectClass->beforeFilter($requestData, $pageData);

        // Data from head form
        $query = DB::table($headTable);

        // Limit access to created by user data
        if (!$permission['permission_allow_rw_all'])
            $query->where("created_by", SessionUtil::getUserID());

// Filters
if (isset($request->filters) && ValidationUtil::isJSONString($request->filters)) {
    $filters = json_decode($request->filters);

    $operators = ['=', '!=', '>', '>=', '<', '<=', 'like', 'not like'];
    $conditions = ['or', 'and'];
    $numberTypes = ['integer', 'decimal', 'boolean'];
    $datetimeTypes = ['time', 'date', 'datetime'];

    $groups = array_unique(array_map(function ($filter) {
        return $filter->group;
    }, $filters));

    foreach ($groups as $group) {
        $groupFilters = array_filter($filters, function ($filter) use ($group) {
            return $filter->group == $group;
        });

        $query->orWhere(function ($groupQuery) use ($groupFilters, $pageOptions, $numberTypes, $datetimeTypes, $operators, $conditions) {
            foreach ($groupFilters as $filter) {
                if (!isset($filter->field, $filter->operator, $filter->value, $filter->condition)) {
                    continue;
                }

                if ((array_key_exists($filter->field, $pageOptions['headForm']['fields']) || $filter->field == "*") 
                    && in_array($filter->operator, $operators) 
                    && in_array($filter->condition, $conditions)) {

                    $dataChecker = function ($field, $value) use ($numberTypes, $datetimeTypes, $pageOptions) {
                        $status = true;
                        $fieldType = $pageOptions['headForm']['fields'][$field]['field_type'] ?? '';
                        if ($fieldType == "button" || $fieldType == "reference_page") {
                            $status = false;
                        } else if (in_array($fieldType, $numberTypes)) {
                            if (!is_numeric($value)) $status = false;
                        } else if (in_array($fieldType, $datetimeTypes)) {
                            try {
                                Carbon::parse($value);
                            } catch (\Exception $e) {
                                $status = false;
                            }
                        }
                        return $status;
                    };

                    // === LIKE 處理 - 重要修正 ===
                if ($filter->field == "*") {
                    $doAllWhere = function ($allFieldQuery) use ($pageOptions, $dataChecker, $filter) {
                        $collate = 'Chinese_Taiwan_Stroke_CI_AS';
                        $searchValue = '%' . $filter->value . '%';

                        foreach (array_keys($pageOptions['headForm']['fields']) as $key) {
                            if (!$dataChecker($key, $filter->value)) {
                                continue;
                            }

                            $fieldType = $pageOptions['headForm']['fields'][$key]['field_type'] ?? 'text';

                            if (in_array($filter->operator, ['like', 'not like'])) {
                                if (in_array($fieldType, ['integer', 'decimal', 'boolean'])) {
                                    // 關鍵修正：數值欄位一定要 CAST
                                    $allFieldQuery->orWhereRaw(
                                        "CAST([{$key}] AS NVARCHAR(50)) COLLATE {$collate} " . $filter->operator . " ?",
                                        [$searchValue]
                                    );
                                } else {
                                    // 字串欄位
                                    $allFieldQuery->orWhereRaw(
                                        "[{$key}] COLLATE {$collate} " . $filter->operator . " ?",
                                        [$searchValue]
                                    );
                                }
                            } else {
                                $allFieldQuery->orWhere($key, $filter->operator, $filter->value);
                            }
                        }
                    };

                    if ($filter->condition == "and") {
                        $groupQuery->where($doAllWhere);
                    } else {
                        $groupQuery->orWhere($doAllWhere);
                    }
                }
                    // 單一欄位處理
                    // 單一欄位處理
                    else if ($dataChecker($filter->field, $filter->value)) {
                        $fieldType = $pageOptions['headForm']['fields'][$filter->field]['field_type'] ?? 'text';

                        if (in_array($filter->operator, ['like', 'not like'])) {
                            $collate = 'Chinese_Taiwan_Stroke_CI_AS';

                            if (in_array($fieldType, ['integer', 'decimal', 'boolean'])) {
                                // 數值欄位
                                $groupQuery->whereRaw(
                                    "CAST([{$filter->field}] AS NVARCHAR(50)) COLLATE {$collate} " . $filter->operator . " ?",
                                    ['%' . $filter->value . '%']
                                );
                            } else {
                                // 字串欄位
                                $groupQuery->whereRaw(
                                    "[{$filter->field}] COLLATE {$collate} " . $filter->operator . " ?",
                                    ['%' . $filter->value . '%']
                                );
                            }
                        } else if ($filter->condition == "and") {
                            $groupQuery->where($filter->field, $filter->operator, $filter->value);
                        } else {
                            $groupQuery->orWhere($filter->field, $filter->operator, $filter->value);
                        }
                    }
                }
            }
        });
    }
}

        // Sort
        if (isset($request->sortby) && ValidationUtil::isJSONString($request->sortby)) {
            $sortBys = json_decode($request->sortby);

            if (count($sortBys) == 0) $query->orderBy($pageOptions['dataKey'], 'desc');

            foreach ($sortBys as $sortBy) {
                if (isset($sortBy->order) && ($sortBy->order == "desc" || $sortBy->order == "asc"))
                    if (isset($sortBy->field) && array_key_exists($sortBy->field, $pageOptions['headForm']['fields']))
                        $query->orderBy($sortBy->field, $sortBy->order);
            }
        } else {
            $query->orderBy($pageOptions['dataKey'], 'desc');
        }

        // Return data w/ pagination
        $result = DataUtil::convertToArray($query->paginate($paginationCount)->toArray());
        foreach ($result['data'] as &$row) {
            if (isset($row['data_options'])) {
                $row['data_options'] = DataUtil::convertToArray(json_decode($row['data_options']));
            }
        }
        $injectClass->afterFilter($requestData, $result, $pageData);
        //dd($result);
        return $result;
    }

    public function view(Request $request, $page_id, $id) // Data API
    {
        // Check permission
        $permission = PermissionUtil::getCurrentUserPagePermission($page_id);
        if (!$permission['permission_read'])
            abort(403);

        // Get page data
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        if ($pageData == null) {
            abort(404);
        }

        // Inject functions
        $class = 'App\\Http\\Inject\\' . $pageData['path'];
        $injectClass = class_exists($class) ? new $class() : new InjectBase;

        $injectClass->beforeView($id, $pageData);

        // Get data
        $data = PageUtil::getData($pageData, $id);
       // dd($data);
        if (VerifyUtil::pageVerifyConfirmation($page_id) && isset($data['data']['data_options']) && isset($data['data']['data_options']['verify'])) {
            $data['verify'] = [
                'verifyStart' => ($data['data']['data_options']['verify']['level'] == 0 && $data['data']['created_by'] == SessionUtil::getUserID()),
                'canDoVerify' => $data['data']['data_options']['verify']['level'] != 0 && in_array($id, array_map(function ($item) {
                    return $item['id'];
                }, VerifyUtil::getDataWhichNeedsVerification($page_id, SessionUtil::getUserID()))),
                'canInitAndReturn' => $data['data']['data_options']['verify']['level'] != 0 && in_array($id, array_map(function ($item) {
                    return $item['id'];
                }, VerifyUtil::getDataWhichCanReturnVerification($page_id, SessionUtil::getUserID())))
            ];
        }

        $injectClass->afterView($id, $data, $pageData);
        //dd(json_encode($data));
        return json_encode($data);
    }


    public function save(Request $request, $page_id)
    {
       // dd($request);
        return $this->doSave($request, $page_id);
    }

    public function doSave(Request $request, $page_id, $data = null, $options = [], &$uploadFiles = [], &$deleteFiles = []) // Save API
    {
        if (!in_array("noCommit", $options))
            DB::beginTransaction();
       // dd($request);
        // Check permission
        $permission = PermissionUtil::getCurrentUserPagePermission($page_id);
        if (!$permission['permission_read'] && !$permission['permission_insert'] && !$permission['permission_update'])
            abort(403);

        // Get page data & page options
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        if ($pageData == null) {
            abort(404);
        }
        $pageOptions = PageUtil::getPageOptions($pageData);
        $headTable = $pageOptions['headTable'];

        // Check input is json or sub save
        if ($data != null)
            $data = $data;
        else if (ValidationUtil::isJSONString($request->data))
            $data = DataUtil::convertToArray(json_decode($request->data));
        else
            abort(400);

        // Inject functions
        $class = 'App\\Http\\Inject\\' . $pageData['path'];
        $injectClass = class_exists($class) ? new $class() : new InjectBase;

        $injectClass->beforeSave($data, $pageData);

        // Limit save for not saveable page
        if (is_array($pageData['page']['page_options']) && isset($pageData['page_options']['page']['saveable']) && !$pageData['page_options']['page']['saveable']) {
            return abort(403);
        }

        // Errors
        $errors = [];
        $addError = function ($message, $tmpID, $text = null, $fieldCode = null) use (&$errors) {
            if (is_array($message)) {
                foreach ($message as $singleMessage)
                    if ($text != null) { // For reference page
                        $errors[] = ['text' => $text, 'errors' => $message, 'tmpID' => $tmpID, 'fieldCode' => $fieldCode];
                        break;
                    } else { // For normal validator
                        $errors[] = ['text' => $singleMessage, 'tmpID' => $tmpID];
                    }
            } else { // For single error
                $errors[] = ['text' => $message, 'tmpID' => $tmpID];
            }
        };

        $saveData = function (&$dataset, $schema, $tableName = null,  $parentID = -1) use ($page_id, &$pageData, $pageOptions, &$saveData, $addError, $injectClass, $request, &$uploadFiles, &$deleteFiles) {
            // For system forms
            $dataKey = 'id';
            if (!empty($pageData['page']['page_options']['primaryKey'])) $dataKey = $pageData['page']['page_options']['primaryKey'];
            $id = isset($dataset['data'][$dataKey]) ? $dataset['data'][$dataKey] : null;

            // Passed flag
            $passed = true;

            // Ignore not edit or add
            if ($dataset["status"] !== '') {
                // Validation data
                $rules = [];
                $attributes = [];

                // Temp reference data
                $dataset['referencePages'] = [];

                // Build attributes translation and field rules and reference page save
                foreach ($schema['fields'] as $field) {
                    $ruleArray = $field['field_rule'];
                    $ruleObject = ValidationUtil::generateFieldRuleObject($ruleArray, $field, $dataset, [
                        'dataKey' => $dataKey,
                        'update' => $dataset['status'] == 'update',
                        'required' => $field['field_required']
                    ]);

                    // Save reference page
                    if ($field['field_type'] == 'reference_page' && $dataset['data'][$field['field_code']] != null) {
                        $saveResult = $this->doSave($request, $field['field_options']['reference_page']['page_id'], $dataset['data'][$field['field_code']], ['noCommit'], $uploadFiles, $deleteFiles);
                        if ($saveResult['status'] == false) {
                            $passed = false;
                            $addError($saveResult['errors'], $dataset['tmpID'], str_replace(':attribute', $field['translation'], $pageData['validation']['not_regex']), $field['field_code']);
                            return $passed;
                        } else {
                            $dataset['referencePages'][$field['field_code']] = $dataset['data'][$field['field_code']];
                            $dataset['data'][$field['field_code']] = $saveResult['headID'];
                        }
                    }

                    $rules[$field['field_code']] = $ruleObject;
                    $attributes[$field['field_code']] = $field['translation'];
                }

                $injectClass->beforeDatasetValidation($dataset, $schema, $rules, $pageData);
                $validationResult = ValidationUtil::validationData($dataset['data'], $rules, $pageData['validation'], $attributes);
                if (!$validationResult['passed']) {
                    $injectClass->afterDatasetValidationFail($dataset, $schema, $rules, $validationResult, $pageData);

                    $passed = false;
                    $addError($validationResult['errors'], $dataset['tmpID']);
                    return $passed;
                } else {
                    $injectClass->afterDatasetValidationSuccess($dataset, $schema, $rules, $validationResult, $pageData);

                    $tableName = $tableName == null ? $pageData['page']['page_code'] . '_' . $schema['form_id'] : $tableName;
                    if ($dataset['status'] == 'add') {
                        $data = PageUtil::generateInsertData($schema, $dataset['data'], $parentID);
                        $dataset['data'] = array_merge($dataset['data'], $data);
                        $injectClass->beforeDatasetInsert($dataset, $schema, $data, $pageData);

                        FileUtil::checkFilenames($dataset, $schema);

                        $id = DB::table($tableName)->insertGetId($data);
                        $dataset['data'][$dataKey] = $id;

                        FileUtil::addUploadFile($uploadFiles, $dataset, $schema);
                        LogUtil::addFormLog($page_id, $schema['form_id'], $id, $parentID, 'add', $dataset['data']);

                        $injectClass->afterDatasetInsert($dataset, $schema, $dataset['data'], $pageData);
                    } else if ($dataset['status'] == 'update') {
                        $id = $dataset['data'][$dataKey];

                        $query = DB::table($tableName)->where($dataKey, $id)->lockForUpdate();
                        $oldData = (array) $query->first();

                        FileUtil::addDeleteFile($deleteFiles, $dataset, $oldData, $schema);
                        FileUtil::checkFilenames($dataset['data'], $schema);

                        $data = PageUtil::generateUpdateData($schema, $dataset['data'], $parentID);
                        $dataset['data'] = array_merge($dataset['data'], $data);
                        $injectClass->beforeDatasetUpdate($dataset, $schema, $dataset['data'], $pageData);

                        FileUtil::addUploadFile($uploadFiles, $dataset, $schema);

                        if ($parentID == -1 && isset($oldData['data_options']) && isset($oldData['data_options']['editable']) && !$oldData['data_options']['editable']) abort(403);
                        LogUtil::addFormLog($page_id, $schema['form_id'], $id, $parentID, 'update', $dataset['data'], $oldData);
                        $query->update($data);

                        $injectClass->afterDatasetUpdate($dataset, $schema, $dataset['data'], $pageData);
                    }
                }
            }

            if (isset($dataset['subData'])) {
                foreach ($dataset['subData'] as $formID => &$formDatas) {
                    $formSchema = $pageOptions['bodyForms'][$formID];
                    foreach ($formDatas as &$data) {
                        $id = isset($id) ? $id : $dataset['data'][$dataKey];
                        $result = $saveData($data, $formSchema, null, $id);
                        if (!$result) {
                            $passed = false;
                            break 2;
                        }
                    }
                }
            }

            return [
                'passed' => $passed,
                'headID' => $id
            ];
        };

        // Permission check
        if ($data['status'] == 'update' && !$permission['permission_update'])
            abort(403);
        if ($data['status'] == 'add' && !$permission['permission_insert']) {
            abort(403);
        }

        // Check page options data max
        if ($data['status'] == 'add' && $pageData['page']['page_options']['data_max'] > 0)
            if (DB::table($headTable)->count() >= $pageData['page']['page_options']['data_max']) {
                $addError(resolve('commonTranslations')['data_count_exceeded'], -1);
            }

        // Save data
        $result = $saveData($data, $pageOptions['headForm'], $headTable, '');

        if (count($errors) !== 0) {
            $injectClass->afterFailSave($data, $pageData);
            if (!in_array("noCommit", $options))
                DB::rollback();

            return [
                'status' => false,
                'errors' => $errors,
                'headID' => null
            ];
        } else {
            if (isset($data['deletedData'])) {
                foreach ($data['deletedData'] as $deletedData) {
                    $form = DataUtil::arraySearch($pageData['forms'], function ($form) use ($deletedData) {
                        return $form['form_id'] == $deletedData['form_id'];
                    });

                    if($form == null) continue;

                    $tableName = $pageData['page']['page_code'] . '_' . $form['form_id'];

                   foreach ($form['fields'] as $formField) {
                       if ($formField['field_type'] == 'reference_page') {
                            $originalData = (array) DB::table($tableName)->where('id', $deletedData['id'])->first();
                            //dd($formField['pageData']);
                           // dd($formField['field_code']);
                           // PageUtil::deleteData($formField['pageData'], $originalData[$formField['field_code']]);
                        }
                 }

                    $oldData = (array) DB::table($tableName)->where('id', $deletedData['id'])->first();
                    FileUtil::addDeleteFile($deleteFiles, null, $oldData, $form);
                    DB::table($tableName)->where('id', $deletedData['id'])->delete();
                    LogUtil::addFormLog($page_id, $form['form_id'], $deletedData['id'], $oldData['parent_id'], 'delete', null, $oldData);
                }
            }

            PageUtil::setVerify($page_id,$headTable,$pageOptions['dataKey'],$data);

            $injectClass->afterSuccessSave($data, $pageData);
            if (!in_array("noCommit", $options)) {
                FileUtil::deleteUploadedFiles($deleteFiles);
                FileUtil::saveUploadFiles($uploadFiles);
                DB::commit();
            }

            return [
                'status' => true,
                'errors' => $errors,
                'headID' => $result['headID']
            ];
        }
    }
    public function delete(Request $request, $page_id, $id)
    {
        // Check permission
        $permission = PermissionUtil::getCurrentUserPagePermission($page_id);
        if (!$permission['permission_delete'])
            abort(403);

        // Get page data
        $pageData = TranslationUtil::getPageDataWithTranslation($page_id);
        if ($pageData == null) {
            abort(404);
        }

        DB::beginTransaction();

        $data = PageUtil::getData($pageData, $id);


        // Inject functions
        $class = 'App\\Http\\Inject\\' . $pageData['path'];
        $injectClass = class_exists($class) ? new $class() : new InjectBase;

        $injectClass->beforeDelete($data, $pageData);

        if (isset($data['data']['data_options']) && isset($data['data']['data_options']['deletable']) && !$data['data']['data_options']['deletable']) abort(403);
        $result = PageUtil::deleteData($pageData, $id);

        if ($result) {
            $injectClass->afterDeleteSuccess($data, $pageData);
            DB::commit();
        } else {
            $injectClass->afterDeleteFail($data, $pageData);
            DB::rollback();
        }

        return response()->json(['status' => $result, 'message' => null], 200);
    }
}
