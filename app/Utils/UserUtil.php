<?php

namespace App\Utils;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;

class UserUtil{

    static public function isAdmin($userID = null){
        if($userID != null){
            $user = User::find($userID);
            if($user == null)
                return false;
            else
                return $user->username == 'admin' ||  $user->username == 'root';
        }
        return SessionUtil::getUsername() == 'admin' ||  SessionUtil::getUsername() == 'root';
    }
    static public function isRoot($userID = null){
        if($userID != null){
            $user = User::find($userID);
            if($user == null)
                return false;
            else
                return $user->username == 'root';
        }
        return SessionUtil::getUsername() == 'root';
    }

    static public function getUserGroup($getUser,$getGroup){
        $ugArr = [];
        if($getUser){
            $users = User::where('user_id', '<>', 0)->orderBy('user_id','asc')->get();
            foreach ($users as $user){
                array_push($ugArr,[
                    "target_id" => $user->user_id,
                    "target_username" => $user->username,
                    "target_name" => $user->name,
                    "target_type" => 'user',
                    "target_remarks" => $user->user_remarks,
                    "target_disabled" => $user->user_disabled
                ]);
            }
        }
        if($getGroup){
            $groups = Group::orderBy('group_id','asc')->get();

            foreach ($groups as $group){
                $group_member = GroupUser::where('group_id','=',$group->group_id)->get();
                $memberArr = [];
                foreach( $group_member as $member){
                    $users = User::find($member->user_id);
                    if(!is_null($users)){
                        array_push($memberArr,[
                            "member_id" => $users->user_id,
                            "member_username" => $users->username,
                            "member_name" => $users->name,
                            "member_type" => 'user',
                            "member_remarks" => $users->user_remarks,
                            "member_disabled" => $users->user_disabled
                        ]);
                    }
                }

                $group_member = $group_member->toArray();
                array_push($ugArr,[
                    "target_id" => $group->group_id,
                    "target_username" => $group->group_name,
                    "target_name" => $group->group_name,
                    "target_type" => 'group',
                    "target_remarks" => "",
                    "target_disabled" => "",
                    "member"=>$memberArr
                ]);
            }
        }
        return $ugArr;
    }

}
