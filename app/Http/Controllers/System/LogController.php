<?php

namespace App\Http\Controllers\System;

use App\Utils\LogUtil;
use App\Utils\PageUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



use App\Http\Controllers\System\SystemController as System;
use App\Utils\DataUtil;

class LogController extends SystemController
{
    protected $pageID;

    public function __construct()
    {
        $this->pageID = PageUtil::getPageIdByPageCode("SY_LOG");
    }

    public function list()
    {
        if (System::systemAuth($this->pageID)) {
            $pageData = TranslationUtil::getPageDataWithTranslation($this->pageID);
            $logOptions = LogUtil::getLogOptions();
            $users = [];
            $pages = [];
            foreach($logOptions['pageDatas'] as $toAddPageData){
                $pages[] = [
                    'page_id' => $toAddPageData['page']['page_id'],
                    'page_module' => $toAddPageData['page']['page_module'],
                    'page_order' => $toAddPageData['page']['page_order'],
                    'translation' => $toAddPageData['page']['translation']
                ];
            }
            foreach($logOptions['users'] as $toAddUser){
                $users[] = [
                    'user_id' => $toAddUser['user_id'],
                    'name' => $toAddUser['name']
                ];
            }

            return view('system.list.log')
                ->with("users",$users)
                ->with("pageData", $pageData)
                ->with("pages", $pages)
                ->with("routes", [
                    "filter" => 'system.log.filter'
                ]);
        } else {
            abort(403);
        }
    }

    public function filter(Request $request)
    {
        if (System::systemAuth($this->pageID)) {
            $requestData = $request->all();
            $paginationCount =  isset($request->paginationCount) && is_numeric($request->paginationCount) ? (int) $request->paginationCount : 10;

            // Data from head form
            $filters = [];
            $sortBys = [];

            // Filters
            if (isset($request->filters) && ValidationUtil::isJSONString($request->filters)) {
                $filters = json_decode($request->filters);
            }

            // Sort
            if (isset($request->sortby) && ValidationUtil::isJSONString($request->sortby)) {
                $sortBys = json_decode($request->sortby);
            }

            // Return data w/ pagination
            $result = LogUtil::queryLogs($filters, $sortBys)->paginate($paginationCount);

            $result = DataUtil::convertToArray(json_decode(json_encode($result)));

            $result['data'] = LogUtil::fillNameToIDFields($result['data']);

            foreach ($result['data'] as &$log) {
                $log['data'] = null;
            }

            return $result;
        } else {
            abort(403);
        }
    }

    public function view($logID)
    {
        if (empty($logID)) return null;
        if (System::systemAuth($this->pageID)) {
            return LogUtil::getLog($logID);
        } else {
            abort(403);
        }
    }
}
