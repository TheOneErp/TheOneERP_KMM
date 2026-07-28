<?php

namespace App\Utils;

use App\Models\Page;
use App\Models\User;

use App\Utils\UserUtil;
use App\Utils\DataUtil;

use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Http\Inject\InjectBase;

class VerifyUtil
{
    public static function getVerifyByUserId(int $userId){
        $allVerify = [];
        $user = User::find($userId);
        $allTargets = [$user];
        $user->groups()->each(function ($group, $groupKey) use (&$allTargets){
            array_push($allTargets, $group);
        });

        foreach ($allTargets as $target){
            $target->verifyLevel->each(function ($level, $levelKey) use (&$allVerify){
                $levelData = $level->toArray();
                $verify = $level->verify;
                $verifyData = $verify->toArray();
                $page = $verify->page;
                if(!is_null($page)){
                    if(isset($allVerify[$page->page_id]))$verifyData =  &$allVerify[$page->page_id];
                    if(!isset($verifyData["levels"])) $verifyData["levels"] = [];

                    $level->verifyCondition->each(function($condition, $conditionKey) use (&$levelData){
                        $conditionData = $condition->toArray();
                        if(!isset($levelData["conditions"])) $levelData["conditions"] = [];
                        array_push($levelData["conditions"], $conditionData);
                    });
                    array_push($verifyData["levels"], $levelData);
                    if(!isset($allVerify[$page->page_id])) $allVerify[$page->page_id] = $verifyData;
                }
            });
        }

        // dd($allVerify);
        return $allVerify;
    }

    public static function getVerifyByPageId(int $pageId){
        $page = Page::find($pageId);
        if(!is_null($page)){
            $verify = $page->verifies;
            if(!is_null($verify)){
                $verifyData = $verify->toArray();
                $verify->verifyLevel->each(function ($level, $levelKey) use (&$verifyData){
                    $levelData = $level->toArray();
                    $level->verifyCondition->each(function ($condition, $conditionKey) use (&$levelData){
                        if(!isset($levelData["conditions"])) $levelData["conditions"] = [];
                        array_push($levelData["conditions"], $condition->toArray());
                    });
                    if(!isset($verifyData["levels"])) $verifyData["levels"] = [];
                    array_push($verifyData["levels"], $levelData);
                });

                // dd($verifyData);
                return $verifyData;
            }
        }
        return null;
    }

    public static function pageVerifyConfirmation(int $pageId){
        $verifyData = VerifyUtil::getVerifyByPageId($pageId);

        return !is_null($verifyData) && isset($verifyData["levels"]) && !empty($verifyData["levels"]);
    }

    public static function getDataWhichNeedsVerificationOfPage(int $pageId){
        $datas = [];
        if(VerifyUtil::pageVerifyConfirmation($pageId)){
            $pageVerify = VerifyUtil::getVerifyByPageId($pageId);
            $tableNames = PageUtil::getTableNameByPageId($pageId);
            if(!is_null($pageVerify) && isset($tableNames[0])){
                foreach($pageVerify["levels"] as $level){
                    $verifyLevel = $level["verify_level"];
                    if(!isset($datas[$verifyLevel])) $datas[$verifyLevel] = [];
                    $keys = ["verify_condition_group","verify_logical","field_code","verify_comparison","verify_value"];
                    $toCheckDatas = DatabaseUtil::groupExpression(DB::table($tableNames[0]),$level["conditions"],"AND",$keys);
                    foreach($toCheckDatas->get() as $data){
                        $data = (array) $data;
                        $dataOptions = isset($data["data_options"]) ? DataUtil::convertToArray(json_decode($data["data_options"])) : null;
                        if(!is_null($dataOptions) && isset($dataOptions["verify"]) && isset($dataOptions["verify"]["level"]) && $dataOptions["verify"]["level"] === (int)$verifyLevel){
                            $data["data_options"] = $dataOptions;
                            $datas[$verifyLevel][] = $data;
                        }
                    }
                }
            }
        }

        return $datas;
    }

