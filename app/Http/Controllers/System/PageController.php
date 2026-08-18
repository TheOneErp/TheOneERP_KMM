<?php
namespace App\Http\Controllers\System;

use App\Models\Page;
use App\Models\Form;
use App\Models\Permission;
use App\Models\Translation;

use App\Utils\UserUtil;
use App\Utils\DataUtil;
use App\Utils\PageUtil;
use App\Utils\SessionUtil;
use App\Utils\ValidationUtil;
use App\Utils\TranslationUtil;

use App\Http\Controllers\System\SystemController as System;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Staudenmeir\LaravelMigrationViews\Facades\Schema as View;

class PageController extends SystemController{
    private $pageId;
    private $modulePageId;

    public function __construct(){
        $this->pageId = PageUtil::getPageIdByPageCode("SY_PAGES");
        $this->modulePageId = PageUtil::getPageIdByPageCode("SY_MODULES");
    }

    public function pages_list(){
        if(System::systemAuth($this->pageId)){
            $show = System::showList($this->pageId);
            $page_data = TranslationUtil::getPageDataWithTranslation($this->pageId);
            $translateFields = [
                "new","module","submodule","selecting","cancel","clear","yes","no","unsave_confirm",
                "save","edit_order","save_success","page_module.min"
            ];
            $translations = TranslationUtil::getTranslationByCode($translateFields);
            // dd($translations);
            foreach($translations as $key => $field){
                $show["languages"][$key] = $field;
            }
            $fields["page_name"] = [
                "translation" =>TranslationUtil::getTranslationByCode("page_name"),
                "field_type" => "string",
                "field_code" => "page_name"
            ];
            foreach($page_data["forms"][0]["fields"] as $code => $field){
                if($field["field_show_on_list"] == "1"){
                    $fields[$code] = $field;
                }
            }

            $pageData = PageUtil::getModules();
            return view($page_data["page"]["page_list_template"])
            ->with("pageModule", $pageData)
            ->with("languages",$show["languages"])
            ->with("fields",$fields)
            ->with("PAGE_ID", $this->pageId);
        }
    }

    public function page_reorder(Request $request){
        if(System::systemAuth($this->pageId, "update")){
            if (ValidationUtil::isJSONString($request->getContent()))
                $data = DataUtil::convertToArray(json_decode($request->getContent()));
            else
                abort(400);
            // dd($data);

            DB::beginTransaction();
            $result = [
                "success" => true,
                "errors" => [],
            ];

            foreach($data as $page_id => $pageData){
                try {
                    $PAGE = Page::find($page_id);
                    $PAGE->page_order = $pageData["page_order"];
                    if($PAGE->isDirty('page_order')) $PAGE->save();
                } catch (\Throwable $th) {
                    $result["success"] = false;
                    array_push($result["errors"],$th->getMessage());
                }
            }

            if($result["success"]){
                DB::commit();
            }else{
                DB::rollBack();
            }
            return $result;
        }
    }

    public function pages_form($type, $id = null){
        if(array_search($type, ['view', 'insert', 'update']) === false){
            abort(404);
        }else if(System::systemAuth($this->pageId, $type)){
            $page_data = TranslationUtil::getPageDataWithTranslation($this->pageId);
            $SY_FIELDS_ID = PageUtil::getPageIdByPageCode("SY_FIELDS");
            $field_data = TranslationUtil::getPageDataWithTranslation($SY_FIELDS_ID);

            $fields = [
                "page_setting","field_setting","SY_TRANSLATION","module","submodule","selecting",
                "page","cancel","clear","page_template","page_body_number","page_has_body","page_max",
                "page_max_message",'savable','query_mode',"page_head","page_body",'string',
                'textarea','integer','decimal',
                'boolean','select','reference_select','checkboxes','radio','date','time',
                'datetime','file','reference','reference_page','button','loading','processing',
                'accessing','redirecting','error.unknown','contact_maintenance','save','position',
                'confirm','translation','default','custom','remove','fill_default_first',
                'unsave_confirm','editable' ,'cloneable','rule_unique','rule_distinct',
                'rule_word_limit','rule_number_limit','rule_digits_limit','integer_digits',
                'decimal_digits','number_digits','rule_max','rule_min','new','decimal_options','item',
                'options_options','rule_string_content','rule_letter_numeric','number',
                'upper_case','lower_case','underline','hyphen','and','or','unrestricted',
                'rule_url','rule_email','rule_in','rule_not_in','other','file_type',
                'file_image','file_video','file_audio','file_document','file_spread_sheet',
                'file_presentation','file_pdf','file_csv','file_archive','file_text',
                'common_setting','min_bigger','field_type_first',
                'row_with_number','page_allow_empty_body',"field_list","name",
                "reference_source_field","reference_source_table","reference_where","reference_other",
                "field","show","order","target","join_left","join_right","comparison_operator",
                "filter.group","logical_operator","value","reference_front","native_sql",
                "reference_multiple","no_page_module","cannot_remove_saved","field","form",
                "delete.confirm","type","list","attached_to","readonly"
            ];
            $translations = TranslationUtil::getTranslationByCode($fields);
            // dd($translations);
            foreach($translations as $key => $field){
                $languages[$key] = $field;
            }
            $fieldWithFormId = ['field_details', 'field_options', 'field_wide', 'wide_label'];
            $translationsWithFormId = TranslationUtil::getTranslationByCode($fieldWithFormId, 2, $SY_FIELDS_ID);
            foreach($translationsWithFormId as $key => $field){
                $languages[$key] = $field;
            }

            // dd($languages);
            $language_data = [];
            foreach (TranslationUtil::getAllLanguages() as $lan){
                array_push($language_data,[
                    "language_id" => $lan->language_id,
                    "language_code" => $lan->language_code,
                    "language_name" => $lan->language_name
                ]);
            }
            $modules = PageUtil::getModules(true);
            $data = null;
            if($type !== "insert"){
                $data = $this->page_origin_data($id);
                if(is_null($data)){
                    return redirect()->route('pages_form',['type'=>'insert']);
                    // abort(404);
                }else if($data["page_setting"]["page_readonly"] && $type == "update"){
                    abort(403);
                }/* else if(){ // TODO: 之後還要判斷list_template

                } */
            }
            // dd($page_data,$languages);
            return view($page_data["page"]["page_form_template"])
            ->with("translations", $languages)
            ->with("languages", $language_data)
            ->with("page_data", $page_data)
            ->with("field_data",$field_data)
            ->with("modules",$modules)
            ->with("type", $type)
            ->with("data", $data)
            ->with("dataId", $id)
            ->with("PAGE_ID", $this->pageId);
        }
    }

