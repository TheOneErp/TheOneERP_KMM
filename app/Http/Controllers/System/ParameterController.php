<?php
namespace App\Http\Controllers\System;

use Request;
use Validator;

use App\Models\Parameter;

use App\Utils\PageUtil;
use App\Utils\TranslationUtil;

use App\Http\Controllers\System\SystemController as System;

class ParameterController extends SystemController{
    protected $pageId;
    public function __construct(){
        $this->pageId = PageUtil::getPageIdByPageCode("SY_PARAMETERS");
    }

    public function parameters_list(){
        if(System::systemAuth($this->pageId)){
            $show = System::showList($this->pageId);
            $show["languages"]["new"] = TranslationUtil::getTranslationByCode("new");
            $parameter = Parameter::orderBy('parameter_id','asc')->paginate(10);
            // dd($show);
            return view($show["list"])
            ->with('datas',$parameter)
            ->with("languages",$show["languages"])
            ->with("fields",$show["fields"]);
        }
    }

    public function parameters_form($type,$id = null){
        if(array_search($type, ['view', 'insert', 'update']) === false){
            abort(404);
        }else if(System::systemAuth($this->pageId, $type)){
            $page_data = TranslationUtil::getPageDataWithTranslation($this->pageId);

            $parameter_data = null;
            if( $type == "update" ){
                $parameter_data = Parameter::where('parameter_id', '=', $id)->first();
            }
            return view('system.form.parameters')
                ->with("page_data", $page_data)
                ->with('parameter_data', $parameter_data)
                ->with('type',$type);
        }
    }

    public function parameters_save($type,$id=null){
		if ($input = Request::all()) {
            app()->setLocale('jp');
            if($type == 'insert'){
                $rules = [
                    'parameter_code' => 'required|max:50|unique:parameters',
                    'parameter_value' => 'required|max:50',
                    'parameter_remarks' => 'required|max:200'
                ];
                $message = [
                    'parameter_code.required' => '名稱不能為空',
                    'parameter_code.max' => '名稱不能超過50個字',
                    'parameter_code.unique' => '名稱不能重複',
                    'parameter_value.required' => '值不能為空',
                    'parameter_value.max' => '值不能超過50個字',
                    'parameter_remarks.required' => '備註不能為空',
                    'parameter_remarks.max' => '備註不能超過200個字',
                ];
            }else{
                $rules = [
                    'parameter_code' => 'required|max:50',
                    'parameter_value' => 'required|max:50',
                    'parameter_remarks' => 'required|max:200'
                ];
                $message = [
                    'parameter_code.required' => '名稱不能為空',
                    'parameter_code.max' => '名稱不能超過50個字',
                    'parameter_value.required' => '值不能為空',
                    'parameter_value.max' => '值不能超過50個字',
                    'parameter_remarks.required' => '備註不能為空',
                    'parameter_remarks.max' => '備註不能超過200個字',
                ];
            }

            $validator = Validator::make($input, $rules, $message);
            // dd($input);
            if(isset($input['parameter_deletable'])){
                $parameter_deletable=1;
            }else{
                $parameter_deletable=0;
            }

            if ($validator->passes()) {
                if($type == 'insert'){
                    $parameters = new Parameter;

                    if($parameters){
                        $parameters->created_by = session("user_id");
                        $parameters->updated_by = session("user_id");
                        $parameters->parameter_code = $input['parameter_code'];
                        $parameters->parameter_value = $input['parameter_value'];
                        $parameters->parameter_remarks = $input['parameter_remarks'];
                        $parameters->parameter_deletable = $parameter_deletable;
                        $parameters->save();
                    }
                }else{
                    $parameters = Parameter::find($id);
                    if($parameters){
                        $parameters->created_by = session("user_id");
                        $parameters->updated_by = session("user_id");
                        $parameters->parameter_code = $input['parameter_code'];
                        $parameters->parameter_value = $input['parameter_value'];
                        $parameters->parameter_remarks = $input['parameter_remarks'];
                        $parameters->parameter_deletable = $parameter_deletable;
                        $parameters->save();
                    }
                }
				NotificationController::notification_setting_add($this->pageId,$type);
                return redirect()->route("parameters_list",['page_id'=>$this->pageId]);

            } else {
                return back()->withInput(Request::all())
                    ->withErrors($validator); // 若錯誤則印出錯誤訊息
            }

        } else {}

	}

    public function parameters_delete($id){
        if(System::systemAuth($this->pageId, "delete")){
            $parameter_data = Parameter::find($id);
            if($parameter_data){
                $parameter_data->delete();
                NotificationController::notification_setting_add($this->pageId,"delete");
                return redirect()->route("parameters_list",['page_id'=>$this->pageId])->withSuccess('刪除成功');
            }else{
                return redirect()->route("parameters_list",['page_id'=>$this->pageId])->withErrors(array('message' => '未能成功刪除id='.$id.'這筆資料，請連絡相關人員')); // 若錯誤則印出錯誤訊息
            }

            // return redirect("/parameters");
        }
    }
}
?>
