<?php

namespace App\Utils;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\Utils\DataUtil;
use App\Utils\UserUtil;
use App\Utils\SessionUtil;

use App\Models\Page;
use App\Models\User;
use App\Models\Permission;

class PermissionUtil
{
    public static function fetchUserPermissionFromDatabase($userID)
    {
        $user = User::find($userID);
        $groups = $user->groups();
        $permissions = [];
        $SystemIgnorePageCode = [
            "SY_MODULES","SY_PAGES","SY_FORMS","SY_FIELDS","SY_PARAMETERS"
        ];

        if (UserUtil::isAdmin($userID) || UserUtil::isRoot($userID)) {
            $pages = DataUtil::convertToArray(Page::all()->toArray());
            $DeveloperToolsPageIDs = [];

            $generatePermissionObject = function ($pageID) use ($userID) {
                return
                    [
                        'page_id' => $pageID,
                        'permission_target_id' => $userID,
                        'permission_type' => 'user',
                        'permission_read' => true,
                        'permission_insert' => true,
                        'permission_update' => true,
                        'permission_delete' => true,
                        'permission_allow_rw_all' => true
                    ];
            };

            foreach ($pages as $page) {
                if ($page['page_code'] == "DT" || in_array($page['page_code'],$SystemIgnorePageCode)) $DeveloperToolsPageIDs[] = $page['page_id'];
                if (in_array($page['page_id'], $DeveloperToolsPageIDs) || in_array($page['page_module'], $DeveloperToolsPageIDs)) {
                    $DeveloperToolsPageIDs[] = $page['page_id'];
                    if (UserUtil::isRoot()) {
                        array_push($permissions, $generatePermissionObject($page['page_id']));
                    }
                } else if (UserUtil::isAdmin()) {
                    array_push($permissions, $generatePermissionObject($page['page_id']));
                }
            }
        } else {
            $userPermission = $user->permissions()
                ->get()
                ->toArray();
            $groupsPermissions = Permission::whereIn("permission_target_id", $groups->map(function ($group) {
                return ($group->group_id);
            })->toArray())
                ->where("permission_type", "group")
                ->get()
                ->toArray();
            $agentUserPermission = DataUtil::convertToArray(DB::table('user_agent')
                ->join("user_agent_page", "user_agent.user_agent_id", "user_agent_page.user_agent_id")
                ->join("permissions",function($join){
                    $join->on("user_agent.user_id", "permissions.permission_target_id");
                    $join->on("user_agent_page.page_id", "permissions.page_id");
                })
                ->where("user_agent.user_agent_disabled_at", '>', Carbon::now()->format("Y-m-d H:m:s"))
                ->where("user_agent_page.user_agent_target_type", 'user')
                ->where("user_agent_page.user_agent_target_id", $userID)
                ->where("permissions.permission_type", 'user')
                ->select(
                    "user_agent.user_agent_enabled_at",
                    "user_agent.user_agent_disabled_at",
                    "user_agent.user_agent_enabled",
                    "permissions.page_id",
                    "permissions.permission_read",
                    "permissions.permission_insert",
                    "permissions.permission_update",
                    "permissions.permission_delete",
                    "permissions.permission_allow_rw_all"
                )->get()->toArray());
            $agentGroupsPermission = DataUtil::convertToArray(DB::table('user_agent')
                ->join("user_agent_page", "user_agent.user_agent_id", "user_agent_page.user_agent_id")
                ->join("permissions",function($join){
                    $join->on("user_agent.user_id", "permissions.permission_target_id");
                    $join->on("user_agent_page.page_id", "permissions.page_id");
                })
                ->where("user_agent.user_agent_disabled_at", '>', Carbon::now()->format("Y-m-d H:m:s"))
                ->where("user_agent_page.user_agent_target_type", 'group')
                ->whereIn("user_agent_page.user_agent_target_id", $groups->map(function ($group) {
                    return ($group->group_id);
                })->toArray())
                ->where("permissions.permission_type", 'user')
                ->select(
                    "user_agent.user_agent_enabled_at",
                    "user_agent.user_agent_disabled_at",
                    "user_agent.user_agent_enabled",
                    "permissions.page_id",
                    "permissions.permission_read",
                    "permissions.permission_insert",
                    "permissions.permission_update",
                    "permissions.permission_delete",
                    "permissions.permission_allow_rw_all"
                )->get()->toArray());
            $permissions = array_merge($userPermission, $groupsPermissions, $agentUserPermission, $agentGroupsPermission);
        }

        return $permissions;
    }

    public static function fetchUserPermissionFromCache($userID)
    {
        if (Cache::store('file')->has("user_permission_" . $userID)) {
            $userPermissions = Cache::store('file')->get("user_permission_" . $userID);
        }else{
            $userPermissions = PermissionUtil::fetchUserPermissionFromDatabase($userID);
            Cache::store('file')->put("user_permission_" . $userID, $userPermissions, now()->addMinutes(30));
        }
        return $userPermissions;
    }

    public static function getUserPermission($userID){
        $permissions = PermissionUtil::fetchUserPermissionFromCache($userID);
        $returnPermissions = [];
        foreach($permissions as $permission){
            if(array_key_exists('user_agent_enabled',$permission)){
                if($permission['user_agent_enabled'] && Carbon::now()->between($permission['user_agent_enabled_at'],$permission['user_agent_disabled_at'])){
                    $returnPermissions[] = $permission;
                }
            }else{
                $returnPermissions[] = $permission;
            }
        }
        return $returnPermissions;
    }


    public static function getCurrentUserPermission()
    {
        return PermissionUtil::getUserPermission(SessionUtil::getUserID());
    }

    public static function getCurrentUserPagePermission($pageID)
    {
        return PermissionUtil::getPermissionInArray(PermissionUtil::getCurrentUserPermission(), $pageID);
    }

    public static function getPermissionInArray($array, $pageID)
    {
        $result = [
            'permission_read' => false,
            'permission_insert' => false,
            'permission_update' => false,
            'permission_delete' => false,
            'permission_allow_rw_all' => false
        ];
        foreach (array_filter($array, function ($permission) use ($pageID) {
            return $permission['page_id'] == $pageID;
        }) as $permission) {
            $result['permission_read'] = $permission['permission_read']  ? true : $result['permission_read'];
            $result['permission_insert'] = $permission['permission_insert']  ? true : $result['permission_insert'];
            $result['permission_update'] = $permission['permission_update'] ? true : $result['permission_update'];
            $result['permission_delete'] = $permission['permission_delete'] ? true : $result['permission_delete'];
            $result['permission_allow_rw_all'] = $permission['permission_allow_rw_all'] ? true : $result['permission_allow_rw_all'];
        }
        return $result;
    }
}