    public function page_origin_data($id){
        $allData = TranslationUtil::getPageDataWithTranslation($id);
        if(is_null($allData)){
            return null;
        }else{
            $allLanguage = TranslationUtil::getAllLanguages();
            // dump($allData);
            $pageData = $allData["page"];
            $formData = $allData["forms"];
            $page_setting = [
                "page_code" => $pageData["page_code"],
                "page_module" => $pageData["page_module"],
                "page_visible" => $pageData["page_visible"],
                "page_stay" => $pageData["page_stay"] ?? false,
                "page_readonly" => $pageData["page_readonly"],
                "page_form_template" => $pageData["page_form_template"],
                "page_has_body" => sizeof($formData) > 1,
                "page_body_number" => sizeof($formData) > 1 ? sizeof($formData) - 1 : sizeof($formData),
                "page_options" => $pageData["page_options"],
                "page_remarks" => $pageData["page_remarks"]
            ];
            $translation_setting = [];
            $form_setting = [];
            $field_setting = [];
            // dd($formData);
            foreach($formData as $formIndex => $form){
                array_push($form_setting,[
                    "form_id" => $form["form_id"],
                    "form_parent" => $form["form_parent"]
                ]);
                array_push($field_setting,[]);
                foreach($form["fields"] as $key => $field){
                    $details = isset($field['field_options']['field_details']) ? $field['field_options']['field_details'] : (Object)[];
                    unset($field["field_options"]['field_details']);
                    $field["field_options"] = empty($field["field_options"])
                    || sizeof($field["field_options"]) === 0 ? (Object)[] : $field["field_options"];
                    // 資料引用特殊處理
                    if($field["field_type"] == "reference" && isset($field["field_options"]["reference"])){
                        $view_datas = [];
                        $view_fields = [];
                        foreach ($field["field_options"]["reference"]["fields"] as $index => $value) {
                            $viewDatas = [
                                "module" => null,
                                "submodule" => null,
                                "page" => null,
                                "form" => null
                            ];
                            $tableNameSplit = explode("_",$value["table_name"]);
                            $form_id = $tableNameSplit[sizeof($tableNameSplit)-1];
                            unset($tableNameSplit[sizeof($tableNameSplit)-1]);
                            if(!isset($view_datas[$value["table_name"]])){
                                $page_code = implode("_",$tableNameSplit);
                                $page_data = TranslationUtil::getPageDataWithTranslationByPageCode($page_code);
                                $page_form = array_search($form_id,array_column($page_data["forms"],'form_id'));
                                $page_module = PageUtil::getPageDataWithParentsByPageId($page_data['page']['page_id']);
                                $translation = TranslationUtil::getTranslationByCode(['page_head', 'page_body']);

                                if(!empty($page_module)){
                                    $viewDatas["module"] = $page_module[0]["page"]["translation"];
                                    if(sizeof($page_module) > 1){
                                        $viewDatas["submodule"] = $page_module[sizeof($page_module)-1]["page"]["translation"];
                                    }
                                }
                                $viewDatas["page"] = $page_data["page"]["translation"];
                                $viewDatas["form"] = $page_form == 0 ? $translation["page_head"] : $translation["page_body"].$page_form;
                                // unset($viewDatas["field"]);
                                $view_datas[$value["table_name"]] = $viewDatas;
                                $view_fields[$value["table_name"]] = $page_data["forms"][$page_form]["fields"];
                            }else{
                                $temp = $view_datas[$value["table_name"]];
                                $viewDatas["module"] = $temp["module"];
                                $viewDatas["submodule"] = $temp["submodule"];
                                $viewDatas["page"] = $temp["page"];
                                $viewDatas["form"] = $temp["form"];
                            }
                            $viewDatas["field"] = TranslationUtil::getTranslationByCode($value["field_code"],null,$form_id);
                            $field["field_options"]["reference"]["fields"][$index]["view_datas"] = $viewDatas;
                        }
                        foreach ($field["field_options"]["reference"]["tables"] as $index => $value) {
                            $viewDatas = [
                                "module" => null,
                                "submodule" => null,
                                "page" => null,
                                "form" => null
                            ];
                            if(isset($view_datas[$value["table_name"]])){
                                $field["field_options"]["reference"]["tables"][$index]["view_datas"] = $view_datas[$value["table_name"]];
                                $field["field_options"]["reference"]["tables"][$index]["view_fields"] = $view_fields[$value["table_name"]];
                            }
                        }
                    }
                    // dump($field);
                    array_push($field_setting[$formIndex],[
                        "form_id" => $field['form_id'],
                        "field_code" => $field['field_code'],
                        "field_type" => $field['field_type'],
                        // "field_rule" => $field['field_rule'],
                        "field_order" => $field['field_order'],
                        "field_default_value" => $field['field_default_value'],
                        "field_required" => $field['field_required'],
                        "field_readonly" => $field['field_readonly'],
                        "field_show_on_form" => $field['field_show_on_form'],
                        "field_show_on_list" => $field['field_show_on_list'],
                        "field_options" => $field['field_options'],
                        "field_remarks" => $field['field_remarks'],
                        "field_details" => $details,
                        "translation" => [
                            "default" => [],
                            "custom" => []
                        ],
                    ]);
                }
                // dd();
                usort($field_setting[$formIndex],function ($a, $b){
                    return $a["field_order"] - $b["field_order"];
                });
            }
            // dd($field_setting);
            // dd($form_setting);
            foreach($allLanguage as $language){
                $langaugeId = $language->language_id;
                $hasPageTranslation = TranslationUtil::translationExists($pageData["page_code"],$langaugeId);
                $pageTranslation = TranslationUtil::getTranslationByCode($pageData["page_code"],$langaugeId);
                if($hasPageTranslation){
                    $translation_setting[$language->language_id] = $pageTranslation;
                }
                foreach($field_setting as $formIndex => $fields){
                    $formId = $form_setting[$formIndex]["form_id"];
                    foreach($fields as $fieldIndex => $field){
                        $hasDefault = TranslationUtil::translationExists($field["field_code"],$langaugeId);
                        $hasCustom = TranslationUtil::translationExists($field["field_code"],$langaugeId,$formId);
                        $defaultValue = null;
                        $customValue = null;
                        if($hasDefault){
                            $defaultTranslation = TranslationUtil::getTranslationByCode($field["field_code"],$langaugeId);
                            $defaultValue = $defaultTranslation;
                        }
                        if($hasCustom){
                            $customTranslation = TranslationUtil::getTranslationByCode($field["field_code"],$langaugeId,$formId);
                            $customValue = $customTranslation;
                        }
                        $field_setting[$formIndex][$fieldIndex]["translation"]["default"][$langaugeId] = $defaultValue;
                        $field_setting[$formIndex][$fieldIndex]["translation"]["custom"][$langaugeId] = $customValue;
                    }
                }
            }
            // dd($page_setting, $translation_setting, $form_setting, $field_setting);
            return [
                "page_setting" => $page_setting,
                "translation_setting" => (object) $translation_setting,
                "form_setting" => $form_setting,
                "field_setting" => $field_setting
            ];
        }
    }