    public static function getDataWhichNeedsVerification(int $pageId, $userId = null, bool $ignoreVerified = true){
        $datas = [];
        $userId = is_null($userId) ? SessionUtil::getUserID() : $userId;
        if(UserUtil::isRoot($userId)){
            foreach(VerifyUtil::getDataWhichNeedsVerificationOfPage($pageId) as $l){
                foreach($l as $d){
                    $datas[] = $d;
                }
            }
        }else if(VerifyUtil::pageVerifyConfirmation($pageId)){
            $allVerify = VerifyUtil::getVerifyByUserId($userId);
            $tableNames = PageUtil::getTableNameByPageId($pageId);
            if(isset($allVerify[$pageId]) && isset($tableNames[0])){
                $tableName = $tableNames[0];
                $verify = $allVerify[$pageId];
                foreach($verify["levels"] as $level){
                    $lid = $level["verify_level_id"];
                    $verifyLevel = $level["verify_level"];
                    $keys = ["verify_condition_group","verify_logical","field_code","verify_comparison","verify_value"];
                    foreach(DatabaseUtil::groupExpression(DB::table($tableName),$level["conditions"],"AND",$keys)->get() as $data){
                        $data = (array) $data;
                        $dataOptions = isset($data["data_options"]) ? DataUtil::convertToArray(json_decode($data["data_options"])) : null;
                        if(!is_null($dataOptions) && isset($dataOptions["verify"])){
                            $dataVerify = $dataOptions["verify"];
                            if(isset($dataVerify["level"]) && isset($dataVerify["population"])){
                                $level = $dataVerify["level"];
                                $population = $dataVerify["population"];
                                if($level === (int)$verifyLevel){
                                    $pass = true;
                                    if($ignoreVerified && isset($population[$level][$lid]) && in_array($userId, array_column($population[$level][$lid], "user_id"))){
                                        $pass = false;
                                    }
                                    if($pass) array_push($datas, $data);
                                }
                            }
                        }
                    }
                }
            }
        }

        // dd($datas);
        return $datas;
    }

    public static function getDataWhichCanReturnVerification(int $pageId, $userId = null){
        $datas = [];
        $userId = is_null($userId) ? SessionUtil::getUserID() : $userId;

        $tableNames = PageUtil::getTableNameByPageId($pageId);
        if(isset($tableNames[0])){
            $allDatas = DB::table($tableNames[0])->get();
            $datasNeedVerify = VerifyUtil::getDataWhichNeedsVerification($pageId, $userId, false);
            foreach($allDatas as $data){
                try {
                    $id = (int) $data->id;
                    $dataOptions = DataUtil::convertToArray(json_decode($data->data_options));
                    if(VerifyUtil::checkDataCanReturn($pageId, $id, $userId, $datasNeedVerify, $dataOptions)){
                        array_push($datas, (array) $data);
                    }
                } catch (\Throwable $th) {
                    // throw $th;
                }
            }
        }
        return $datas;
    }

    public static function checkDataCanReturn(int $pageId, int $dataId, $userId = null, $allDatas = null, $data = null){
        $result = false;
        $userId = is_null($userId) ? SessionUtil::getUserID() : $userId;
        $tableNames = PageUtil::getTableNameByPageId($pageId);
        $verifiable = in_array($dataId, array_column(is_null($allDatas) ? VerifyUtil::getDataWhichNeedsVerification($pageId, $userId, false) : $allDatas, 'id'));

        if($verifiable){
            $result = true;
        }else if(UserUtil::isRoot($userId)){
            $result = true;
        }else{
            try {
                $dataOptions = is_null($data) ? DataUtil::convertToArray(json_decode(DB::table($tableNames[0])->where('id', $dataId)->pluck('data_options')->first())) : $data;
                $verify = $dataOptions["verify"];
                $current = $verify["level"];
                $lastLevel = array_key_last($verify["population"]);
                $lastLevels = [];
                foreach($verify["population"][$lastLevel] as $level){
                    foreach($level as $verifier){
                        $lastLevels[] = $verifier;
                    }
                }
                usort($lastLevels, function ($a, $b){
                    $dateA = new DateTime($a["verify_at"]);
                    $dateB = new DateTime($b["verify_at"]);
                    if($dateA > $dateB || $dateA == $dateB){
                        return -1;
                    }else if($dateA < $dateB){
                        return 1;
                    }
                });
                // dd($lastLevels);
                $lastVerifier = $lastLevels[0]["user_id"];
                $result = $current === 255 && $lastVerifier === $userId;
            } catch (\Throwable $th) {
                // dd($th);
            }
        }

        return $result;
    }

