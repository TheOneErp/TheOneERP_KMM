<?php

namespace App\Utils;

use App\Models\Log;
use App\Models\Page;
use App\Models\User;
use App\Models\Translation;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Utils\SessionUtil;
use App\Utils\TranslationUtil;

use Carbon\Carbon;

class LogUtil
{
    public static $ACTION_ADD = 1;
    public static $ACTION_UPDATE = 2;
    public static $ACTION_DELETE = 3;
    public static $ACTION_VERIFY_START = 4;
    public static $ACTION_VERIFY_CONFIRM = 5;
    public static $ACTION_VERIFY_RETURN = 6;
    public static $ACTION_VERIFY_INIT = 7;

    // 新增 Action 在上面，並需要在 getLogOptions => $data['translations'] 新增相對應的翻譯

    public static function getLogOptions()
    {
        $languageID = SessionUtil::getLanguageID();
        if (!Cache::has('logOptions_' . $languageID)) {
            $data = [
                'pageDatas' => [],
                'users' => [],
                'forms' => [],
                'translations' => []
            ];

            $pages = DataUtil::convertToArray(Page::all()->toArray());
            foreach ($pages as $page) {
                $pageData = TranslationUtil::getPageDataWithTranslation($page['page_id'], $languageID);
                $data['pageDatas'][$page['page_id']] = $pageData;
                $data['pageDatas'][$page['page_id']]['validation'] = null;
                foreach ($pageData['forms'] as $form) {
                    $data['forms'][$form['form_id']] = $form;
                }
            }

            $data['users'] = DataUtil::arrayToKeyValueArray(DataUtil::convertToArray(User::all()->toArray()), 'user_id');

            $commonTranslations = resolve('commonTranslations');
            $translations = Translation::whereIn('translation_code', ['page_head', 'page_body'])->get()->toArray();
            $data['translations'] = [
                LogUtil::$ACTION_ADD => $commonTranslations['add'],
                LogUtil::$ACTION_UPDATE => $commonTranslations['edit'],
                LogUtil::$ACTION_DELETE => $commonTranslations['delete'],
                LogUtil::$ACTION_VERIFY_START => $commonTranslations['verify.start'],
                LogUtil::$ACTION_VERIFY_CONFIRM => $commonTranslations['verify.confirm'],
                LogUtil::$ACTION_VERIFY_RETURN => $commonTranslations['verify.return'],
                LogUtil::$ACTION_VERIFY_INIT => $commonTranslations['verify.init'],
                'head' => TranslationUtil::getTranslationInArray($translations, 'page_head', $languageID),
                'body' => TranslationUtil::getTranslationInArray($translations, 'page_body', $languageID)
            ];

            Cache::put('logOptions_' . $languageID, $data, now()->addMinutes(30));
        }
        return Cache::get('logOptions_' . $languageID);
    }

    public static function addFormLog($pageID, $formID, $id = null, $parentID = null, $action, $data, $oldData = null)
    {
        $insertData = [
            'page_id' => $pageID,
            'form_id' => $formID,
            'id' => $id,
            'parent_id' => $parentID == "" ? null : $parentID,
            'created_by' => SessionUtil::getUserID()
        ];
        switch ($action) {
            case 'add':
                $insertData['action'] = LogUtil::$ACTION_ADD;
                $insertData['data'] = json_encode($data);
                break;
            case 'update':
                $insertData['action'] = LogUtil::$ACTION_UPDATE;
                $insertData['data'] = [];
                foreach ($oldData as $key => $value) {
                    if ($value != $data[$key]) {
                        $insertData['data'][$key] = [
                            'old' => $oldData[$key],
                            'new' => $data[$key]
                        ];
                    }
                }
                $insertData['data'] = json_encode($insertData['data']);
                break;
            case 'delete':
                $insertData['action'] = LogUtil::$ACTION_DELETE;
                $insertData['data'] = json_encode($oldData);
                break;
        }
        DB::table('logs')->insert($insertData);
    }

    public static function addVerificationLog($pageID, $id, $levelID, $data, $oldData, $action = "CONFIRM")
    {
        $action = "ACTION_VERIFY_$action";
        $population = $data["population"];
        $lastLevel = array_key_last($population);
        foreach($levelID as $lid){
            $lastVerifierIndex = array_key_last($population[$lastLevel][$lid]);
            $lastVerifier = $population[$lastLevel][$lid][$lastVerifierIndex];

            $toInsert = [
                "level" => $data['level'] == $oldData['level'] ? $data['level'] : "{$oldData['level']} -> {$data['level']}",
                "verifier" => $lastVerifier
            ];

            $insertData = [
                'page_id' => $pageID,
                'form_id' => "",
                'id' => $id,
                'parent_id' => null,
                'action' => LogUtil::$$action,
                'created_by' => SessionUtil::getUserID(),
                'data' => json_encode($toInsert)
            ];
            // dd($insertData);
            DB::table('logs')->insert($insertData);
        }
    }

