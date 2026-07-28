<?php
namespace App\Http\Controllers\System;

use App\Models\User;
use App\Models\Page;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Translation;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\NotificationTarget;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

use App\Mail\SendEmail;

use App\Utils\PageUtil;
use App\Utils\UserUtil;
use App\Utils\TranslationUtil;

use App\Http\Controllers\System\SystemController as System;

class NotificationController extends SystemController{
	protected $pageId;
    public function __construct(){
        $this->pageId = PageUtil::getPageIdByPageCode("SY_NOTIFICATION_SETTING");
    }

	//list
	public function notification_setting_list(){
        if(System::systemAuth($this->pageId)){
            $show = System::showList($this->pageId);
            $show["languages"]["new"] = TranslationUtil::getTranslationByCode("new");

            $msg = NotificationSetting::orderBy('notification_setting_id','asc')->paginate(10);
            foreach ($msg as $value){
                $page_code = Page::visible()->where('page_id',$value->page_id)->pluck('page_code')->first();
                $PageName = PermissionController::changeTranslation("page",$page_code);
                if( $value->notification_setting_target_type == "user"){
                    $userName = User::where('user_id', '=', $value->notification_setting_target)->pluck('name')->first();
                }else{
                    $userName = Group::where('group_id', '=', $value->notification_setting_target)->pluck('group_name')->first();
                }
                // $value->page_id = is_null($currentPageName) ? $defaultPageName : $currentPageName;
                $value->page_id = $PageName;
                $value->notification_setting_target = $userName;

            }

            return view($show["list"])
            ->with('datas',$msg)
            ->with("languages",$show["languages"])
            ->with("fields",$show["fields"]);
        }
	}
	//form
	public function notification_setting_form($type,$id=null){
        if(array_search($type, ['view', 'insert', 'update']) === false){
            abort(404);
        }
        else if(System::systemAuth($this->pageId, $type))
        {
            $data = null;
            //表單
            $pages = Page::visible()
                ->where('page_module', '<>', 0)
                ->whereNotNull('page_list_template')
                ->whereNotNull('page_form_template')
                ->get();
            $pageArr = [];
            $ugArr = [];
            $defaultTranslation = Translation::where('translation_type','page')->get();
            $currentTranslation = $defaultTranslation->where('language_id', session('language_id', 1));
            foreach ($pages as $page){
                $defaultPageName = $defaultTranslation->where('translation_code',$page->page_code)->pluck('translation')->first();
                $currentPageName = $currentTranslation->where('translation_code',$page->page_code)->pluck('translation')->first();

                array_push($pageArr,[
                    "page_id" => $page->page_id,
                    "page_text" => is_null($currentPageName) ? $defaultPageName : $currentPageName,
                    "page_module" => $page->page_module,
                    "page_code" => $page->page_code,
                    "page_order" => $page->page_order,
                    "page_has_child" => is_null($page->page_list_template)
                ]);
            }

            $ugArr = UserUtil::getUserGroup(true,true);
            if( $type == "update" ){
                $data = NotificationSetting::where('notification_setting_id', '=', $id)->first();
                $target = NotificationTarget::where('notification_setting_id', '=', $data->notification_setting_id)->get();

                foreach( $ugArr as $key=>$value){
                    $ugArr[$key]['check'] = 0;
                    foreach( $target as $tKey=>$tValue){

                        if( $tValue['notification_target'] == $value['target_id'] && $tValue['notification_target_type'] == $value['target_type'] ){
                            $ugArr[$key]['check'] = 1;

                        }

                    }
                }
            }
            // dd($ugArr);
            return view('system.form.notification_setting')->with('data',$data)->with('type',$type)->with('id',$id)->with('pageArr',$pageArr)->with('ugArr',$ugArr);
        }

	}

