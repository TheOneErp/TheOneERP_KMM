<?php
namespace App\Http\Controllers\System;

use Request;
use Validator;
use Illuminate\Support\Facades\Cache;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\NotificationSetting;

use App\Utils\PageUtil;
use App\Utils\UserUtil;
use App\Utils\TranslationUtil;

use App\Http\Controllers\System\SystemController as System;
class GroupController extends SystemController{
    protected $pageId;
    public function __construct(){
        $this->pageId = PageUtil::getPageIdByPageCode("SY_GROUPS");
    }
    public function groups_list(){
        if(System::systemAuth($this->pageId)){
            $show = System::showList($this->pageId);
            $show["languages"]["new"] = TranslationUtil::getTranslationByCode("new");
            $group = Group::orderBy('group_id','asc')->paginate(10);
            //dd($group);
            return view($show["list"])
            ->with('datas',$group)
            ->with("languages",$show["languages"])
            ->with("fields",$show["fields"]);
        }
    }

    public function groups_form($type,$id = null){
        if(array_search($type, ['view', 'insert', 'update']) === false){
            abort(404);
        }else if(System::systemAuth($this->pageId, $type)){
            $page_data = TranslationUtil::getPageDataWithTranslation($this->pageId);
            $group_data = null;
            $group_user_data = null;
            $ugArr = [];
            $ugArr = UserUtil::getUserGroup(true,false);
            $checkgu = [];
            if( $type == "update" ){
                $group_data = Group::where('group_id', '=', $id)->first();
                $group_user_data = GroupUser::where('group_id', '=', $group_data->group_id)->get();
                //dd($group_user_data);

                foreach($ugArr as $gukey => $guval){
                    foreach($group_user_data as $guchkey => $guchval){
                        if($guchval->user_id == $guval['target_id']){
                            array_push($checkgu,[
                                "target_id" => $guval['target_id'],
                                "target_username" => $guval['target_username'],
                                "target_name" => $guval['target_name'],
                            ]);
                        }
                    }
                }
                /* foreach($checkgu as $guchkey => $guchval){
                    unset($ugArr[$guchkey]);
                } */

                foreach($ugArr as $gukey => $guval){
                    $ugArr[$gukey]['check'] = 0;
                    foreach($checkgu as $guchkey => $guchval){
                        if(($guchval['target_id'] == $guval['target_id'])){
                            $ugArr[$gukey]['check'] = 1;
                        }
                    }
                }
            }

            return view('system.form.groups')
            ->with("page_data", $page_data)
            // ->with("field_data",$field_data)
            ->with('group_data', $group_data)
            ->with('group_user_data', $group_user_data)
            ->with('ugArr', $ugArr)
            ->with('type',$type);
        }
    }

    public function groups_save($type,$id=null){

		if ($input = Request::all()) {
            //dd($input);
            if($type == 'insert'){
                $rules = [
                    'group_name' => 'required|unique:groups',
                    'tid' => 'required',
                ];
                $message = [
                    'group_name.required' => '群組名稱不能為空',
                    'tid.required' => '至少需有一位群組成員',
                ];
            }else{
                $rules = [
                    'group_name' => 'required|max:50',
                    'tid' => 'required',
                ];
                $message = [
                    'group_name.required' => '群組名稱不能為空',
                    'group_name.max' => '群組名稱不能超過50個字',
                    'tid.required' => '至少需有一位群組成員'
                ];
            }

            $validator = Validator::make($input, $rules, $message);

            if ($validator->passes()) {
                if($type == 'insert'){
                    $groups = new Group;


                    if($groups){
                        $groups->created_by = session("user_id");
                        $groups->updated_by = session("user_id");
                        $groups->group_name = $input['group_name'];
                        $groups->save();
                        $gd = Group::where('group_name', '=', $input['group_name'])->first();
                        //dd($input['tid']);
                        foreach($input['tid'] as $key=>$value){
                            $groupuser = new GroupUser;
                            $groupuser->created_by = session("user_id");
                            $groupuser->updated_by = session("user_id");
                            $groupuser->group_id = $gd->group_id;
                            $groupuser->user_id = $value;
                            $groupuser->save();
                        }

                    }
                }else{
                    $groups = Group::find($id);
                    if($groups){
                        $groups->created_by = session("user_id");
                        $groups->updated_by = session("user_id");
                        $groups->group_name = $input['group_name'];
                        $groups->save();
                        $gd = Group::where('group_name', '=', $input['group_name'])->first();
                        $gd_delete = GroupUser::where('group_id', '=', $gd->group_id)->delete();
                        foreach($input['tid'] as $key=>$value){
                            $groupuser = new GroupUser;
                            $groupuser->created_by = session("user_id");
                            $groupuser->updated_by = session("user_id");
                            $groupuser->group_id = $gd->group_id;
                            $groupuser->user_id = $value;
                            $groupuser->save();
                        }
                    }
                }
				NotificationController::notification_setting_add($this->pageId,$type);
                Cache::flush();
                return redirect()->route("groups_list",['page_id'=>$this->pageId]);
            } else {
                // dd($validator);
                return back()->withInput(Request::all())
                    ->withErrors($validator); // 若錯誤則印出錯誤訊息
            }

        } else {}

	}

    public function groups_delete($id){
        if(System::systemAuth($this->pageId, "delete")){
            $group_data = Group::find($id);
            $group_user_data = GroupUser::where('group_id', '=', $group_data->group_id);
            //dd($group_user_data);
            if($group_data){
                $group_data->delete();
                $group_user_data->delete();
                NotificationController::notification_setting_add($this->pageId,"delete");
                Cache::flush();
                return redirect()->route("groups_list",['page_id'=>$this->pageId])->withSuccess('刪除成功');
            }else{
                return redirect()->route("groups_list",['page_id'=>$this->pageId])->withErrors(array('message' => '未能成功刪除id='.$id.'這筆資料，請連絡相關人員')); // 若錯誤則印出錯誤訊息
            }
        }
    }
}
