<?php
namespace App\Http\Controllers\System;

use Request;

use App\Models\User;
use App\Models\Group;
use App\Models\Permission;
use App\Models\Translation;
use App\Models\PermissionColumn;

use App\Utils\PageUtil;
use App\Utils\UserUtil;
use App\Utils\TranslationUtil;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\System\SystemController as System;

class PermissionController extends SystemController{
	protected $pageId;

    public function __construct(){
        $this->pageId = PageUtil::getPageIdByPageCode("SY_PERMISSIONS");
    }

	//form
	public function permission_form($type,$id=null,$user_type=null){
        if(array_search($type, ['view', 'insert', 'update']) === false){
            abort(404);
        }else if(System::systemAuth($this->pageId, ["insert","update"])){
            $data = null;
            if( $user_type == "group"){
                $data = Group::where('group_id', '=', $id)->first();
                $username = $data->group_name;
                $name = $data->group_name;
            }else{
                $data = User::where('user_id', '=', $id)->first();
                $username = $data->username;
                $name = $data->name;
            };

            $permission = Permission::where('permission_target_id', '=', $id)->where('permission_type',$user_type)->get();
            $pageData = PageUtil::getModules();
            $translations = TranslationUtil::getTranslationByCode(['selecting','module','submodule','page','clear']);
			$showData = TranslationUtil::getAllPageDataWithTranslation(false);
			$PermissionColumnData = PermissionColumn::all();
            foreach( $permission as $key=>$value){
                $permission_column = $PermissionColumnData->where('permission_id', '=', $value->permission_id)->all();
				$show = $showData->where('page_id','=',$value->page_id)->first();
                $allFields = [];
                if( $show["forms"] ){
                    foreach ($show["forms"] as $forms){
                        foreach ($forms["fields"] as $key=>$fields ){
                            array_push($allFields,[
                                "page_id" => $forms["page_id"],
                                "form_type" => $forms["form_type"],
                                "field_id" =>$fields["field_id"],
                                "translation" =>$fields["translation"]
                            ]);
                        }
                    }
                    if( array_key_exists($value->page_id, $pageData["page"]) ){
                        $pageData["page"][$value->page_id]["permission_read"] = $value->permission_read ;
                        $pageData["page"][$value->page_id]["permission_insert"] = $value->permission_insert ;
                        $pageData["page"][$value->page_id]["permission_update"] = $value->permission_update ;
                        $pageData["page"][$value->page_id]["permission_delete"] = $value->permission_delete ;
                        $pageData["page"][$value->page_id]["permission_allow_rw_all"] = !$value->permission_allow_rw_all ;
                        $pageData["page"][$value->page_id]["column"] = $permission_column ;
                        $pageData["page"][$value->page_id]["field"] = $allFields;
                    }else{
                        // dump($value->page_id . "no");
                    }
                }
            }
            $ugArr = UserUtil::getUserGroup(true,true);
			
            return view('system.form.permission_form')
                ->with('name',$name)
                ->with('user_type',$user_type)
                ->with('username',$username)
                ->with('data',$data)
                ->with("page", $pageData)
                ->with("translations", $translations)
                ->with("modules", $pageData["module"])
                ->with('pages',$pageData['page'])
                ->with('ugArr',$ugArr);
        }
	}

	//轉換語言
	public static function changeTranslation($type,$page_code){
		$defaultTranslation = Translation::where('translation_type',$type)->get();
        $currentTranslation = $defaultTranslation->where('language_id', session('language_id', 1));

		$defaultPageName = $defaultTranslation->where('translation_code',$page_code)->pluck('translation')->first();
		$currentPageName = $currentTranslation->where('translation_code',$page_code)->pluck('translation')->first();

		if( is_null($currentPageName) ){
			return $defaultPageName;
		}else{
			return $currentPageName;
		}

	}

	public function permission_getFields(){
		if ($input = Request::all()) {
			$pageId = $input['pid'];
			// $show = System::showList($pageId);
			$show = TranslationUtil::getPageDataWithTranslation($pageId);
			$allFields = [];
			foreach ($show["forms"] as $forms){
				foreach ($forms["fields"] as $key=>$fields ){
					array_push($allFields,[
						"page_id" => $forms["page_id"],
						"form_type" => $forms["form_type"],
						"fields" => [
							"field_id" =>$fields["field_id"],
							"translation" =>$fields["translation"]
						]
					]);
				}

			}
		}
		return response()->json($allFields);
	}