	//新增修改
    public function notification_setting_save($type,$id=null)
    {
		if ($input = Request::all()) {
			if($type == "update" ){
				//驗證
				$rules = [
					'page_id' => 'required',
					'msg_object' => 'required|array',
					'msg_object.*' => 'required',
					'msg_status' => 'required',
					'msg_content'=>'required'
				];
				$message = [
					'page_id.required' => '表單不能為空',
					'msg_object.required' => '通知對象不能為空',
					'msg_status.required' => '表單狀態不能為空',
					'msg_content.required' => '內容不能為空',
				];
				$notice = NotificationSetting::where('notification_setting_id', '=', $id)->first(); //先把資料抓出來
			}else{
				//驗證
				$rules = [
					'page_id' => 'required',
					'msg_object' => 'required|array',
					'msg_object.*' => 'required',
					'msg_status' => 'required',
					'msg_content'=>'required'
				];
				$message = [
					'page_id.required' => '表單不能為空',
					'msg_object.required' => '通知對象不能為空',
					'msg_status.required' => '表單狀態不能為空',
					'msg_content.required' => '內容不能為空',
				];
			}

            $validator = Validator::make($input, $rules, $message);

			if ( isset($input['msg_email']) ){
				$msg_email = 1;
			}else{
				$msg_email  = 0;
			}

			if ( isset($input['msg_sms']) ){
				$msg_sms = 1;
			}else{
				$msg_sms  = 0;
			}
            if ($validator->passes() ) {

				if($type == "update" ){
					NotificationTarget::where('notification_setting_id', '=', $notice->notification_setting_id)->delete(); //先把之前的刪掉
					$notice->updated_by = session("user_id");
					$notice->page_id = $input['page_id'];
					$notice->notification_setting_trigger_type = $input['msg_status'];
					$notice->notification_setting_content = $input['msg_content'];
					$notice->notification_setting_mail = $msg_email;
					$notice->notification_setting_phone = $msg_sms;
					$notice->save();
					$noticeId = $notice->notification_setting_id;
					$creator = $notice->created_by;
					$updated = session("user_id");

				}else{
					$NOTIFICATION = NotificationSetting::create([
						'page_id'=> $input['page_id'],
						'notification_setting_trigger_type'=> $input['msg_status'],
						'notification_setting_content'=> $input['msg_content'],
						'notification_setting_mail'=>  $msg_email,
						'notification_setting_phone'=>  $msg_sms,
						'created_by'=> session("user_id"),
						'updated_by'=> session("user_id")
					]);

					$creator = session("user_id");
					$updated = session("user_id");
					$noticeId = $NOTIFICATION->notification_setting_id;
				}

				$targetData = [];
				foreach($input['msg_object'] as $key=>$value ){
					$targetArr = explode("_",$value);
					$target_type = $targetArr[0];
					$target_id = $targetArr[1];
					// $targets = [];
					NotificationTarget::create([
						'notification_setting_id'=> $noticeId,
						'notification_target'=> $target_id,
						'notification_target_type'=> $target_type,
						'created_by'=> $creator,
						'updated_by'=>  $updated
					]);

				}
				self::notification_setting_add($this->pageId,$type);
                return redirect()->route('notification_setting_list',  ['page_id' => $this->pageId] ); //創建用戶後將用戶代碼傳到newuser並跳轉頁面
            } else {
				return back()->withInput(Request::all())->with('msg_email', $msg_email)->with('msg_sms', $msg_sms)->withErrors($validator); // 若錯誤則印出錯誤訊息
            }
        }
	}

	//刪除
	public function notification_setting_delete($id){
        if(System::systemAuth($this->pageId, "delete")){
            $data = NotificationSetting::where('notification_setting_id', '=', $id)->first();
            if($data){
                NotificationTarget::where('notification_setting_id', '=', $data->notification_setting_id)->delete(); //先把之前的刪掉
                $data->delete();
                self::notification_setting_add($this->pageId,"delete");
                return redirect()->route('notification_setting_list',  ['page_id' => $this->pageId])->withSuccess('刪除成功');
            }else{
                return redirect()->route('notification_setting_list',  ['page_id' => $this->pageId])->withErrors(array('message' => '未能成功刪除id='.$id.'這筆資料，請連絡相關人員')); // 若錯誤則印出錯誤訊息
            }
        }
	}