    public function pages_save(Request $request, $type, $id = null){
        DB::beginTransaction();
        $result = [
            "success" => true,
            "errors" => [],
        ];
        $addError = function ($message, $messagePrefix, $tab, $id, $type, $formIndex, $rowIndex) use (&$result){
            $result["success"] = false;
            if (!is_array($message)){$message = [$message];}
            foreach ($message as $singleMessage){
                $result["errors"][] = ['message' => $messagePrefix . ' ' . $singleMessage, 'tab' => $tab, 'id' => $id, 'type' => $type, 'formIndex' => $formIndex, 'rowIndex' => $rowIndex];
            }
        };
        $parseFieldType = function($type){
            $types = [
                "" => ["button"],
                "text" => ["string","textarea","checkboxes","file","reference","select","radio","reference_page"],
                "integer" => ["integer"],
                "decimal" => ["decimal"],
                "date" => ["date"],
                "time" => ["time"],
                "dateTime" => ["datetime"],
                "boolean" => ["boolean"],
                // "enum" => ["select","radio"],
            ];
            foreach($types as $key => $arr){
                if(array_search($type,$arr) !== false){
                    return $key;
                }
            }
            return "";
        };

        // Page data
        $SY_FORMS_ID = PageUtil::getPageIdByPageCode("SY_FORMS");
        $SY_FIELDS_ID = PageUtil::getPageIdByPageCode("SY_FIELDS");

        $pages = TranslationUtil::getPageDataWithTranslation($this->pageId);
        $forms = TranslationUtil::getPageDataWithTranslation($SY_FORMS_ID);
        $fields = TranslationUtil::getPageDataWithTranslation($SY_FIELDS_ID);

        $validation_message = $pages["validation"];
        $custom_message = TranslationUtil::getTranslationByCode(["page_code.regex","page_module.min","page_options.native.no_sql","page_options.native.sql_error","field_code.regex"]);
        foreach($custom_message as $msgName => $msgValue){
            $validation_message[$msgName] = $msgValue;
        }

        $msgTranslations = TranslationUtil::getTranslationByCode([
            "page_setting","field_setting","SY_TRANSLATION","row_with_number","page_head","page_body","of","field_no_details","field_type_error"
        ]);

        if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);


        // TODO: page_list_template 也要能新增(?)
        $pageSetting = $data["page_setting"];
        $pageExists = PageUtil::getPageIdByPageCode($pageSetting["page_code"]);
        $pageSetting["page_readonly"] = false;
        $pageSetting["page_list_template"] = $pageSetting["page_form_template"];
        $modulePageCount = Page::where("page_module", $pageSetting["page_module"])->get()->count();

        $to_create_page = [
            'updated_by' => SessionUtil::getUserID()
        ];
        if(is_null($pageExists)){
            $to_create_page["created_by"] = SessionUtil::getUserID();
            $pageOrder = $modulePageCount;
        }else{
            $pageOrigin = Page::find($pageExists);
            $pageOrder = $pageOrigin->page_order;
            if(!$pageOrigin->page_visible || $pageOrigin->page_module != $pageSetting["page_module"]){
                $pageOrder = $modulePageCount;
            }
        }
        $pageSetting["page_order"] = $pageSetting["page_visible"] ? $pageOrder : 0;

        $to_create_page["page_stay"] = isset($pageSetting["page_stay"]) ? $pageSetting["page_stay"] : false;

        $pageToValidate = [];
        $page_rules = [];
        $page_attributes = [];
        foreach($pages["forms"][0]["fields"] as $key => $setting){
            if($key !== "page_code")
                $to_create_page[$key] = $pageSetting[$key];

            $pageToValidate[$key] = $pageSetting[$key];
            $pageRuleArray = $setting['field_rule'];
            if($type == "update")
                $pageSetting["data"]["page_id"] = $id;
            $page_rules[$key] = ValidationUtil::generateFieldRuleObject($pageRuleArray, $setting, $pageSetting, [
                'dataKey' => "page_id",
                'update' => $type == 'update',
                'required' => $setting['field_required']
            ]);
            $page_attributes[$key] = $setting['translation'];
        }

