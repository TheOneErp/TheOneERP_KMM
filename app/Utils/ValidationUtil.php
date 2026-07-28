<?php

namespace App\Utils;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\CheckboxesIn;
use App\Rules\FileCheck;
use App\Rules\In;
use App\Rules\NotIn;


class ValidationUtil
{
    public static function validationData($data, $rules, $messages, $attributes = [])
    {
        $passed = true;
        $errors = [];
        $validator = Validator::make($data, $rules, $messages, $attributes);
        if ($validator->fails()) {
            $passed = false;
            $errors = $validator->messages()->all();
        }

        return [
            'passed' => $passed,
            'errors' => $errors,
            'validator' => $validator
        ];
    }
    public static function validationArray($array, $rules, $messages, $attributes = [])
    {
        $passed = true;
        $errors = [];
        foreach ($array as $data) {
            $result = ValidationUtil::validationData($data, $rules, $messages, $attributes);
            if ($result['passed']) $passed = false;
            $errors = array_merge($errors, $result['errors']);
        }
        return [
            'passed' => $passed,
            'errors' => $errors
        ];
    }
    public static function validationFile($file, $field, $rule)
    {
    }

    public static function generateFieldRuleObject($ruleArray, $field, $dataset, $options)
    {
        if ($ruleArray == null) return [];

        $ruleObject = [];

        $dataKey = isset($options['dataKey']) ? $options['dataKey'] : 'id';

        foreach ($ruleArray as $rule) {
            if (is_array($rule)) {
                if (isset($rule['in'])) {
                    $ruleObject[] = new In($rule['in']);
                }
                if (isset($rule['not_in'])) {
                    $ruleObject[] = new NotIn($rule['not_in']);
                }
            } else {
                if (strpos($rule, 'unique') !== false && isset($options['update']) && $options['update']) {
                    if (count(explode(",", $rule)) == 1) {
                        $ruleObject[] = $rule . "," . $field['field_code'] . "," . $dataset['data'][$dataKey] . "," . $dataKey;
                    } else {
                        $ruleObject[] = $rule . $dataset['data'][$dataKey] . "," . $dataKey;
                    }
                } else if ($rule == 'checkboxes_in') {
                    $ruleObject[] = new CheckboxesIn($field['field_options']['options']);
                } else {
                    $ruleObject[] = $rule;
                }
            }
        }

        if ($field['field_required']) {
            if ($field['field_type'] == "checkboxes")
                $ruleObject[] = 'checkboxes_required';
            else
                $ruleObject[] = 'required';
        }

        if ($field['field_type'] == 'file') {
            $ruleObject[] = new FileCheck($dataset,$field);
        }

        return $ruleObject;
    }

    public static function unsetFieldRules($toUnset, &$ruleObject){
        $unsetRuleInRuleObject = function($toUnset) use (&$ruleObject){
            foreach ($ruleObject as $index => $rule){
                if(is_string($rule) && strpos($toUnset,$rule) === 0){
                    unset($ruleObject[$index]);
                }
            }
        };

        if(is_array($toUnset)){
            foreach($toUnset as $rule){
                $unsetRuleInRuleObject($rule);
            }
        }else{
            $unsetRuleInRuleObject($toUnset);
        }
    }

    public static function isJSONString($str)
    {
        json_decode($str);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
