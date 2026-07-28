<?php
namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

use App\Models\User;
use App\Models\Group;
use App\Models\UserAgent;
use App\Models\UserAgentPage;
use App\Models\NotificationUser;
use App\Models\NotificationSetting;

use App\Http\Controllers\System\SystemController as System;

use App\Utils\UserUtil;
use App\Utils\PageUtil;
use App\Utils\DataUtil;
use App\Utils\SessionUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

class UserController extends SystemController{
    protected $pageId;
    protected $notificationUserId;
    protected $agentId;
    public function __construct(){
        $this->pageId = PageUtil::getPageIdByPageCode("SY_USERS");
        $this->agentId = PageUtil::getPageIdByPageCode("SY_USER_AGENT");
        $this->agentPageId = PageUtil::getPageIdByPageCode("SY_USER_AGENT_PAGE");
        $this->notificationUserId = PageUtil::getPageIdByPageCode("SY_NOTIFICATION_USER");
    }

	// list
	public function users_list(){
        if(System::systemAuth($this->pageId)){
            $show = System::showList($this->pageId);
            $translateFields = [
                "new","yes","no"
            ];
            $translations = TranslationUtil::getTranslationByCode($translateFields);
            // dd($translations);
            foreach($translations as $key => $field){
                $show["languages"][$key] = $field;
            }
            $user = User::orderBy('user_id','asc');
            if(!UserUtil::isRoot()) $user->where('username', '<>', 'root');
            $user = $user->paginate(10);
            // dd($show["fields"]);
            return view($show["list"])
            ->with('datas',$user)
            ->with("languages",$show["languages"])
            ->with("fields",$show["fields"]);
        }
	}
	// form
	public function users_form($type, $id=null){
        $personnal = $type === "update" && (int) $id === SessionUtil::getUserID();
        if(array_search($type, ['view', 'insert', 'update']) === false){
            abort(404);
        }else if(System::systemAuth($this->pageId, $type, false) || $personnal){
            $page_data = TranslationUtil::getPageDataWithTranslation($this->pageId);
            $notificationUser_data = TranslationUtil::getPageDataWithTranslation($this->notificationUserId);
            $agent_data = TranslationUtil::getPageDataWithTranslation($this->agentId);
            $agentPage_data = TranslationUtil::getPageDataWithTranslation($this->agentPageId);
            // dd($agent_data);
            $fields = [
                'user_setting','agent_setting','password_confirmation',"module","submodule","selecting",
                "page","clear","user","group",'loading','processing',
                'accessing','redirecting','error.unknown','contact_maintenance','save'
            ];
            $translations = TranslationUtil::getTranslationByCode($fields);
            foreach($translations as $key => $field){
                $languages[$key] = $field;
            }
            // dd($languages);

            $data = null;
            if($type !== "insert"){
                $data = $this->user_origin_data($id);
                if(is_null($data)){
                    return redirect()->route('users_form',['type'=>'insert']);
                    // abort(404);
                }else if(UserUtil::isRoot($id) && !UserUtil::isRoot()){
                    abort(403);
                }else{
                    $pageModules = PageUtil::getModules(false, $id);
                }
            }else{
                $pageModules = PageUtil::getModules(false);
            }
            // dd($pageModules);
            return view($page_data["page"]["page_form_template"])
            ->with("translations", $languages)
            ->with("page_data", $page_data)
            ->with("notificationUser_data", $notificationUser_data)
            ->with("agent_data", $agent_data)
            ->with("agentPage_data", $agentPage_data)
            ->with("pageModules",$pageModules)
            ->with("type", $type)
            ->with("data", $data)
            ->with("dataId", $id)
            ->with("PAGE_ID", $this->pageId);
        }else{
            abort(403);
        }
    }
    public function user_origin_data(int $id){
        $allData = [
            "user" => User::find($id),
        ];
        // dd($allData);
        $returnData = [];
        if(is_null($allData["user"])){
            return null;
        }else{
            $user = TranslationUtil::getPageDataWithTranslation($this->pageId);
            $notification_user = TranslationUtil::getPageDataWithTranslation($this->notificationUserId);
            $user_agent = TranslationUtil::getPageDataWithTranslation($this->agentId);
            $user_agent_page = TranslationUtil::getPageDataWithTranslation($this->agentPageId);
            // $agentData =
            // dd($user_agent);

            $allData["notification_user"] = User::find($id)->notificationUser;
            $allData["user_agent"] = User::find($id)->userAgent;
            foreach($allData as $key => $data){
                $fields = $$key["forms"][0]["fields"];
                $returnData["{$key}_setting"] = [];
                foreach($fields as $code => $field){
                    if($key === "user_agent" && ($code === "user_agent_enabled_at" || $code === "user_agent_disabled_at")){
                        if(is_null($data)){
                            $returnData["{$key}_setting"][$code] = null;
                        }else{
                            $date = date_create($data->{$code});
                            $returnData["{$key}_setting"][$code] = is_null($data->{$code}) ? null : $date->format('Y-m-d H:i:s');
                        }
                    }else{
                        $returnData["{$key}_setting"][$code] = is_null($data) ? null : $data->{$code};
                    }
                }
                // dd($returnData);
            }
            $returnData["user_agent_setting"]["pages"] = [];
            if(!is_null($allData["user_agent"])){
                $agentPage = $allData["user_agent"]->userAgentPage;
                // dd($allData["user_agent"], $agentPage);
                if($agentPage->isNotEmpty()){
                    foreach($agentPage as $page){
                        $targetId = $page->user_agent_target_id;
                        $targetName = null;
                        $targetType = $page->user_agent_target_type;
                        if(!empty($targetId)){
                            $targetData = null;
                            $targetFieldCode = "";
                            if($targetType === "user"){
                                $targetData = User::find($targetId);
                                $targetFieldCode = "name";
                            }else if($targetType === "group"){
                                $targetData = Group::find($targetId);
                                $targetFieldCode = "group_name";
                            }
                            if(is_null($targetData)){
                                $targetId = null;
                                $targetType = null;
                            }else{
                                $targetName = $targetData->$targetFieldCode;
                            }
                        }
                        $returnData["user_agent_setting"]["pages"][] = [
                            "page_id" => $page->page_id,
                            "user_agent_target_id" => $targetId,
                            "user_agent_target_name" => $targetName,
                            "user_agent_target_type" => $targetType,
                        ];
                    }
                }else{
                    $returnData["user_agent_setting"]["pages"] = [];
                }
            }

            unset($returnData["user_setting"]["password"]);
            // dd($returnData);
            return $returnData;
        }
    }
	// 新增修改
	/* public function users_save($type,$id=null){

		if ($input = Request::all()) {
			if($type == "update" ){
				//驗證
				$rules = [
					'usr_name' => 'required|max:30',
					'usr_password' => 'nullable|max:12|confirmed',
					'usr_email'=>'nullable|email',
					'usr_tel' => 'nullable|regex:/^[0-9]+$/',
					'usr_note' => 'nullable|max:200'
				];
				$message = [
					'usr_name.required' => '用戶名稱不能為空',
					'usr_name.max' => '用戶名稱不能超過30個字',
					'usr_password.max' => '用戶密碼最多12個英數字',
					'usr_password.confirmed' => '新密碼需跟確認密碼相同',
					'usr_email.email' => '用戶EMAIL需符合email格式',
					'usr_tel.regex' => '用戶電話只能使用數字',
					'usr_note.max' => '用戶備註不能超過200個字',
				];
				$user = User::where('user_id', '=', $id)->first();  //先把資料抓出來
				$notif = NotificationUser::where('user_id', '=', $id)->first();
			}else{
				//驗證
				$rules = [
					'username' => 'required|regex:/^[a-zA-Z0-9\s-]+$/|max:20|unique:users',
					'usr_name' => 'required|max:30',
					'usr_password' => 'required|max:12|confirmed',
					'usr_email'=>'nullable|email',
					'usr_tel' => 'nullable|regex:/^[0-9]+$/',
					'usr_note' => 'nullable|max:200'
                ];

				$message = [
					'username.required' => '用戶帳號不能為空',
					'username.regex' => '用戶帳號只能使用英文字母或數字',
					'username.max' => '用戶帳號不能超過20個字',
					'username.unique' => '用戶帳號不能重複',
					'usr_name.required' => '用戶名稱不能為空',
					'usr_name.max' => '用戶名稱不能超過30個字',
					'usr_password.required' => '用戶密碼不能為空',
					'usr_password.max' => '用戶密碼最多12個英數字',
					'usr_password.confirmed' => '新密碼需跟確認密碼相同',
					'usr_email.email' => '用戶EMAIL需符合email格式',
					'usr_tel.regex' => '用戶電話只能使用數字',
					'usr_note.max' => '用戶備註不能超過200個字',
				];
			}

            $validator = Validator::make($input, $rules, $message);


			if ( isset($input['usr_stop']) ){
				$usr_stop = 1;
			}else{
				$usr_stop  = 0;
			}

			$aas = $validator->errors();

			// dd($aas->messages());

            if ($validator->passes()) {
				//model

				if($type == "update" ){
					$user->updated_by = session("user_id");
					$user->name = $input['usr_name'];
					$user->user_disabled = $usr_stop;
					$user->user_remarks = $input['usr_note'];
					if( $input['usr_password'] != "" ){
						$input['usr_password'] = Hash::make($input['usr_password']);
						$user->password = $input['usr_password'];
					}

					$notif->notification_user_email = $input['usr_email'];
					$notif->notification_user_phone = $input['usr_tel'];
					$notif->updated_by = session("user_id");

					$user->save();
					$notif->save();
				}else{
					$input['usr_password'] = Hash::make($input['usr_password']);
					$PAGE_USER_FORM = User::create(['username' => $input['username'], 'password' => $input['usr_password'], 'name' => $input['usr_name'], 'user_disabled' => $usr_stop,  'user_remarks' => $input['usr_note'], 'created_by' => session("user_id"), 'updated_by' => session("user_id")]);
					$PAGE_NITIFICATION_USER_FORM = NotificationUser::create(['user_id' => $PAGE_USER_FORM->user_id, 'notification_user_phone' => $input['usr_tel'], 'notification_user_email' => $input['usr_email'], 'created_by' => session("user_id"), 'updated_by' => session("user_id")]);
				}

				NotificationController::notification_setting_add($this->pageId,$type);

                return redirect()->route('users_list',  ['page_id' => $this->pageId] )->withSuccess('新增成功'); //創建用戶後將用戶代碼傳到newuser並跳轉頁面

            } else {
                return back()->withInput(Request::all())->with('usr_stop', $usr_stop)->withErrors($validator); // 若錯誤則印出錯誤訊息
            }
        } else { //非POST則用GET開啟
        }
    } */
    public function users_save(Request $request, $type, $id = null){
        $personnal = $type === "update" && (int) $id === SessionUtil::getUserID();
        DB::beginTransaction();
        $result = [
            "success" => true,
            "errors" => [],
        ];
        $addError = function ($message, $messagePrefix, $tab, $id, $type, $formIndex, $rowIndex) use (&$result){
            $result["success"] = false;
            if (is_array($message)){
                foreach ($message as $singleMessage){
                    $result["errors"][] = ['message' => $messagePrefix . ' ' . $singleMessage, 'tab' => $tab, 'id' => $id, 'type' => $type, 'formIndex' => $formIndex, 'rowIndex' => $rowIndex];
                }
            }else{
                $result["errors"][] = ['message' => $messagePrefix . ' ' . $message, 'tab' => $tab, 'id' => $id, 'type' => $type, 'formIndex' => $formIndex, 'rowIndex' => $rowIndex];
            }
        };

        $user = TranslationUtil::getPageDataWithTranslation($this->pageId);
        $notification_user = TranslationUtil::getPageDataWithTranslation($this->notificationUserId);
        $user_agent = TranslationUtil::getPageDataWithTranslation($this->agentId);

        $validation_message = $user["validation"];
        $custom_message = TranslationUtil::getTranslationByCode(["username.regex"]);
        foreach($custom_message as $msgName => $msgValue){
            $validation_message[$msgName] = $msgValue;
        }

        $msgTranslations = TranslationUtil::getTranslationByCode([
            "user_setting","agent_setting","SY_TRANSLATION","row_with_number","page_head","page_body","of","field_no_details","field_type_error"
        ]);

        if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

        // dd($data);
        $SAVED_DATA = [];
        foreach(["user","notification_user","user_agent"] as $tableType){
            $toCreate = [
                "updated_by" => SessionUtil::getUserID()
            ];
            $toValidate = [];
            $skipCreate = false;
            $rules = [];
            $attributes = [];
            $keyData = [];
            $modelName = "App\\Models\\";
            foreach(explode('_',$tableType) as $n){
                $modelName = DataUtil::unionString($modelName,ucfirst($n),"");
            }

            foreach($$tableType["forms"][0]["fields"] as $key => $setting){
                if($key !== "user_id")
                    $toCreate[$key] = $data["{$tableType}_setting"][$key];

                $toValidate[$key] = $data["{$tableType}_setting"][$key];
                $pageRuleArray = $setting['field_rule'];
                // dump("{$key}=>",$setting['field_rule']);
                if($type == "update")
                    $data["{$tableType}_setting"]["data"]["user_id"] = $id;

                $pageRuleObject = ValidationUtil::generateFieldRuleObject($pageRuleArray, $setting, $data["{$tableType}_setting"], [
                    'dataKey' => "user_id",
                    'update' => $type == 'update',
                    'required' => $setting['field_required']
                ]);
                $rules[$key] = $pageRuleObject;
                $attributes[$key] = $setting['translation'];
            }

            if($tableType == "user"){
                $tab = "user_setting";
                $toCreate["password"] = Hash::make($data["user_setting"]["password"]);
                if($type === "update"){
                    ValidationUtil::unsetFieldRules("required",$rules["password"]);
                    $rules["password"][] = "nullable";
                    if($data["user_setting"]["password"] != "0" && empty($data["user_setting"]["password"])){
                        unset($toCreate["password"]);
                        ValidationUtil::unsetFieldRules("confirmed",$rules["password"]);
                    }
                }
                $toValidate["password_confirmation"] = $data["user_setting"]["password_confirmation"];
                $keyData = ["username" => $data["user_setting"]["username"]];
            }else if($tableType == "notification_user"){
                $tab = "user_setting";
                if(isset($SAVED_DATA["user"])){
                    $keyData = ["user_id" => $SAVED_DATA["user"]->user_id];
                }else{
                    $skipCreate = true;
                }
            }else if($tableType == "user_agent"){
                $tab = "agent_setting";
                if(isset($SAVED_DATA["user"])){
                    $keyData = ["user_id" => $SAVED_DATA["user"]->user_id];
                }else{
                    $skipCreate = true;
                }
                foreach(["user_agent_enabled_at","user_agent_disabled_at"] as $f){
                    if($toCreate["user_agent_enabled"] === false){
                        ValidationUtil::unsetFieldRules("required",$rules[$f]);
                        $rules[$f][]="nullable";
                    }
                    if(empty($toCreate[$f])) $toCreate[$f] = null;
                }

            }
            // dd($toValidate,$toCreate,$rules,$attributes);

            $pageValidation = ValidationUtil::validationData($toValidate, $rules, $validation_message, $attributes);
            if(!$pageValidation["passed"]){
                foreach($pageValidation["validator"]->errors()->toArray() as $errorField => $messages){
                    foreach($messages as $msg){
                        $addError($msg, $msgTranslations[$tab], $tab, $errorField, 'validation', null, null);
                    }
                }
            }else if(!$skipCreate){
                $dataExists = $modelName::where($keyData)->get()->isNotEmpty();
                if(!$dataExists){
                    $toCreate["created_by"] = SessionUtil::getUserID();
                }
                $SAVED_DATA[$tableType] = $modelName::updateOrCreate($keyData,$toCreate);
            }
        }
        // dd($SAVED_DATA);

        // dd($result);
        if($result["success"]){
            if($type === "update"){
                $SAVED_DATA["user_agent_page"] = [];
                $USER_AGENT_ID = $SAVED_DATA["user_agent"]->user_agent_id;
                foreach($data["user_agent_setting"]["pages"] as $page){
                    $keyData = [
                        "user_agent_id" => $USER_AGENT_ID,
                        "page_id" => $page['page_id']
                    ];
                    $toCreate = [];
                    foreach(["user_agent_target_id","user_agent_target_type"] as $f){
                        $toCreate[$f] = $page[$f];
                    }
                    $SAVED_DATA["user_agent_page"][] = UserAgentPage::updateOrCreate($keyData,$toCreate);
                }
                Cache::flush();
            }

            if($personnal){
                $result["redirect"] = route("index");
            }else{
                $result["redirect"] = route("users_list");
            }
            DB::commit();
            return response()->json($result,200);
        }else{
            DB::rollBack();
            return response()->json($result,400);
        }
    }

	// 刪除
	public function users_delete($id){
        if(System::systemAuth($this->pageId, "delete")){
            $data = User::where('user_id', '=', $id)->first()->delete();
            if($data){
                Cache::forget('userData_'.$id);
                return  redirect()->route('users_list',  ['page_id' => $this->pageId] )->withSuccess('刪除成功');
            }else{
                return redirect()->route("users_list",['page_id' => $this->pageId])->withErrors(array('message' => '未能成功刪除id='.$id.'這筆資料，請連絡相關人員')); // 若錯誤則印出錯誤訊息

            }
        }
	}
}
?>