        $pageValidation = ValidationUtil::validationData($pageToValidate, $page_rules, $validation_message, $page_attributes);
        if(!$pageValidation["passed"]){
            foreach($pageValidation["validator"]->errors()->toArray() as $errorField => $messages){
                foreach($messages as $msg){
                    $addError($msg, $msgTranslations["page_setting"], "page_setting", $errorField, 'validation', null, null);
                }
            }
        }

        // Create or update page data
        $PAGE_DATA = Page::updateOrCreate(
            ["page_code" => $pageSetting["page_code"]],
            $to_create_page
        );
        $pageQuery = $PAGE_DATA->page_options["query_mode"];
        $pageNativeSQL = $pageQuery["native"];
        // Create or update page translation
        foreach ($data["translation_setting"] as $languageId => $translation){
            $nameRepeated = TranslationUtil::translationExists($PAGE_DATA->page_code,$languageId);
            $to_create_translation = [
                "translation" => $translation,
                'updated_by' => SessionUtil::getUserID(),
            ];
            if(!$nameRepeated){
                $to_create_translation['created_by'] = SessionUtil::getUserID();
            }
            Translation::updateOrCreate(
                [
                    "language_id" => $languageId,
                    "translation_code" => $PAGE_DATA->page_code,
                    "translation_type" => "page"
                ],
                $to_create_translation
            );
        }

        // Forms & Fields
        $FORMS_DATA = [];
        $TEMP_FIELDS = [];
        $formNumber = $pageSetting["page_has_body"] ? $pageSetting["page_body_number"] : 0;