	//新增通知細項
    public static function notification_setting_add($pageType,$status)
    { //頁面 , 動作 update delete insert
		//規則

		$notice = NotificationSetting::where('page_id', '=', $pageType)->where('notification_setting_trigger_type', $status)->get();
		if( $notice ){


			$notification_setting_content = "";

			foreach($notice as $key=>$value){
				$NoTarget = NotificationTarget::where('notification_setting_id', '=', $value->notification_setting_id)->get();
				$userArr = [];
				$notification_setting_content = $value->notification_setting_content; //內容
				$page_id  = $value->page_id; //頁面
				$notification_setting_mail = $value->notification_setting_mail;//mail勾選
				$notification_setting_phone = $value->notification_setting_phone;//簡訊勾選
				$createtor = session("user_id");

				$mailData = [];
				foreach($NoTarget as $key2=>$value2){
					if($value2->notification_target_type == 'user'){
						$notice_user = User::leftJoin('notification_user', function($join) {
							$join->on('users.user_id', '=', 'notification_user.user_id');
						})->where('users.user_id', $value2->notification_target)->get();
					}else{//群組
						$notice_user = GroupUser::leftJoin('users', 'users.user_id', '=', 'group_user.user_id')
						->leftJoin('notification_user', function($join) {
							$join->on('group_user.user_id', '=', 'notification_user.user_id');
						})->where('group_user.group_id', $value2->notification_target)->get();
					}

					foreach($notice_user as $key=>$userValue){
						if( $userValue->user_disabled != 1 ){
							if( !in_array($userValue->user_id,$userArr) ){
								array_push($userArr,$userValue->user_id);
								Notification::create([
									'notification_setting_id'=> $value->notification_setting_id,
									'user_id'=> $userValue->user_id,
									'notification_text'=> $notification_setting_content,
									'notification_link'=> $page_id,
									'notification_read'=>  0,
									'created_by'=>  $createtor,
									'updated_by'=>  $createtor
								]);


								//是否有email
								if( $notification_setting_mail == 1 ){
									$mailDetail = [];
									if( !is_null($userValue->notification_user_email) && $userValue->notification_user_email != "" ){
										$mailDetail=[
											'name'=>$userValue->name,
											'email'=>$userValue->notification_user_email
										];
										array_push($mailData,$mailDetail);
									}
								}
								//是否有sms
								if( $notification_setting_phone == 1 ){
									if( !is_null($userValue->notification_user_phone) && $userValue->notification_user_phone != "" ){
										self::notification_setting_sms($userValue->notification_user_phone,$value->notification_setting_content);
									}
								}
							}//if user not in $userArr
						}//if user can use
					}	//end of $notice_user
				}//end of user type $NoTarget
				self::notification_setting_email($mailData,$notification_setting_content);
			}// end of noti

		};
	}

