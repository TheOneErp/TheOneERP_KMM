<?php

namespace App\Utils;

use App\Models\Page;
use App\Models\Form;
use App\Models\Field;
use App\Models\Parameter;
use App\Models\Translation;

use App\Utils\DataUtil;
use App\Utils\SessionUtil;
use App\Utils\TranslationUtil;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class PageUtil
{
    public static function getPagePathByPageId(int $pageId)
    {
        $result = null;
        $page = Page::find($pageId);
        if (!is_null($page)) {
            $result = $page->page_code;
            $parent = $page->module()->first();
            if (!is_null($parent)) {
                $result = DataUtil::unionString(PageUtil::getPagePathByPageId($parent->page_id), $result, "\\");
            }
        }

        return $result;
    }

    public static function getTableNameByPageId(int $pageId)
    {
        $page = Page::find($pageId);
        $tableName = [];
        if (!is_null($page)) {
            $pageCode = $page->page_code;
            $pageCodeSplit = explode("_", $pageCode);
            if (array_search($pageCodeSplit[0], ["SY", "DT"]) === false) {
                $page->forms->each(function ($form) use (&$tableName, $pageCode) {
                    $formId = $form->form_id;
                    array_push($tableName, "{$pageCode}_{$formId}");
                });
            }
        }

        return $tableName;
    }

    public static function getTableNameByPageCode(string $pageCode)
    {
        $page = Page::findByCode($pageCode)->get();
        $tableName = [];
        if ($page->isNotEmpty()) {
            $tableName = PageUtil::getTableNameByPageId($page[0]->page_id);
        }

        return $tableName;
    }

    public static function getPageIdByPageCode($pageCode)
    {
        return Page::where("page_code", $pageCode)->pluck('page_id')->first();
    }

    public static function getPageDataByPageCode($pageCode)
    {
        $page = Page::where("page_code", $pageCode)->first();
        if ($page != null) {
            return PageUtil::getPageData($page->page_id);
        } else {
            return null;
        }
    }

    public static function getPageData($pageID)
    {
        $returnArray = [
            "page" => null,
            "path" => null,
            "forms" => []
        ];

        $page = Page::find($pageID);
        if ($page == null) {
            return null;
        }
        $returnArray["page"] = $page->toArray();

        $getPath = function ($currentPage, &$currentPath) use (&$getPath) {
            $module = $currentPage->module()->first();
            if ($module != null) {
                $currentPath = $module->page_code . '\\' . $currentPath;
                return $getPath($module, $currentPath);
            } else {
                return $currentPath;
            }
        };
        $pageCode = $page->page_code;
        $returnArray['path'] = $getPath($page, $pageCode);

        $returnArray["page"]["permission"] = PermissionUtil::getCurrentUserPagePermission($pageID);

        foreach ($page->forms as $form) {
            $formArray = $form->toArray();
            $fields = $form->fields->toArray();
            usort($fields, function ($a, $b) {
                return $a['field_order'] - $b['field_order'];
            });
            $formArray["fields"] = $fields;

            foreach ($fields as $field) {
                if ($field['field_type'] == 'reference_page' && $field['field_options']['reference_page']['page_id'] != $pageID) {
                    $field['pageData'] = TranslationUtil::getPageDataWithTranslation($field['field_options']['reference_page']['page_id']);
                    $formArray["fields"][$field['field_code']] = $field;
                }
            }

            $returnArray["forms"][] = $formArray;
        }

        return ($returnArray);
    }

    public static function getModules(bool $allModule = false, $userId = null)
    {
        $isRoot = (is_null($userId) && UserUtil::isRoot()) || ($userId === 0 ? UserUtil::isRoot() : false);
        $isAdmin = (is_null($userId) && UserUtil::isAdmin()) || $userId === 1 ? UserUtil::isAdmin() : false;
        $userId = is_null($userId) ? SessionUtil::getUserID() : $userId;
        $permissions = PermissionUtil::getUserPermission($userId);
        // dd($permissions);
        $languages = Translation::all();
        $defaultLanguage = Parameter::where("parameter_code", "default_language")->get()->pluck("parameter_value")->first();

        $result = [
            "module" => [],
            "submodule" => [],
            "page" => []
        ];

        $addPage = function ($page) use (&$result, $allModule, $permissions, $languages, $defaultLanguage, &$addPage, $isRoot, $isAdmin) {
            $code = $page["page_code"];
            $visible = $page["page_visible"];
            $listTemplate = $page["page_list_template"];
            $formTemplate = $page["page_form_template"];
            $isModule = is_null($listTemplate) && is_null($formTemplate) && $visible;
            if ($code == "DT" && !$isRoot) {
                return false;
            }
            $pass = false;
            // dd($page);
            if (!is_null($page)) {
                if (!is_null($page["children"])) {
                    foreach ($page["children"] as $child) {
                        $childPass = $addPage($child);
                        if ($childPass === true || $allModule) {
                            $pass = true;
                        }
                    }
                } else if ($isModule && $allModule && $code != 'SY') {
                    $pass = true;
                } else {
                    if ($isAdmin) {
                        $pass = true;
                    } else {
                        $pagePermissionIndex = array_search($page["page_id"], array_column($permissions, "page_id"));
                        if ($pagePermissionIndex !== false) {
                            $pagePermission = $permissions[$pagePermissionIndex];
                            foreach (["read", "insert", "update", "delete"] as $permissionType) {
                                if ($pagePermission["permission_{$permissionType}"] === true) {
                                    $pass = true;
                                }
                            }
                        }
                    }
                }
            }

            if ($pass) {
                $defaultPageName = $languages->where('language_id', $defaultLanguage)->where('translation_code', $page["page_code"])->pluck('translation')->first();
                $currentPageName = $languages->where('language_id', session('language_id'))->where('translation_code', $page["page_code"])->pluck('translation')->first();
                $pageName = is_null($currentPageName) ? $defaultPageName : $currentPageName;
                $temp = [
                    "page_id" => $page["page_id"],
                    "page_code" => $code,
                    "page_name" => is_null($pageName) ? $code : $pageName,
                    "page_readonly" => $page["page_readonly"],
                    "page_visible" => $page["page_visible"],
                    "page_stay" => $page["page_stay"] ?? false,
                    "page_order" => $page["page_order"],
                    "page_module" => $page["page_module"],
                    "page_remarks" => $page["page_remarks"],
                ];
                $arrKey = $page["page_id"];
                if ($page["page_module"] == 0) {
                    $result["module"][$arrKey] = $temp;
                } else if ($isModule) {
                    $result["submodule"][$arrKey] = $temp;
                } else {
                    $result["page"][$arrKey] = $temp;
                }
            }

            return $pass;
        };
        // dd(PageUtil::getAllPageWithChildrenByPageId());
        foreach (PageUtil::getAllPageDataWithChildren() as $pageWithChildren) {
            $pass = $addPage($pageWithChildren);
        }
        // dump($result);
        /* foreach ($result as &$pages){
            uksort($pages, function ($a,$b){
                if($a["page_module"] == $b["page_module"]){
                    if($a["page_visible"] == $b["page_visible"]){
                        return $a["page_order"] - $b["page_order"];
                    }else if($a["page_visible"]){
                        return -1;
                    }else{
                        return 1;
                    }
                }else{
                    return $a["page_module"] - $b["page_module"];
                }
            });
        } */
        // dd($result);
        return $result;
    }

    /**
     * 取回PageData 及其Children之PageData
     * @param string|int $pageID 欲取回PageData之page_id
     *
     * @return array|null 若此ID查無資料則回傳null
     */
    public static function getPageDataWithChildrenByPageId($pageID)
    {
        $allPage = collect([]);
        foreach (Page::all() as $page) {
            $tempPageData = [
                "page_id" => $page->page_id,
                "page_order" => $page->page_order,
                "page_module" => $page->page_module,
                "page" => $page->toArray(),
                "forms" => []
            ];
            foreach ($page->forms as $formIndex => $form) {
                $fields = [];
                foreach ($form->fields as $field) {
                    $code = $field->field_code;
                    $fields[$code] = $field->toArray();
                }
                $tempPageData["forms"][$formIndex] = $form->toArray();
                $tempPageData["forms"][$formIndex]["fields"] = $fields;
            }
            $allPage->push($tempPageData);
        }

        $getChild = function ($pageId) use ($allPage, &$getChild) {
            $pageData = $allPage->where('page_id', $pageId)->first();
            $pageData["children"] = null;
            if (!is_null($pageData)) {
                unset($pageData["page_id"]);
                unset($pageData["page_order"]);
                unset($pageData["page_module"]);
                // $pageData["children"] = null;
                $children = $allPage->where('page_module', $pageId)->sortBy('page_order');
                if ($children->isNotEmpty()) {
                    $pageData["children"] = [];
                    foreach ($children as $child) {
                        $pageData["children"][] = $getChild($child["page_id"]);
                    }
                }
            }
            return $pageData;
        };

        return $getChild($pageID);
    }

    public static function getAllPageData($returnArray = true)
    {
        $allPages = collect([]);
        $allForms = collect([]);
        $allFields = collect([]);
        foreach (Field::all() as $field) {
            $tempFieldData = [];
            foreach ($field->toArray() as $key => $value) {
                $tempFieldData[$key] = $value;
            }
            $allFields->push($tempFieldData);
        }

        foreach (Form::all() as $form) {
            $tempFormData = [];
            foreach ($form->toArray() as $key => $value) {
                $tempFormData[$key] = $value;
            }
            $tempFormData["fields"] = [];
            foreach ($allFields->where("form_id", $form->form_id) as $field) {
                $tempFormData["fields"][$field["field_code"]] = $field;
            }
            $allForms->push($tempFormData);
        }

        foreach (Page::all() as $page) {
            $tempPageData = [];
            foreach ($page->toArray() as $key => $value) {
                $tempPageData[$key] = $value;
            }
            $tempPageData["forms"] = [];
            $formIndex = 0;
            foreach ($allForms->where("page_id", $page->page_id) as $form) {
                $tempPageData["forms"][$formIndex++] = $form;
            }
            $allPages->push($tempPageData);
        }

        $allPages = $returnArray ? $allPages->toArray() : $allPages;
        // dd($allPages);
        return $allPages;
    }

    public static function getAllPageDataWithChildren()
    {
        $result = [];
        $allPages = PageUtil::getAllPageData(false);
        $getChild = function ($pageId) use ($allPages, &$getChild) {
            $pageData = $allPages->where('page_id', $pageId)->first();
            $pageData["children"] = null;
            if (!is_null($pageData)) {
                $children = $allPages->where('page_module', $pageId)->sortBy('page_order');
                if ($children->isNotEmpty()) {
                    $pageData["children"] = [];
                    foreach ($children as $child) {
                        $pageData["children"][] = $getChild($child["page_id"]);
                    }
                }
            }
            return $pageData;
        };

        $firstLevel =
            $allPages->where("page_module", 0)
            ->sortBy("page_order")
            ->sortByDesc("page_visible");
        foreach ($firstLevel as $module) {
            $result[] = $getChild($module["page_id"]);
        }
        // dd($result);
        return $result;
    }

    public static function getPageDataWithParentsByPageId($pageID, $result = [])
    {
        $page_data = PageUtil::getPageData($pageID);
        if (is_null($page_data)) {
            return null;
        }
        $page_module = $page_data["page"]["page_module"];
        if ($page_module !== 0) {
            $result = PageUtil::getPageDataWithParentsByPageId($page_module, $result);
        }
        array_push($result, TranslationUtil::getPageDataWithTranslation($pageID));
        return $result;
    }

    public static function getPageOptions($pageData)
    {
        $bodyForms = [];
        $headForm = array_filter($pageData['forms'], function ($form) use (&$bodyForms) {
            if ($form['form_type'] == "head") return true;
            $bodyForms[$form['form_id']] = $form;
            return false;
        });

        if (count($headForm) < 1) abort(404);
        else $headForm = $headForm[0];

        $dataKey = "id";
        $listView = "templates.list.universal";
        $formView = "templates.form.universal";

        // Fetch page options
        if (!empty($pageData['page']['page_options'])) {
            if (!empty($pageData['page']['page_options']['table'])) $table = $pageData['page']['page_options']['table'];
            if (!empty($pageData['page']['page_options']['primaryKey'])) $dataKey = $pageData['page']['page_options']['primaryKey'];
        }

        // If template specified . check it and change to it.
        if ($pageData['page']['page_list_template'] != null && view()->exists('templates.list.' . $pageData['page']['page_list_template'])) {
            $listView = 'templates.list.' . $pageData['page']['page_list_template'];
        } else if ($pageData['page']['page_list_template'] != null && view()->exists($pageData['page']['page_list_template'])) {
            $listView = $pageData['page']['page_list_template'];
        }
        if ($pageData['page']['page_form_template'] != null && view()->exists('templates.form.' . $pageData['page']['page_form_template'])) {
            $formView = 'templates.form.' . $pageData['page']['page_form_template'];
        } else if ($pageData['page']['page_form_template'] != null && view()->exists($pageData['page']['page_form_template'])) {
            $formView = $pageData['page']['page_form_template'];
        }

        $headForm['table'] = $pageData['page']['page_code'] . "_" . $headForm['form_id'];
        $headForm['dataKey'] = $dataKey;
        if (isset($pageData['page']['page_options']['table'])) $headForm['table'] = $pageData['page']['page_options']['table'];
        foreach ($bodyForms as &$form) {
            $form['table'] = $pageData['page']['page_code'] . "_" . $form['form_id'];
        }

        return [
            'headForm' => $headForm,
            'bodyForms' => $bodyForms,
            'headTable' => $headForm['table'],
            'dataKey' => $dataKey,
            'listView' => $listView,
            'formView' => $formView,
        ];
    }

    public static function getData($pageData, $headID, $options = [])
    {
        $pageOptions = PageUtil::getPageOptions($pageData);
        $headData = DB::table($pageOptions['headTable'])->where($pageOptions['dataKey'], $headID)->first();
        if ($headData == null)
            if (in_array('noAbort', $options))
                return null;
            else
                abort(404);

        $headData = DataUtil::convertToArray($headData);

        $dataProcessor = function (&$data, $schema) {
            if (is_null($data['data_options'])) {
                $data['data_options'] = [];
            }
            if (is_string($data['data_options'])) {
                $data['data_options'] = DataUtil::convertToArray(json_decode($data['data_options']));
            }
            foreach ($schema['fields'] as $field) {
                if ($field['field_type'] == "checkboxes") {
                    $data[$field['field_code']] = $data[$field['field_code']] != null ? explode(",", $data[$field['field_code']]) : [];
                }
                if ($field['field_type'] == "reference_page") {
                    $referencePageData = TranslationUtil::getPageDataWithTranslation($field['field_options']['reference_page']['page_id']);
                    if ($data[$field['field_code']] != null)
                        $data[$field['field_code']] = PageUtil::getData($referencePageData, $data[$field['field_code']], ['noAbort']);
                }
            }
        };

        $getData = function ($id, $schema, &$data) use ($pageOptions, &$getData, $dataProcessor) {
            $tmpData = [
                'form_id' => $schema['form_id'],
                'data' => $data,
                'subData' => []
            ];

            $subForms = array_filter($pageOptions['bodyForms'], function ($subForm) use ($schema) {
                return $subForm['form_parent'] == $schema['form_id'];
            });
            foreach ($subForms as $subForm) {
                $tmpData['subData'][$subForm['form_id']] = [];
                $subDatas = DataUtil::convertToArray(DB::table($subForm['table'])->where('parent_id', $id)->get()->toArray());
                foreach ($subDatas as $subData) {
                    $dataProcessor($subData, $subForm);
                    $tmpData['subData'][$subForm['form_id']][] = $getData($subData['id'], $subForm, $subData);
                }
            }
            return $tmpData;
        };

        $dataProcessor($headData, $pageOptions['headForm']);
        $returnData = $getData($headData[$pageOptions['dataKey']], $pageOptions['headForm'], $headData);

        return $returnData;
    }

    public static function deleteData($pageData, $headID, $options = [])
    { // TODO: when noAbort skip deleteFiles and add to root deleteFiles array
        $deleteFiles = [];

        $pageOptions = PageUtil::getPageOptions($pageData);
        $headData = DB::table($pageOptions['headTable'])->where($pageOptions['dataKey'], $headID)->first();
        //dd($headData);
        if ($headData == null)
            if (in_array('noAbort', $options))
                return null;
            else
                abort(404);
        $headData = DataUtil::convertToArray($headData);

        $deleteData = function ($id, $schema, &$data, $dataKey = null, $parentID = null) use ($pageOptions, &$deleteData, $pageData, &$deleteFiles) {
            $tmpData = [
                'form_id' => $schema['form_id'],
                'data' => $data,
                'subData' => []
            ];
            $subForms = array_filter($pageOptions['bodyForms'], function ($subForm) use ($schema) {
                return $subForm['form_parent'] == $schema['form_id'];
            });

            foreach ($schema['fields'] as $field) {
                if ($field['field_type'] == 'reference_page') {
                    $referencePageData = TranslationUtil::getPageDataWithTranslation($field['field_options']['reference_page']['page_id']);
                    if ($data[$field['field_code']] != null)
                        $data[$field['field_code']] = PageUtil::deleteData($referencePageData, $data[$field['field_code']], ['noAbort']);
                }
                if ($field['field_type'] == 'file' && !empty($data[$field['field_code']])) {
                    $deleteFiles[] = [
                        'field_id' => $field['field_id'],
                        'id' => $data['id'],
                        'filename' => $data[$field['field_code']]
                    ];
                }
            }

            foreach ($subForms as $subForm) {
                $tmpData['subData'][$subForm['form_id']] = [];
                $subDatas = DataUtil::convertToArray(DB::table($subForm['table'])->where('parent_id', $id)->get()->toArray());
                foreach ($subDatas as $subData) {
                    $deleteData($subData['id'], $subForm, $subData, null, $id);
                }
            }
            if ($dataKey != null)
                DB::table($schema['table'])->where($dataKey, $id)->delete();
            else
                DB::table($schema['table'])->where("id", $id)->delete();

            LogUtil::addFormLog($pageData['page']['page_id'], $schema['form_id'], $id, $parentID, 'delete', null, $data);
        };

        $deleteData($headData[$pageOptions['dataKey']], $pageOptions['headForm'], $headData, $pageOptions['dataKey']);
        DB::table($pageOptions['headTable'])->where($pageOptions['dataKey'], $headID)->delete();

        foreach ($deleteFiles as $file) {
            FileUtil::deleteFile($file['field_id'], $file['id'], $file['filename']);
        }

        return true;
    }

    public static function generateInsertData($schema, $data, $parentID = null)
    {
        if (!is_array($data)) $data = (array) $data;
        $tmp = [
            'created_by' => SessionUtil::getUserID(),
            'updated_by' => SessionUtil::getUserID(),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];
        foreach ($schema['fields'] as $field) {
            if ($field['field_type'] == "button")
                continue;
            else if ($field['field_type'] == "checkboxes")
                $tmp[$field['field_code']] = implode(",", $data[$field['field_code']]);
            else if ( !(isset($field['field_options']['system_field']) && $field['field_options']['system_field'] ) && isset($data[$field['field_code']]))
                $tmp[$field['field_code']] = $data[$field['field_code']];
        }
        if ($parentID != null) $tmp['parent_id'] = $parentID;
        return $tmp;
    }

    public static function generateUpdateData($schema, $data, $orignalData = null)
    {
        $tmp = [
            'updated_by' => SessionUtil::getUserID(),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];
        foreach ($schema['fields'] as $field) {
            if ($field['field_type'] == "button")
                continue;
            else if ($field['field_type'] == "checkboxes")
                $tmp[$field['field_code']] = implode(",", $data[$field['field_code']]);
            else if (!(isset($field['field_options']['system_field']) && $field['field_options']['system_field'] ) && isset($data[$field['field_code']]))
                $tmp[$field['field_code']] = $data[$field['field_code']];
        }
        return $tmp;
    }

    public static function setVerify(int $page_id, string $headTable, string $dataKey, $data)
    {
        $needVerify = VerifyUtil::pageVerifyConfirmation($page_id);
        $originalData = (array) DB::table($headTable)->where($dataKey, $data['data'][$dataKey])->first();

        if (empty($originalData['data_options'])) {
            $dataOptions = [];
        } else {
            $dataOptions = DataUtil::convertToArray(json_decode($originalData['data_options']));
        }

        if (!isset($dataOptions['verify'])) {
            if ($needVerify) {
                $dataOptions['verify'] = ['level' => 0];
            } else {
                $dataOptions['verify'] = [
                    'level' => 255,
                    "population" => [
                        255 => [
                            "-1" =>[
                                [
                                    "verify_at" => Carbon::now()->format('Y-m-d H:i:s'),
                                    "user_id" => SessionUtil::getUserID(),
                                    "name" => SessionUtil::getUsername()
                                ]
                            ]
                        ]
                    ],
                ];
            }
        }

        DB::table($headTable)->where($dataKey, $data['data'][$dataKey])->lockForUpdate()->update(['data_options' => json_encode($dataOptions)]);
    }
}

class FieldUtil
{
    public static function getFieldByFormIdAndCode($formId, $code)
    {
        $fields = Form::find($formId)->fields();

        return $fields->where("field_code", $code)->first();
    }
}