    public static function executeDataVerify(int $pageId, int $dataId, $userId = null, $tableName, $errorMessages , &$result = null){
        if(is_null($result)){
            $result = [
                "success" => true,
                "messages" => [],
            ];
        }

        $q = DB::table($tableName)->where('id',$dataId)->lockForUpdate();
        $data = (array) $q->first();
        $dataOptions = DataUtil::convertToArray(json_decode($data["data_options"]));
        $userData = User::find($userId);
        $userVerify = VerifyUtil::getVerifyByUserId($userId);
        $pageVerify = VerifyUtil::getVerifyByPageId($pageId);
        $logAction = "CONFIRM";
        $class = 'App\\Http\\Inject\\' . PageUtil::getPagePathByPageId($pageId);
        $injectClass = class_exists($class) ? new $class() : new InjectBase;
        $levelPassed = function(&$population, $level = null) use ($userData, &$result, $errorMessages){
            $p = [
                'user_id' => $userData->user_id,
                'name' => $userData->name,
                'verify_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
            $l = is_null($level) ? 0 : $level["verify_level"];
            $lid = is_null($level) ? 0 : $level["verify_level_id"];
            $population[$l] = isset($population[$l]) ? $population[$l] : [];
            $pass = true;

            if(!isset($population[$l][$lid])) $population[$l][$lid] = [];
            if(array_search($p["user_id"],array_column($population[$l][$lid],"user_id")) === false){
                array_push($population[$l][$lid],$p);
                if(!is_null($level) && $level["verify_target_type"] === "group" && $level["verify_population"] > 1){
                    $pass = isset($population[$l][$lid]) && sizeof($population[$l][$lid]) >= $level["verify_population"];
                }

            }else{
                // error: You had verified this data.
                $result["success"] = false;
                $result["messages"][] = $errorMessages["verify.error.had_verified"];
            }

            return $pass;
        };

        // dd($data, $userData, $dataOptions);
        if(!empty($data) && !is_null($userData) && isset($dataOptions["verify"])){
            $injectClass->beforeExecuteVerify($data,$result);

            $oldVerify = $dataOptions["verify"];
            $current = $dataOptions["verify"]["level"];
            $population = array_key_exists("population",$dataOptions["verify"]) ? $dataOptions["verify"]["population"] : [];
            $lid = [];
            $toNextLevel = false;

            if((int) $current === 0 && (int) $data["created_by"] === $userId){
                $toNextLevel = $levelPassed($population);
                $logAction = "START";
                array_push($lid,0);
            }else if(isset($userVerify[$pageId]) || UserUtil::isRoot($userId)){
                $levels = UserUtil::isRoot($userId) ? $pageVerify["levels"] : $userVerify[$pageId]["levels"];
                $levelCorrespond = array_filter($levels, function($arr) use ($current){
                    return $arr["verify_level"] === $current;
                });
                if(sizeof($levelCorrespond) > 0){
                    $allDatas = VerifyUtil::getDataWhichNeedsVerification($pageId, $userId);
                    $dataCheck = array_search($dataId, array_column($allDatas, 'id')) !== false;
                    foreach($levelCorrespond as $level){
                        if($dataCheck){
                            $toNextLevel = $levelPassed($population,$level) || $toNextLevel;
                        }else{
                            // error: You don't have permission of verify this data.
                            $result["success"] = false;
                            $result["messages"][] = $errorMessages["verify.error.no_permission"];
                        }
                        array_push($lid, $level["verify_level_id"]);
                    }
                }else if($current === 255){
                    // error: The verify-level of data is highest.
                    $result["success"] = false;
                    $result["messages"][] = $errorMessages["verify.error.highest"];
                }else{
                    // error: You don't have permission of verify this data.
                    $result["success"] = false;
                    $result["messages"][] = $errorMessages["verify.error.no_permission"];
                }
            }else{
                // error: You don't have permission of verify this data.
                $result["success"] = false;
                $result["messages"][] = $errorMessages["verify.error.no_permission"];
            }

            if($result["success"]){
                usort($pageVerify["levels"], function($a, $b){
                    return $b["verify_level"] - $a["verify_level"];
                });

                $pl = $current + 1;
                while ($current == $oldVerify["level"] && $toNextLevel) {
                    if(in_array($pl, array_column($pageVerify["levels"],"verify_level"))){
                        $tempOptions = $dataOptions;
                        $tempOptions["verify"]["level"] = $pl;
                        $q->update(["data_options" => json_encode($tempOptions)]);
                        $needVerifyOfPage = VerifyUtil::getDataWhichNeedsVerificationOfPage($pageId);
                        if(isset($needVerifyOfPage[$pl]) && in_array($dataId, array_column($needVerifyOfPage[$pl],"id"))){
                            $current = $pl;
                        }else{
                            $pl++;
                        }
                    }else if($pl > $pageVerify["levels"][0]["verify_level"]){
                        $current = 255;
                    }else{
                        $pl++;
                    }
                }
                $dataOptions["verify"]["level"] = $current;
                $dataOptions["verify"]["population"] = $population;
                $dataOptions["editable"] = false;
                $dataOptions["deletable"] = false;
                $q->update(["data_options" => json_encode($dataOptions)]);
                // dd($pageId, $dataId, $lid, $dataOptions["verify"], $oldVerify, $logAction);
                LogUtil::addVerificationLog($pageId, $dataId, $lid, $dataOptions["verify"], $oldVerify, $logAction);

                $injectClass->afterSuccessExecuteVerify($data,$result);
                if($current == 255) $injectClass->afterLastestExecuteVerify($data,$result);
            }
        }else{
            $result["success"] = false;
            $result["messages"][] = $errorMessages["error.data_wrong"].$errorMessages["contact_maintenance"];
            $injectClass->afterFailedExecuteVerify($data,$result);
        }

        // return $result;
    }

    public static function returnDataVerify(int $pageId, int $dataId, $userId = null, $tableName,$errorMessages , &$result = null){
        if(is_null($result)){
            $result = [
                "success" => true,
                "messages" => [],
            ];
        }

        $q = DB::table($tableName)->where('id',$dataId)->lockForUpdate();
        $data = (array) $q->first();
        $dataOptions = DataUtil::convertToArray(json_decode($data["data_options"]));
        $userData = User::find($userId);
        $logAction = "RETURN";
        $class = 'App\\Http\\Inject\\' . PageUtil::getPagePathByPageId($pageId);
        $injectClass = class_exists($class) ? new $class() : new InjectBase;

        if(!empty($data) && !is_null($userData) && isset($dataOptions["verify"])){
            $injectClass->beforeReturnVerify($data,$result);

            $oldVerify = $dataOptions["verify"];
            $current = $dataOptions["verify"]["level"];
            $population = array_key_exists("population",$dataOptions["verify"]) ? $dataOptions["verify"]["population"] : null;

            if(VerifyUtil::checkDataCanReturn($pageId, $dataId, $userId)){
                $onlyLastest = false;
                if(isset($population[$current])){
                    $onlyLastest = $current === 255;
                    unset($population[$current]);
                }
                $originalLevel = $current;
                $current = $onlyLastest ? 0 : array_key_last($population);
                $population[$current]["-1"][0] = [
                    'user_id' => $userData->user_id,
                    'name' => $userData->name,
                    'verify_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ];

                $dataOptions["verify"]["level"] = $current;
                $dataOptions["verify"]["population"] = $population;
                LogUtil::addVerificationLog($pageId, $dataId, ["-1"], $dataOptions["verify"], $oldVerify, $logAction);
                if(isset($dataOptions["verify"]["population"][$current])){
                    unset($dataOptions["verify"]["population"][$current]);
                }

                if($current === 0){
                    $dataOptions["editable"] = true;
                    $dataOptions["deletable"] = true;
                }
                $q->update(["data_options" => json_encode($dataOptions)]);
                $injectClass->afterReturnVerify($data,$result);

                if($current === 0){
                    $injectClass->afterInitVerify($data,$result);
                }else if($originalLevel === 255){
                    $injectClass->afterLastestReturnVerify($data,$result);
                }
            }else{
                $result["success"] = false;
                $result["messages"][] = $errorMessages["verify.error.no_permission"];
            }
        }else{
            $result["success"] = false;
            $result["messages"][] = $errorMessages["error.data_wrong"].$errorMessages["contact_maintenance"];
        }


        // dd($q->get());
    }

    public static function initDataVerify(int $pageId, int $dataId, $userId = null, $tableName, $errorMessages, &$result = null){
        if(is_null($result)){
            $result = [
                "success" => true,
                "messages" => [],
            ];
        }

        $q = DB::table($tableName)->where('id',$dataId)->lockForUpdate();
        $data = (array) $q->first();
        $class = 'App\\Http\\Inject\\' . PageUtil::getPagePathByPageId($pageId);
        $injectClass = class_exists($class) ? new $class() : new InjectBase;
        $injectClass->beforeInitVerify($data,$result);

        if(!empty($data)){
            $dataOptions = DataUtil::convertToArray(json_decode($data["data_options"]));
            $originalLevel = isset($dataOptions["verify"]) && isset($dataOptions["verify"]["level"]) ? $dataOptions["verify"]["level"] : 0;
            if(VerifyUtil::checkDataCanReturn($pageId, $dataId, $userId)){
                $dataOptions["verify"] = [
                    "level" => 0,
                    "population" => [],
                ];
                $dataOptions["deletable"] = true;
                $dataOptions["editable"] = true;
            }else{
                $result["success"] = false;
                $result["messages"][] = $errorMessages["verify.error.no_permission"];
            }

            if($result["success"]){
                $q->update(["data_options" => json_encode($dataOptions)]);
                $injectClass->afterInitVerify($data,$result);
                if($originalLevel === 255){
                    $injectClass->afterLastestInitVerify($data,$result);
                }
            }
        }
    }

    public static function resetDataVerify(int $id){
        $verification = VerifyUtil::pageVerifyConfirmation($id);
        $tableNames = PageUtil::getTableNameByPageId($id);
        if(isset($tableNames[0])){
            $t = $tableNames[0];
            $allData = DB::table($t)->lockForUpdate()->get();
            foreach($allData as $data){
                $dataOptions = DataUtil::convertToArray(json_decode($data->data_options));
                $dataId = $data->id;

                if(isset($dataOptions['verify'])){
                    if($dataOptions["verify"]["level"] != 255){
                        $dataOptions["verify"] = [
                            "level" => 0,
                            "population" => [],
                        ];
                    }
                }else{
                    $dataOptions["verify"] = [
                        "level" => 255,
                        "population" => [
                            255 => [
                                "-1" =>[
                                    [
                                        "verify_at" => Carbon::now()->format('Y-m-d H:i:s'),
                                        "user_id" => 2,
                                        "name" => "Admin"
                                    ]
                                ]
                            ]
                        ],
                    ];
                }

                if($verification && $dataOptions["verify"]["level"] === 255){
                    $dataOptions["deletable"] = false;
                    $dataOptions["editable"] = false;
                }else{
                    $dataOptions["deletable"] = true;
                    $dataOptions["editable"] = true;
                }

                DB::table($t)->where('id', $dataId)->update(["data_options" => json_encode($dataOptions)]);
            }
        }
    }
}