	//抓取通知
	public static function notification_setting_selectNotice(){
		if ($input = Request::all()) {
			$notiHistory = $input['notiHistory'];
			$remainNum = $input['remainNum'];
		}else{
			$notiHistory = 1;
			$remainNum = 0;
		}
		$userId= session("user_id");

		if( $remainNum > 0){
			if( $notiHistory == 1 ){
				$notice = Notification::where('user_id', '=', $userId)->orderBy('notification_id','desc')->take(5)->get();
			}else{
				$notice = Notification::where('user_id', '=', $userId)->where('notification_id', '<', $notiHistory)->orderBy('notification_id','desc')->take(5)->get();
			}
			$noticeArr = [];
			$CountNum = count($notice);
			$remainNum = $remainNum - $CountNum;
			if( $notice ){
				foreach($notice as $key=>$value){
					$name = User::where('user_id',$value->created_by)->pluck('name')->first(); //人名
					$page_code = Page::visible()->where('page_id',$value->notification_link)->pluck('page_code')->first();
					$PageName = PermissionController::changeTranslation("page",$page_code);
					$noticeArr[$key]['id'] = $value->notification_id;
					$noticeArr[$key]['creator'] = is_null($name)?TranslationUtil::getTranslationByCode('user_deleted'):$name;
					$noticeArr[$key]['page'] = "「" . $PageName . "」";
					$noticeArr[$key]['page_link'] = $value->notification_link;
					$noticeArr[$key]['content'] = $value->notification_text;
					$noticeArr[$key]['time'] = date('Y-m-d', strtotime($value->created_at));
					$noticeArr[$key]['read'] = $value->notification_read;
					$noticeArr[$key]['remainNum'] = $remainNum;
				}
			}

			return $noticeArr;
		}else{
			$noticeArr[0]['remainNum'] = $remainNum;
			return $noticeArr;
		}





	}

	//已讀
	public function notification_setting_trandRead(){
		if ($input = Request::all()) {
			if( isset($input['target']) ){
				$id = $input['target'];
				$notice = Notification::where('user_id', '=', $id)->where('notification_read', '=', 0)->update(['notification_read' => 1]);  //先把資料抓出來
				if( $notice ){
					return 1;
				}else{
					return 0;
				}
				// $noticeArr = self::notification_setting_noticeOrNot();

			}else{
				$id = $input['id'];
				$notic = Notification::where('notification_id', '=', $id)->first();  //先把資料抓出來
				$notic->notification_read =1;
				$notic->save();
				return 1;
			}

		}
		// dd($id);

	}

	//是否有通知
	public static function notification_setting_noticeOrNot(){
		$userAccount = session("user_id");

		/* $notice = NotificationSetting::leftJoin('notification_target', function($join) {
			$join->on('notification_target.notification_setting_id', '=', 'notification_setting.notification_setting_id');
		})->where('page_id', '=', $pageType)->where('notification_setting_trigger_type', $status)->get(); */

		$notice = Notification::where('user_id', '=', $userAccount)->where('notification_read', '=', 0)->count();
		$remainNum = Notification::where('user_id', '=', $userAccount)->count();
		$noticeArr = [];
		$noticeArr['notice'] = $notice;
		$noticeArr['remainNum'] = $remainNum;
		return $noticeArr;

	}

	//email
	public static function notification_setting_email($mailData,$content){
		/* $to = collect([
           ['name' =>$name, 'email' => $email]
        ]); */
		$to = collect($mailData);

		$params = [
            'content' => $content
        ];
		try{
			Mail::to($to)->send(new SendEmail( $params ));
			return ;
		}
		catch(\Exception $e){
		}
		// return view('system.list.test')->with('FORM_NAME', 'test');;
	}

	//簡訊
	public static function notification_setting_sms($phone,$notice){
		
		try{
			$strOnlineSend = "http://www.smsgo.com.tw/sms_gw/sendsms.aspx?";
			$strOnlineSend .= "username=". env('SMS_ID'); //會員帳號
			$strOnlineSend .= "&password=". env('SMS_PASSWORD'); //會員密碼
			$strOnlineSend .= "&dstaddr=" . $phone ; //接收簡訊的手機號碼  暫定 $bi_cellphone
			$strOnlineSend .= "&encoding=BIG5";
			$strOnlineSend .= "&smbody=".urlencode("雲量系統通知 : " . $notice);
			$strOnlineSend .= "&response=".urlencode("");
			$file = @fopen($strOnlineSend, "r");
			return ;
		}
		catch(\Exception $e){
		}
		// return view('system.list.test')->with('FORM_NAME', 'test');;
	}
}
?>