    public static function queryLogs($filters, $sortBys)
    {
        $query = DB::table('logs');

        $filters = DataUtil::convertToArray($filters);
        $sortBys = DataUtil::convertToArray($sortBys);

        $pageFilters = array_filter($filters, function ($item) {
            return ($item['field'] == 'page_id');
        });
        $formFilters = array_filter($filters, function ($item) {
            return ($item['field'] == 'page_id');
        });
        $idFilters = array_filter($filters, function ($item) {
            return ($item['field'] == 'id');
        });
        $parentIDFilters = array_filter($filters, function ($item) {
            return ($item['field'] == 'parent_id');
        });
        $userFilters = array_filter($filters, function ($item) {
            return ($item['field'] == 'created_by');
        });
        $datetimeFilters = array_filter($filters, function ($item) {
            return ($item['field'] == 'created_at');
        });

        $operators = ['=', '!=', '>', '>=', '<', '<=', 'like', 'not like'];

        foreach ([$pageFilters, $formFilters, $idFilters, $parentIDFilters, $userFilters, $datetimeFilters] as $filterGroup) {
            if (count($filterGroup) > 0)
                $query->where(function ($q) use ($filterGroup, $operators) {
                    foreach ($filterGroup as $filter) {
                        if (isset($filter['field']) && isset($filter['operator']) && in_array($filter['operator'], $operators) && isset($filter['value']))
                            if ($filter['field'] == 'created_at') {
                                try {
                                    $result = Carbon::parse($filter['value']);
                                    $q->where('created_at', $filter['operator'], $filter['value']);
                                } catch (\Exception $e) {
                                }
                            } else {
                                $q->orWhere($filter['field'], $filter['operator'], $filter['value']);
                            }
                    }
                });
        }

        if (count($sortBys) == 0) $query->orderBy('log_id', 'desc');

        foreach ($sortBys as $sortBy) {
            if (isset($sortBy['order']) && ($sortBy['order'] == "desc" || $sortBy['order'] == "asc"))
                if (isset($sortBy['field']) && in_array($sortBy['field'], ['log_id', 'page_id', 'form_id', 'id', 'parent_id', 'action', 'created_at', 'created_by']))
                    $query->orderBy($sortBy['field'], $sortBy['order']);
        }

        return $query;
    }

    public static function queryVerificationLogs($pageID, $id = null, $orderSC = "ASC"){
        $result = [];
        $actions = [LogUtil::$ACTION_VERIFY_START,LogUtil::$ACTION_VERIFY_CONFIRM,LogUtil::$ACTION_VERIFY_RETURN,LogUtil::$ACTION_VERIFY_INIT];

        $all = Log::where('page_id', $pageID)
                ->whereIn('action', $actions)
                ->orderBy('created_at', $orderSC);

        if(!is_null($id)) $all->where('id', $id);
        foreach($all->get() as $data){
            array_push($result, $data->toArray());
        }

        return $result;
    }

    public static function fillNameToIDFields(&$logs)
    {
        $logOptions = LogUtil::getLogOptions();
        $doFillData = function (&$data) use ($logOptions) {
            foreach ($data as $key => &$value) {
                if ($key == 'created_by' || $key == 'updated_by') {
                    if (is_array($value) && isset($logOptions['users'][$value['old']]) && isset($logOptions['users'][$value['new']])) {
                        $value['old'] = $logOptions['users'][$value['old']]['name'];
                        $value['new'] = $logOptions['users'][$value['new']]['name'];
                    } else if (isset($logOptions['users'][$value])) {
                        $value = $logOptions['users'][$value]['name'];
                    }
                }
            }
        };
        $doFillLog = function ($log) use ($logOptions, $doFillData) {
            $log['ids'] = [
                'page_id' => $log['page_id'],
                'form_id' => $log['form_id'],
                'created_by' => $log['created_by'],
                'action' => $log['action']
            ];

            $log['data'] = DataUtil::convertToArray(json_decode($log['data']));
            $log['page_id'] = $logOptions['pageDatas'][$log['page_id']]['page']['translation'];
            $log['action'] = $logOptions['translations'][$log['action']];
            $log['created_by'] = $logOptions['users'][$log['created_by']]['name'];
            if(!empty($logOptions['forms'][$log['form_id']])){
                $form = $logOptions['forms'][$log['form_id']];
                $log['form_id'] = $form['form_type'] == 'head' ? $logOptions['translations']['head'] : $logOptions['translations']['body'] . $form['form_order'];
            }

            $doFillData($log['data']);

            return $log;
        };

        if (!array_key_exists('log_id', $logs)) {
            foreach ($logs as &$log) {
                $log = $doFillLog($log);
            }
        } else {
            $logs = $doFillLog($logs);
        }

        return $logs;
    }

    public static function getLog($logID)
    {
        $logOptions = LogUtil::getLogOptions();
        $data = DB::table('logs')->where('log_id', $logID)->get()->first();

        if ($data == null) {
            return null;
        } else {
            $data = (array) $data;
            $data = LogUtil::fillNameToIDFields($data);
            return [
                'data' =>  $data,
                'pageData' => isset($logOptions['pageDatas'][$data['ids']['page_id']]) ? $logOptions['pageDatas'][$data['ids']['page_id']] : null,
                'formData' => isset($logOptions['forms'][$data['ids']['form_id']]) ? $logOptions['forms'][$data['ids']['form_id']] : null,
            ];
        }
    }
}