        for($formIndex = 0; $formIndex <= $formNumber; $formIndex++)
        {
            // Create forms data
            $formSetting = $data["form_setting"][$formIndex];
            $form_parent = $formIndex>0 ? $FORMS_DATA[$formSetting["form_parent"]]->form_id : null;
            if(is_null($formSetting["form_id"])){
                $FORMS_DATA[$formIndex] = $PAGE_DATA->forms()->create([
                    'page_id' => $PAGE_DATA->page_id,
                    'form_order' => $formIndex,
                    'form_type' => $formIndex==0?"head":"body",
                    'form_parent' => $form_parent,
                    'created_by' => SessionUtil::getUserID(),
                    'updated_by' => SessionUtil::getUserID()
                ]);
            }else{
                $FORMS_DATA[$formIndex] = Form::find($formSetting["form_id"]);
                $FORMS_DATA[$formIndex]->form_order = $formIndex;
                $FORMS_DATA[$formIndex]->form_type = $formIndex==0?"head":"body";
                $FORMS_DATA[$formIndex]->form_parent = $form_parent;
                if($FORMS_DATA[$formIndex]->isDirty()){
                    $FORMS_DATA[$formIndex]->updated_by = SessionUtil::getUserID();
                    $FORMS_DATA[$formIndex]->save();
                }
            }

            $thisFormId = $FORMS_DATA[$formIndex]->form_id;
            $table_name = "{$PAGE_DATA->page_code}_{$thisFormId}";
            $form_fields = $data["field_setting"][$formIndex];
            $to_create_fields[$formIndex] = [];
            $formName = $formIndex === 0 ? $msgTranslations["page_head"] : "{$msgTranslations["page_body"]}{$formIndex}";
            $allLanguages = TranslationUtil::getAllLanguages();
            $systemFields = [
                [
                    'field_code' => 'id',
                    'field_type' => 'integer',
                    'field_rule' => [],
                    'field_order' => -6,
                    'field_required' => true,
                    'field_readonly' => true,
                    'field_show_on_list' => false,
                    'field_show_on_form' => false,
                    'field_options' => [
                        "system_field" => true,
                        "column_type" => '',
                        "column_modifiers" => [],
                        "field_details" => ["saved"=>true, "edited"=>true],
                    ],
                ],
                [
                    'field_code' => 'parent_id',
                    'field_type' => 'integer',
                    'field_rule' => [],
                    'field_order' => -5,
                    'field_required' => false,
                    'field_readonly' => true,
                    'field_show_on_list' => false,
                    'field_show_on_form' => false,
                    'field_options' => [
                        "system_field" => true,
                        "column_type" => 'bigInteger',
                        "column_modifiers" => [
                            [
                                "name" => "default",
                                "value" => -1
                            ],
                            [
                                "name" => "nullable",
                            ],
                        ],
                        "field_details" => ["saved"=>true, "edited"=>true],
                    ],
                ],
                [
                    'field_code' => 'created_by',
                    'field_type' => 'integer',
                    'field_rule' => [],
                    'field_order' => -4,
                    'field_required' => true,
                    'field_readonly' => true,
                    'field_show_on_list' => false,
                    'field_show_on_form' => false,
                    'field_options' => [
                        "system_field" => true,
                        "column_type" => 'integer',
                        "column_modifiers" => [
                            [
                                "name" => "default",
                                "value" => -1
                            ],
                        ],
                        "field_details" => ["saved"=>true, "edited"=>true],
                    ],
                ],
                [
                    'field_code' => 'updated_by',
                    'field_type' => 'integer',
                    'field_rule' => [],
                    'field_order' => -3,
                    'field_required' => true,
                    'field_readonly' => true,
                    'field_show_on_list' => false,
                    'field_show_on_form' => false,
                    'field_options' => [
                        "system_field" => true,
                        "column_type" => 'integer',
                        "column_modifiers" => [
                            [
                                "name" => "default",
                                "value" => -1
                            ],
                        ],
                        "field_details" => ["saved"=>true, "edited"=>true],
                    ],
                ],
                [
                    'field_code' => 'created_at',
                    'field_type' => 'datetime',
                    'field_rule' => [],
                    'field_order' => -2,
                    'field_required' => true,
                    'field_readonly' => true,
                    'field_show_on_list' => false,
                    'field_show_on_form' => false,
                    'field_options' => [
                        "system_field" => true,
                        "column_type" => 'timestamp',
                        "column_modifiers" => [
                            [
                                "name" => "useCurrent",
                            ],
                        ],
                        "field_details" => ["saved"=>true, "edited"=>true],
                    ],
                ],
                [
                    'field_code' => 'updated_at',
                    'field_type' => 'datetime',
                    'field_rule' => [],
                    'field_order' => -1,
                    'field_required' => true,
                    'field_readonly' => true,
                    'field_show_on_list' => false,
                    'field_show_on_form' => false,
                    'field_options' => [
                        "system_field" => true,
                        "column_type" => 'timestamp',
                        "column_modifiers" => [
                            [
                                "name" => "useCurrent",
                            ],
                        ],
                        "field_details" => ["saved"=>true, "edited"=>true],
                    ],
                ],
                [
                    'field_code' => 'data_options',
                    'field_type' => 'string',
                    'field_rule' => [],
                    'field_order' => -1,
                    'field_required' => true,
                    'field_readonly' => true,
                    'field_show_on_list' => false,
                    'field_show_on_form' => false,
                    'field_options' => [
                        "system_field" => true,
                        "column_type" => 'longText',
                        "column_modifiers" => [
                            [
                                "name" => "nullable",
                            ],
                        ],
                        "field_details" => ["saved"=>true, "edited"=>true],
                    ],
                ],
            ];

            array_push($TEMP_FIELDS,[]);
            if(!($pageQuery["enabled"] && $pageNativeSQL["enabled"])){
                $idIndex = array_search("id", array_column($systemFields, "field_code"));
                if($formIndex === 0 && $pageSetting["page_options"]["data_max"] > 0 && $pageSetting["page_options"]["data_max"] < 4294967296){
                    $systemFields[$idIndex]["field_options"]["column_type"] = "increments";
                }else{
                    $systemFields[$idIndex]["field_options"]["column_type"] = "bigIncrements";
                }
                foreach($systemFields as $sfIndex => $sf){
                    $fieldInFormIndex = array_search($sf["field_code"],array_column($form_fields,"field_code"));
                    $sf["field_order"] = 0 - (sizeof($systemFields) - (int) $sfIndex);
                    if($fieldInFormIndex !== false){
                        $sfInput = $form_fields[$fieldInFormIndex];
                        $sf["field_show_on_list"] = $sfInput["field_show_on_list"];
                        $sf["field_show_on_form"] = $sfInput["field_show_on_form"];
                        unset($form_fields[$fieldInFormIndex]);
                        $tempArray = [];
                        foreach($form_fields as $tempField){
                            array_push($tempArray, $tempField);
                        }
                        $form_fields = $tempArray;
                    }

                    array_push($form_fields, $sf);
                }
            }

            // Create fields data
            foreach($form_fields as $fieldIndex => $field){
                $FIELD_DATA = $FORMS_DATA[$formIndex]->fields()->where("field_code", $field["field_code"])->get();
                $to_create_fields[$formIndex][$fieldIndex] = [];

                if(!(array_key_exists("system_field",$field["field_options"]) && $field["field_options"]["system_field"] === true))
                {
                    $defaultValue_rule = $field["field_rule"];
                    $uniqueIndex = array_search("unique",$field["field_rule"]);
                    if($uniqueIndex !== false){
                        if(!empty($pageSetting["page_code"])){
                            $field["field_rule"][$uniqueIndex] = "unique:{$table_name}";
                        }else{
                            unset($defaultValue_rule[$uniqueIndex]);
                        }
                    }

                    $field["field_details"]["saved"] = true;
                    $field["field_options"]["field_details"] = $field["field_details"];

                    $rowName = str_replace(":row", $fieldIndex+1, $msgTranslations["row_with_number"]);
                    $errorName = empty($field["field_code"]) ? $rowName : $field["field_code"];
                    $fieldErrorPrefix = str_replace(":a", "{$msgTranslations["field_setting"]} {$formName}", str_replace(":b", $errorName, $msgTranslations["of"]));

                    $fieldTypeError = false;
                    if($FIELD_DATA->isNotEmpty()){
                        $newColumnType = $parseFieldType($field["field_type"]);
                        $oldColumnType = $parseFieldType($FIELD_DATA[0]->field_type);
                        if($oldColumnType !== "" && $newColumnType != $oldColumnType && $newColumnType != "text"){
                            $fieldTypeError = true;
                            $addError($msgTranslations["field_type_error"], $fieldErrorPrefix, "field_setting", "{$formIndex}field_type{$fieldIndex}", 'field_type_error', $formIndex, $fieldIndex);
                        }
                    }
                    if(!isset($field["field_details"]["edited"]) || !$field["field_details"]["edited"]){
                        $addError($msgTranslations["field_no_details"], $fieldErrorPrefix, "field_setting", null, 'no_details', $formIndex, $fieldIndex);
                    }else if(!$fieldTypeError){
                        $view_name = "{$PAGE_DATA->page_code}_{$thisFormId}_{$field["field_code"]}";
                        if($field["field_type"] == "reference"){
                            $referenceSetting = $field["field_options"]["reference"];
                            if(!is_null($referenceSetting["front_field"]["form_id"])){
                                $pos = $referenceSetting["front_field"]["form_id"];
                                $formId = isset($FORMS_DATA[$pos]) ? $FORMS_DATA[$pos]->form_id : 0;
                                $referenceSetting["front_field"]["form_id"] = $formId;
                            }
                            $nativeSQL = $referenceSetting["sql"]["native"];

                            if($nativeSQL["enabled"] && $result["success"]){
                                if(empty($nativeSQL["sql"])){
                                    $addError($custom_message["page_options.native.no_sql"],$fieldErrorPrefix,"field_setting",null,'no_details',$formIndex,$fieldIndex);
                                }else{
                                    try {
                                        View::createOrReplaceView($view_name,$nativeSQL["sql"]);
                                    } catch (\Throwable $th) {
                                        if($this->DEBUG_MODE){
                                            throw $th;
                                        }else{
                                            $addError($custom_message["page_options.native.sql_error"],$fieldErrorPrefix,"field_setting",null,'no_details',$formIndex,$fieldIndex);
                                        }
                                    }
                                }
                            }else{
                                View::dropViewIfExists($view_name);
                            }

                            foreach($referenceSetting["tables"] as $tableIndex => $ref_table){
                                unset($referenceSetting["tables"][$tableIndex]["view_datas"]);
                                unset($referenceSetting["tables"][$tableIndex]["view_fields"]);
                            }
                            foreach($referenceSetting["fields"] as $columnIndex => $ref_field){
                                unset($referenceSetting["fields"][$columnIndex]["view_datas"]);
                            }
                            $field["field_options"]["reference"] = $referenceSetting;
                        }else if($result["success"]){
                            View::dropViewIfExists($view_name);
                        }
                    }

                    $field_rules = [];
                    $field_attributes = [];
                    foreach ($fields["forms"][0]["fields"] as $key => $setting){
                        if($key != "form_id")
                            $to_create_fields[$formIndex][$fieldIndex][$key] = $field[$key];

                        if($key === "field_default_value"){
                            $fieldRuleArray = $defaultValue_rule;
                            $requiredIndex = array_search("required", $fieldRuleArray);
                            if($requiredIndex !== false){
                                unset($fieldRuleArray[$requiredIndex]);
                                $fieldRuleArray[] = "nullable";
                            }
                            if($field["field_type"] === "checkboxes"){
                                $setting["field_options"] = $field["field_options"];
                            }
                        }else{
                            $fieldRuleArray = $setting['field_rule'];
                        }
                        $field_update = ($type == 'update' && $FIELD_DATA->isNotEmpty());
                        if($field_update){
                            $field["data"]["field_id"] = $FIELD_DATA[0]->field_id;
                        }
                        $field_rules[$key] = ValidationUtil::generateFieldRuleObject($fieldRuleArray, $setting, $field, [
                            'dataKey' => "field_id",
                            'update' => $field_update,
                            'required' => $setting['field_required']
                        ]);
                        $field_attributes[$key] = $setting['translation'];
                    }
                    if($field["field_type"] === "checkboxes"){
                        $t = &$field["field_default_value"];
                        $t = empty($t) ? [] : explode(",",$t);
                    }
                    $fieldValidation = ValidationUtil::validationData($field, $field_rules, $validation_message, $field_attributes);

                    if(!$fieldValidation["passed"]){
                        foreach($fieldValidation["validator"]->errors()->toArray() as $errorField => $messages){
                            foreach($messages as $msg){
                                $errorId = "$formIndex{$errorField}$fieldIndex";
                                $addError($msg, $fieldErrorPrefix, "field_setting", $errorId, 'validation', $formIndex, $fieldIndex);
                            }
                        }
                    }else{
                        // Create field translation
                        foreach($allLanguages as $language){
                            $languageId = $language->language_id;
                            $languageType = TranslationUtil::translationExists($field["field_code"],$languageId) ? "custom" : "default";
                            $translationFormId = $languageType=="custom" ? $thisFormId : null;
                            $to_create_translation = [
                                "translation" => $field["translation"][$languageType][$languageId],
                                'updated_by' => SessionUtil::getUserID(),
                            ];
                            $translationRepeated = TranslationUtil::translationExists($field["field_code"],$languageId,$translationFormId);
                            if(!$translationRepeated){
                                $to_create_translation['created_by'] = SessionUtil::getUserID();
                            }
                            if(!empty($field["translation"][$languageType][$languageId])){
                                Translation::updateOrCreate(
                                    [
                                        "language_id" => $languageId,
                                        "translation_type" => "field",
                                        "translation_code" => $field["field_code"],
                                        "form_id" => $translationFormId
                                    ],
                                    $to_create_translation
                                );
                            }else if($languageType == "custom"){
                                Translation::where('language_id', $languageId)
                                ->where('translation_code',$field["field_code"])
                                ->where('form_id',$translationFormId)
                                ->delete();
                            }
                        }
                    }
                }else{
                    $to_create_fields[$formIndex][$fieldIndex] = $field;
                }
                if($result["success"]){
                    $TEMP_FIELDS[$formIndex][$fieldIndex] = $FORMS_DATA[$formIndex]->fields()->updateOrCreate(
                        ["form_id" => $thisFormId,"field_code" => $field["field_code"]],
                        $to_create_fields[$formIndex][$fieldIndex]
                    );
                }
            }

            // Create DB table
            if($result["success"]){
                if($pageQuery["enabled"] && $pageNativeSQL["enabled"]){
                    if(empty($pageNativeSQL["sql"])){
                        $addError($custom_message["page_options.native.no_sql"],$msgTranslations["page_setting"],"page_setting","page_sql",'validation',null,null);
                    }else{
                        try {
                            View::createOrReplaceView($table_name,$pageNativeSQL["sql"]);
                        } catch (\Throwable $th) {
                            if($this->DEBUG_MODE){
                                throw $th;
                            }else{
                                $addError($custom_message["page_options.native.sql_error"],$msgTranslations["page_setting"],"page_setting","page_sql",'validation',null,null);
                            }
                        }
                    }
                }else{
                    $isCreate = !Schema::hasTable($table_name);
                    $shemaCall = $isCreate ? "create" : "table";
                    Schema::{$shemaCall}($table_name, function (Blueprint $table) use ($to_create_fields, $formIndex, $parseFieldType, $table_name, $isCreate){
                        foreach($to_create_fields[$formIndex] as $field){
                            $code = $field["field_code"];
                            // $default = $field["field_default_value"];
                            $options = $field["field_options"];
                            $columnType = $parseFieldType($field["field_type"]);
                            if(array_key_exists("system_field",$options) && $options["system_field"]){
                                if(!Schema::hasColumn($table_name, $code)){
                                    $column = $table->{$options["column_type"]}($code);
                                    foreach($options["column_modifiers"] as $modifier){
                                        if(array_key_exists("value",$modifier)){
                                            $column->{$modifier["name"]}($modifier["value"]);
                                        }else{
                                            $column->{$modifier["name"]}();
                                        }
                                    }
                                }
                            }else if($columnType !== ""){
                                if($columnType == "string"){
                                    // 字串
                                    // $max = isset($field["field_options"]["field_details"]["text"]["max"]) ? $field["field_options"]["field_details"]["text"]["max"] : 2000;
                                    // $column = $table->string($code,((int)$max)*2);
                                    $column = $table->text($code);
                                }else if($columnType == "decimal" && isset($options["decimal"])){
                                    // 小數
                                    $total = $options["decimal"]["total"];
                                    $decimal = $options["decimal"]["decimal"];
                                    $column = $table->decimal($code, $total, $decimal);
                                }else{
                                    $column = $table->{$columnType}($code);
                                }
                                // if($field["field_required"]) $column->required(); else $column->nullable();
                                // if(!is_null($default)) $column->default($default);
                                $column->nullable();
                                if(Schema::hasColumn($table_name, $code)){
                                    $column->change();
                                }
                            }
                        }
                        if($isCreate){
                            $table->index(['id','parent_id']);
                        }
                        // dd($table);
                    });
                    View::dropViewIfExists($table_name);
                }
            }
        }
        // dd($result);
        if($result["success"]){
            // Create permission for login user
            if($type == "insert"){
                if(!UserUtil::isAdmin()){
                    Permission::create(["page_id" => $PAGE_DATA->page_id, "permission_target_id" => SessionUtil::getUserID(), "permission_type" => "user", "permission_read" => 1, "permission_insert" => 1, "permission_update" => 1, "permission_delete" => 1, "permission_allow_rw_all" => 1, 'created_by' => SessionUtil::getUserID(), 'updated_by' => SessionUtil::getUserID(), 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
                }
            }
            Cache::flush();
            DB::commit();
            return response()->json($result,200);
        }else{
            DB::rollBack();
            return response()->json($result,400);
        }
    }

    public function modules_list(){
        if(System::systemAuth($this->modulePageId)){
            $show = System::showList($this->modulePageId);
            $translateFields = [
                "new","module","submodule","selecting","cancel","clear","yes","no","unsave_confirm",
                "save","edit_order","save_success","page_module.min",'page_name','type'
            ];
            $translations = TranslationUtil::getTranslationByCode($translateFields);
            $page_data = TranslationUtil::getPageDataWithTranslation($this->pageId);
            $module_page_data = TranslationUtil::getPageDataWithTranslation($this->modulePageId);

            foreach($translations as $key => $field){
                if($key != 'page_name'){
                    $show["languages"][$key] = $field;
                }
            }
            // dd($show);
            $fields["page_name"] = [
                "translation" => $translations['page_name'],
                "field_type" => "string",
                "field_code" => "page_name"
            ];
            $fields["type"] = [
                "translation" => $translations["type"],
                "field_type" => "string",
                "field_code" => "type"
            ];
            foreach($page_data["forms"][0]["fields"] as $code => $field){
                if($field["field_show_on_list"] == "1"){
                    $fields[$code] = $field;
                }
            }
            // dd($fields);
            $pageData = PageUtil::getModules(true);
            return view($module_page_data["page"]["page_list_template"])
            ->with("pageModule", $pageData)
            ->with("languages",$show["languages"])
            ->with("fields",$fields)
            ->with("PAGE_ID", $this->modulePageId);
        }
    }

    public function modules_form($type, $id = null){
        if(array_search($type, ['view', 'insert', 'update']) === false){
            abort(404);
        }else if(System::systemAuth($this->modulePageId, $type)){
            $page_data = TranslationUtil::getPageDataWithTranslation($this->pageId);
            $module_page_data = TranslationUtil::getPageDataWithTranslation($this->modulePageId);

            $fields = [
                "module_setting","SY_TRANSLATION","module","submodule","selecting",
                "page","clear",'loading','processing',
                'accessing','redirecting','error.unknown','contact_maintenance','save'
            ];
            $translations = TranslationUtil::getTranslationByCode($fields);
            foreach($translations as $key => $field){
                $languages[$key] = $field;
            }

            // dd($languages);
            $language_data = [];
            foreach (TranslationUtil::getAllLanguages() as $lan){
                array_push($language_data,[
                    "language_id" => $lan->language_id,
                    "language_code" => $lan->language_code,
                    "language_name" => $lan->language_name
                ]);
            }
            $modules = PageUtil::getModules(true);
            $data = null;
            if($type !== "insert"){
                $data = $this->module_origin_data($id);
                if(is_null($data)){
                    return redirect()->route('modules_form',['type'=>'insert']);
                    // abort(404);
                }else if($data["module_setting"]["page_readonly"] && $type == "update"){
                    abort(403);
                }/* else if(){ // TODO: 之後還要判斷list_template

                } */
            }
            // dd($page_data,$languages);
            return view($module_page_data["page"]["page_form_template"])
            ->with("translations", $languages)
            ->with("languages", $language_data)
            ->with("page_data", $page_data)
            ->with("module_data", $module_page_data)
            ->with("modules",$modules)
            ->with("type", $type)
            ->with("data", $data)
            ->with("dataId", $id)
            ->with("PAGE_ID", $this->pageId);
        }
    }

    public function module_origin_data($id){
        $allData = TranslationUtil::getPageDataWithTranslation($id);
        if(is_null($allData)){
            return null;
        }else{
            $allLanguage = TranslationUtil::getAllLanguages();
            // dump($allData);
            $pageData = $allData["page"];
            $formData = $allData["forms"];
            $page_setting = [
                "page_code" => $pageData["page_code"],
                "page_module" => $pageData["page_module"],
                "page_visible" => $pageData["page_visible"],
                "page_stay" => $pageData["page_stay"] ?? false,
                "page_readonly" => $pageData["page_readonly"],
                "page_options" => $pageData["page_options"],
                "page_remarks" => $pageData["page_remarks"]
            ];
            $translation_setting = [];

            foreach($allLanguage as $language){
                $langaugeId = $language->language_id;
                $hasPageTranslation = TranslationUtil::translationExists($pageData["page_code"],$langaugeId);
                $pageTranslation = TranslationUtil::getTranslationByCode($pageData["page_code"],$langaugeId);
                if($hasPageTranslation){
                    $translation_setting[$language->language_id] = $pageTranslation;
                }
            }

            return [
                "module_setting" => $page_setting,
                "translation_setting" => (object) $translation_setting
            ];
        }
    }

    public function modules_save(Request $request, $type, $id = null){
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

        $pages = TranslationUtil::getPageDataWithTranslation($this->pageId);

        $validation_message = $pages["validation"];
        $custom_message = TranslationUtil::getTranslationByCode(["page_code.regex","page_module.min","page_options.native.no_sql","page_options.native.sql_error","field_code.regex"]);
        foreach($custom_message as $msgName => $msgValue){
            $validation_message[$msgName] = $msgValue;
        }

        $msgTranslations = TranslationUtil::getTranslationByCode([
            "page_setting","field_setting","SY_TRANSLATION","row_with_number","page_head","page_body","of","field_no_details","field_type_error"
        ]);

        if (ValidationUtil::isJSONString($request->getContent()))
            $data = DataUtil::convertToArray(json_decode($request->getContent()));
        else
            abort(400);

        // dd($data);

        $pageSetting = $data["module_setting"];
        $pageExists = PageUtil::getPageIdByPageCode($pageSetting["page_code"]);
        $pageSetting["page_readonly"] = false;
        $pageSetting["page_list_template"] = null;
        $pageSetting["page_form_template"] = null;
        $modulePageCount = Page::where("page_module", $pageSetting["page_module"])->get()->count();

        $to_create_page = [
            'updated_by' => SessionUtil::getUserID()
        ];
        if(is_null($pageExists)){
            $to_create_page["created_by"] = SessionUtil::getUserID();
            $pageOrder = $modulePageCount;
        }else{
            $pageOrigin = Page::find($pageExists);
            $pageOrder = $pageOrigin->page_order;
            if(!$pageOrigin->page_visible || $pageOrigin->page_module != $pageSetting["page_module"]){
                $pageOrder = $modulePageCount;
            }
        }
        $pageSetting["page_order"] = $pageSetting["page_visible"] ? $pageOrder : 0;

        $pageToValidate = [];
        $page_rules = [];
        $page_attributes = [];
        foreach($pages["forms"][0]["fields"] as $key => $setting){
            if($key !== "page_code")
                $to_create_page[$key] = $pageSetting[$key];

            $pageToValidate[$key] = $pageSetting[$key];
            $pageRuleArray = $setting['field_rule'];
            // dump("{$key}=>",$setting['field_rule']);
            if($type == "update")
                $pageSetting["data"]["page_id"] = $id;
            $page_rules[$key] = ValidationUtil::generateFieldRuleObject($pageRuleArray, $setting, $pageSetting, [
                'dataKey' => "page_id",
                'update' => $type == 'update',
                'required' => $setting['field_required']
            ]);
            $page_attributes[$key] = $setting['translation'];
        }
        foreach($page_rules["page_code"] as $key => &$rule){
            if(strpos($rule,"max") === 0){
                $rule = "max:3";
            }else if(strpos($rule,"regex") === 0){
                $rule = "regex:/^[A-Z]{1,2}[0-9]?$/";
            }
        }
        foreach(['page_list_template','page_form_template'] as $r){
            foreach($page_rules[$r] as $key => &$rule){
                if($rule === "required"){
                    $rule = "nullable";
                }
            }
        }

        // dd($pageToValidate,$page_rules);
        $pageValidation = ValidationUtil::validationData($pageToValidate, $page_rules, $validation_message, $page_attributes);
        if(!$pageValidation["passed"]){
            foreach($pageValidation["validator"]->errors()->toArray() as $errorField => $messages){
                foreach($messages as $msg){
                    $addError($msg, $msgTranslations["page_setting"], "page_setting", $errorField, 'validation', null, null);
                }
            }
        }

        // Create or update page data
        $PAGE_DATA = Page::updateOrCreate(
            ["page_code" => $pageSetting["page_code"]],
            $to_create_page
        );
        // Create or update page translation
        foreach ($data["translation_setting"] as $languageId => $translation){
            $nameRepeated = TranslationUtil::translationExists($PAGE_DATA->page_code,$languageId);
            $to_create_translation = [
                "translation" => $translation,
                'updated_by' => SessionUtil::getUserID(),
            ];
            if(!$nameRepeated){
                $to_create_translation['created_by'] = SessionUtil::getUserID();
            }
            Translation::updateOrCreate(
                [
                    "language_id" => $languageId,
                    "translation_code" => $PAGE_DATA->page_code,
                    "translation_type" => "page"
                ],
                $to_create_translation
            );
        }

        // dd($result);
        if($result["success"]){
            DB::commit();
            return response()->json($result,200);
        }else{
            DB::rollBack();
            return response()->json($result,400);
        }
    }


    public function getPageFields($page_id){
        if(System::systemAuth($this->pageId)){
            $page = TranslationUtil::getPageDataWithTranslation($page_id);
            return response()->json($page,200);
        }
    }
}

?>
