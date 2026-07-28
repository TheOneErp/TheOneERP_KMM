<?php
namespace App\Http\Controllers\System;

use Request;
use Validator;

use App\Models\Schedule;

use App\Utils\UserUtil;
use App\Utils\PageUtil;
use App\Utils\TranslationUtil;

use App\Http\Controllers\System\SystemController as System;
class ScheduleController extends SystemController{

    protected $pageId;
    public function __construct(){
        $this->pageId = PageUtil::getPageIdByPageCode("DT_SCHEDULES");
    }

    public function schedules_form($type,$id = null){
        if(!UserUtil::isRoot()){
            abort(403);
        }
        $page_data = TranslationUtil::getPageDataWithTranslation($this->pageId);
        $schedules_data = null;
        if( $type == "update" ){
			$schedules_data = Schedule::where('schedule_id', '=', $id)->first();
		}
        return view('system.form.schedules')
            ->with("page_data", $page_data)
            // ->with("field_data",$field_data)
            ->with('schedules_data', $schedules_data)
            ->with('type',$type);
    }
    public function schedules_save($type,$id=null){
        if(!UserUtil::isRoot()){
            abort(403);
        }
		if ($input = Request::all()) {
            app()->setLocale('jp');
            if($type == 'insert'){
                $rules = [
                    'schedule_name' => 'required|max:50|unique:schedules',
                    'schedule_fun' => 'required|max:50|unique:schedules',
                    'schedule_remarks' => 'required|max:200'
                ];
                $message = [
                    'schedule_name.required' => '名稱不能為空',
                    'schedule_name.max' => '名稱不能超過50個字',
                    'schedule_name.unique' => '名稱不能重複',
                    'schedule_fun.required' => '函數不能為空',
                    'schedule_fun.max' => '函數不能超過50個字',
                    'schedule_fun.unique' => '函數不能重複',
                    'schedule_remarks.required' => '備註不能為空',
                    'schedule_remarks.max' => '備註不能超過200個字',
                ];
            }else{
                $rules = [
                    'schedule_name' => 'required|max:50',
                    'schedule_fun' => 'required|max:50',
                    'schedule_remarks' => 'required|max:200'
                ];
                $message = [
                    'schedule_name.required' => '名稱不能為空',
                    'schedule_name.max' => '名稱不能超過50個字',
                    'schedule_fun.required' => '函數不能為空',
                    'schedule_fun.max' => '函數不能超過50個字',
                    'schedule_remarks.required' => '備註不能為空',
                    'schedule_remarks.max' => '備註不能超過200個字',
                ];
            }

            $validator = Validator::make($input, $rules, $message);

            if ($validator->passes()) {
                if($type == 'insert'){
                    $schedules = new Schedule;

                    if($schedules){
                        $schedules->created_by = session("user_id");
                        $schedules->updated_by = session("user_id");
                        $schedules->schedule_name = $input['schedule_name'];
                        $schedules->schedule_fun = $input['schedule_fun'];
                        $schedules->schedule_remarks = $input['schedule_remarks'];
                        $schedules->schedule_active = $input['schedule_active'];
                        $schedules->save();
                    }
                }else{
                    $schedules = Schedule::find($id);
                    if($schedules){
                        $schedules->created_by = session("user_id");
                        $schedules->updated_by = session("user_id");
                        $schedules->schedule_name = $input['schedule_name'];
                        $schedules->schedule_fun = $input['schedule_fun'];
                        $schedules->schedule_remarks = $input['schedule_remarks'];
                        $schedules->schedule_active = $input['schedule_active'];
                        $schedules->save();
                    }
                }
				NotificationController::notification_setting_add($this->pageId,$type);
                return redirect()->route("schedules_list",['page_id'=>$this->pageId]);

            } else {
                return back()->withInput(Request::all())
                    ->withErrors($validator); // 若錯誤則印出錯誤訊息
            }

        } else {}


	}
    public function schedules_list(){
        if(!UserUtil::isRoot()){
            abort(403);
        }
        $show = System::showList($this->pageId);
        $show["languages"]["new"] = TranslationUtil::getTranslationByCode("new");
        $schedule = Schedule::orderBy('schedule_id','asc')->paginate(10);
        // dd($show);
        return view($show["list"])
        ->with('datas',$schedule)
        ->with("languages",$show["languages"])
        ->with("fields",$show["fields"]);
    }
    public function schedules_del($id){
        if(!UserUtil::isRoot()){
            abort(403);
        }
        $schedules_data = Schedule::find($id);
        if($schedules_data){
            $schedules_data->delete();
			NotificationController::notification_setting_add($this->pageId,"delete");
            return redirect()->route("schedules_list",['page_id'=>$this->pageId])->withSuccess('刪除成功');// 若錯誤則印出錯誤訊息
        }else{
            return redirect()->route("schedules_list",['page_id'=>$this->pageId])->withErrors(array('message' => '未能成功刪除id='.$id.'這筆資料，請連絡相關人員')); // 若錯誤則印出錯誤訊息
        }
        // return redirect("/schedules");
    }
    public function schedules_run($id){
        if(!UserUtil::isRoot()){
            abort(403);
        }
        $schedules_data = Schedule::find($id);
        //dd($schedules_data->schedule_active);
        if($schedules_data->schedule_active == 0){
            //schtasks /Run /TN "Laravel sch"
            exec('schtasks /Run /TN "'.$schedules_data->schedule_name.'"',$out);
            if($out == null){
                $schedules_data->schedule_active = 0;
                $schedules_data->save();
                return redirect()->route("schedules_list",['page_id'=>$this->pageId])->withErrors(array('message' => '未能成功啟動'.$schedules_data->schedule_name.'此排程，請確認工作排程器目前狀態是否為就緒')); // 若錯誤則印出錯誤訊息
            }else{
                $schedules_data->schedule_active = 1;
                $schedules_data->save();
                return redirect()->route("schedules_list",['page_id'=>$this->pageId])->withSuccess('啟動成功');
            }
        }else if($schedules_data->schedule_active == 1){
            //schtasks /End /TN "Laravel sch"
            exec('schtasks /End /TN "'.$schedules_data->schedule_name.'"',$out);
            if($out == null){
                $schedules_data->schedule_active = 1;
                $schedules_data->save();
                return redirect()->route("schedules_list",['page_id'=>$this->pageId])->withErrors(array('message' => '未能成功暫停'.$schedules_data->schedule_name.'此排程，請確認工作排程器目前狀態是否為已停用')); // 若錯誤則印出錯誤訊息
            }else{
                $schedules_data->schedule_active = 0;
                $schedules_data->save();
                return redirect()->route("schedules_list",['page_id'=>$this->pageId])->withSuccess('暫停成功');
            }
        }else{
            return redirect()->route("schedules_list",['page_id'=>$this->pageId])->withErrors(array('message' => '未能成功更改id='.$id.'此排程狀態，請連絡相關人員')); // 若錯誤則印出錯誤訊息
        }
        // return redirect("/schedules");
    }
}
?>