	//新增修改
	public function permission_save(){
		if ($input = Request::all()) {
			// dd($input);
			$user_account = $input['user_account'];
			$user_type = $input['user_type'];
			if( $user_type ==  "group"){
				$user = Group::where('group_name', '=', $user_account)->first();
				$id = $user->group_id;

			}else{
				$user = User::where('username', '=', $user_account)->first();  //先把資料抓出來
				$id = $user->user_id;
			}


			//刪除原本的
			$data = Permission::where('permission_target_id', '=', $id)->where('permission_type',$user_type)->get();
			if( count($data) != 0  ){

				foreach($data as $deleteItem){
					 PermissionColumn::where('permission_id', '=', $deleteItem->permission_id)->delete();
				}
				Permission::where('permission_target_id', '=', $id)->where('permission_type',$user_type)->delete();
			}

			foreach ($input['permission'] as $key=>$value){
				$permissionArr = [];

				$column = $value['column'];
				$page = $value['page'];
				$PERMISSION = Permission::create([
					'page_id'=> $key,
					'permission_target_id'=> $id,
					'permission_type'=> $user_type,
					'permission_read'=> $page['permission_read'],
					'permission_insert'=>  $page['permission_insert'],
					'permission_update'=>  $page['permission_update'],
					'permission_delete'=>  $page['permission_delete'],
					'permission_allow_rw_all'=>  !$page['permission_allow_rw_all'],
					'created_by'=> session("user_id"),
					'updated_by'=> session("user_id")
				]);

				$columnData = [];
				if( count($column) != 0 ){
					foreach($column as $columnKey=> $columnValue){
						$columnArr = [];
						$columnArr=[
							'field_id'=> $columnValue['field_name'],
							'permission_column_attribute'=> $columnValue['field_attribute'],
							'permission_column_logic'=> $columnValue['field_logic'],
							'permission_column_content'=>  is_null($columnValue['field_content'])?"":$columnValue['field_content'],
							'permission_column_relative'=>  $columnValue['field_related'],
							'permission_column_remarks'=>  is_null($columnValue['field_remark'])?"":$columnValue['field_remark'],
							'created_by'=> session("user_id"),
							'updated_by'=> session("user_id")
						];
						array_push($columnData,$columnArr);
					}
					$PERMISSION->permissionColumn()->createMany($columnData);
				}
            }
			Cache::flush();
            if($user_type != 'group')
                Cache::forget('userData_'.$id);
			return json_encode('儲存成功');
		} else { //非POST則用GET開啟
			return back()->withInput(Request::all())->withErrors($validator); // 若錯誤則印出錯誤訊息
        }
	}

	//權限複製
	public function permission_copy(){
		if ($input = Request::all()) {
			$uid = $input['uid'];
			$type = $input['type'];
			$user_account = $input['user_account'];
			$user_type = $input['user_type'];
			if( $user_type ==  "group"){
				$user = Group::where('group_name', '=', $user_account)->first();
				$id = $user->group_id;

			}else{
				$user = User::where('username', '=', $user_account)->first();  //先把資料抓出來
				$id = $user->user_id;
			}


			$data = Permission::where('permission_target_id', '=', $id)->where('permission_type',$user_type)->get();
			if( count($data) != 0  ){

				foreach($data as $deleteItem){
					 PermissionColumn::where('permission_id', '=', $deleteItem->permission_id)->delete();
				}
				Permission::where('permission_target_id', '=', $id)->where('permission_type',$user_type)->delete();
			}

			// $userAll = User::all();
			$permission = Permission::where('permission_target_id', '=', $uid)->where('permission_type',$type)->get();
			foreach( $permission as $key=>$value){
				$PERMISSION = Permission::create([
					'page_id'=> $value->page_id,
					'permission_target_id'=> $id,
					'permission_type'=> $user_type,
					'permission_read'=> $value->permission_read,
					'permission_insert'=>  $value->permission_insert,
					'permission_update'=>  $value->permission_update,
					'permission_delete'=>  $value->permission_delete,
					'permission_allow_rw_all'=>  $value->permission_allow_rw_all,
					'created_by'=> session("user_id"),
					'updated_by'=> session("user_id")
				]);
				//抓出被複製的
				$permission_column = PermissionColumn::where('permission_id', '=', $value->permission_id)->get();
				$permission_column = $permission_column->toArray();
				if( count($permission_column) != 0 ){
					$columnData = [];
					foreach( $permission_column as $cKey=>$cValue){
						$columnArr = [];
						$columnArr=[
							'field_id'=> $cValue['field_id'],
							'permission_column_attribute'=> $cValue['permission_column_attribute'],
							'permission_column_logic'=> $cValue['permission_column_logic'],
							'permission_column_content'=>  is_null($cValue['permission_column_content'])?"":$cValue['permission_column_content'],
							'permission_column_relative'=>  $cValue['permission_column_relative'],
							'permission_column_remarks'=>  is_null($cValue['permission_column_remarks'])?"":$cValue['permission_column_remarks'],
							'created_by'=> session("user_id"),
							'updated_by'=> session("user_id")
						];
						array_push($columnData,$columnArr);

					}
					$PERMISSION->permissionColumn()->createMany($columnData);
				}
			}
		}
        Cache::flush();
        return "success";
	}


}
