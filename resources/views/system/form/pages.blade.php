@extends('layouts.default')
@section('title', $page_data["page"]["translation"])
@section('content')

<!-- Notice Messages -->
<div id="loading-dimmer" class="ts active dimmer">
    <div class="ts loader"></div>
</div>

{{-- <div id="translation-modal" class="ts modals dimmer">
    <dialog
        id="translationModal"
        class="ts modal"
    >
        <div class="header">
            <h3 class="ts header">@{{translations.translation}}</h3>
        </div>
        <div class="content">
        </div>
        <div class="actions">
            <div class="ts fluid separated stackable buttons">
                <button class="ts positive button" id="translation_confirm">
                    @{{translations.confirm}}
                </button>
                <button class="ts deny button" id="translation_cancel">
                    @{{translations.cancel}}
                </button>
            </div>
        </div>
    </dialog>
</div> --}}

<div id="translation-modal" class="ts modals dimmer">
    <dialog
        id="translationModal"
        class="ts modal"
    >
        <div class="header">
            <h3 class="ts header">@{{translations.translation}}</h3>
        </div>
        <div class="content">
            <table v-if="onShow" class="ts borderless single line table">
                <thead>
                    <tr>
                        <th></th>
                        <th style="text-align: center;">@{{translations.default}}</th>
                        <th style="text-align: center;">@{{translations.custom}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="language in languages">
                        <td>@{{language.language_name}}</td>
                        <td>
                            <div
                                class="ts underlined input"
                                {{-- :class="{disabled: isDisabled('default',language.language_id)}" --}}
                            >
                                <input
                                    type="text"
                                    v-bind="{
                                        id: `default${language.language_id}`,
                                        disabled: parent.config.mode=='view'
                                    }"
                                    v-model="defaultLangauge[language.language_id]"
                                    @change="dataIsChanged"
                                >
                            </div>
                        </td>
                        <td>
                            <div
                                class="ts underlined input"
                                {{-- :class="{disabled: isDisabled('custom',language.language_id)}" --}}
                            >
                                {{-- TODO: 需要先填Default才能填Custom --}}
                                <input
                                    type="text"
                                    v-bind="{
                                        id: `custom${language.language_id}`,
                                        disabled: parent.config.mode=='view'
                                    }"
                                    v-model="customLangauge[language.language_id]"
                                    @change="dataIsChanged"
                                >
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="actions">
            <div class="ts fluid separated stackable buttons">
                <button class="ts positive button" id="translation_confirm">
                    @{{translations.confirm}}
                </button>
                <button class="ts deny button" id="translation_cancel">
                    @{{translations.cancel}}
                </button>
            </div>
        </div>
    </dialog>
</div>

<div id="detail-modal" class="ts modals dimmer">
    <dialog
        id="detailModal"
        class="ts modal"
        :style="modalStyle"
    >
        <div class="header">
            <h3 class="ts header">@{{translations.field_details}}</h3>
        </div>
        <div class="content" style="min-height: 35rem;">
            <form v-if="onShow" class="ts form">
                <h5 class="ts header">@{{translations.common_setting}}</h5>
                {{-- 複製、編輯 --}}
                    <div class="field">
                        <div class="ts center aligned compact horizontal checkboxes">
                            <div v-if="!cantEdit()" class="ts toggle checkbox" :class="{disabled: parent.config.mode =='view'}">
                                <input
                                    id="editable"
                                    type="checkbox"
                                    v-model="values.field_options.editable"
                                    v-data-change
                                >
                                <label for="editable">@{{translations.editable}}</label>
                            </div>
                            <div class="ts toggle checkbox" :class="{disabled: parent.config.mode =='view'}">
                                <input
                                    id="cloneable"
                                    type="checkbox"
                                    v-model="values.field_options.cloneable"
                                    v-data-change
                                >
                                <label for="cloneable">@{{translations.cloneable}}</label>
                            </div>
                        </div>
                    </div>
                {{-- 唯一值 --}}
                    <div class="field">
                        <div class="ts center aligned compact horizontal checkboxes">
                            <div class="ts toggle checkbox" :class="{disabled: parent.config.mode =='view'}">
                                <input
                                    id="rule_unique"
                                    type="checkbox"
                                    v-model="values.field_details.unique"
                                    v-data-change
                                >
                                <label for="rule_unique">@{{translations.rule_unique}}</label>
                            </div>
                            <div class="ts toggle checkbox" v-if="!isHead" :class="{disabled: parent.config.mode =='view'}">
                                <input
                                    id="rule_distinct"
                                    type="checkbox"
                                    v-model="values.field_details.distinct"
                                    v-data-change
                                >
                                <label for="rule_distinct">@{{translations.rule_distinct}}</label>
                            </div>
                        </div>
                    </div>
                {{-- 欄位寬度 --}}
                    <div class="ts container centered grid" v-if="hasWide">
                        <div class="forteen wide column">
                            <fieldset class="tertiary">
                                <legend>@{{translations.wide_label}}</legend>
                                <div class="ts center aligned compact horizontal stackable checkboxes">
                                    <div
                                        class="ts radio checkbox"
                                        v-for="(wide,index) in wides"
                                        :class="{disabled: parent.config.mode =='view'}"
                                    >
                                        <input
                                            :id="'wide'+(index+1)"
                                            type="radio"
                                            name="wide"
                                            :value="wide"
                                            v-model="values.field_options.wide"
                                            v-data-change
                                        >
                                        <label :for="'wide'+(index+1)">@{{(index+1)}} @{{translations.field_wide}}</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                <div class="ts divider" v-if="hasSpecial"></div>
                <h5 class="ts header" v-if="hasSpecial">@{{translations.field_options}}</h5>
                <div class="ts container grid" v-if="hasSpecial">
                    <div :class="field_type.indexOf('reference')!==-1?specialWide['reference-side']:specialWide['normal-side']"></div>
                    <div class="stretched column">
                        {{-- 字串設定(string、textarea) --}}
                            <div v-if="parseFieldType === 'text'">
                                {{-- word limit --}}
                                <fieldset class="tertiary">
                                    <legend>@{{translations.rule_word_limit}}</legend>
                                    <div class="fields">
                                        <div class="field">
                                            <label>@{{translations.rule_max}}</label>
                                            <input
                                                class="max"
                                                id="text-max"
                                                type="number"
                                                max="2000"
                                                min="1"
                                                v-bind="{disabled: parent.config.mode=='view'}"
                                                v-model.number="values.field_details.text.max"
                                                v-number-limit-validate
                                                v-max-min-validate
                                                v-data-change
                                            >
                                        </div>
                                        <div class="field">
                                            <label>@{{translations.rule_min}}</label>
                                            <input
                                                class="min"
                                                id="text-min"
                                                type="number"
                                                max="2000"
                                                min="1"
                                                v-bind="{disabled: parent.config.mode=='view'}"
                                                v-model.number="values.field_details.text.min"
                                                v-number-limit-validate
                                                v-max-min-validate
                                                v-data-change
                                            >
                                        </div>
                                    </div>
                                </fieldset>
                                {{-- content --}}
                                <fieldset class="tertiary">
                                    <legend>@{{translations.rule_string_content}}</legend>
                                    <div class="fields">
                                        <div class="ts checkboxes">
                                            {{-- 一般radio --}}
                                            <div v-for="(ruleValue,rule) in rules">
                                                <div
                                                    class="ts radio checkbox"
                                                    :class="{disabled: parent.config.mode=='view'}"
                                                    style="margin-bottom: .4em;"
                                                >
                                                    <input
                                                        :id="rule"
                                                        type="radio"
                                                        :value="ruleValue"
                                                        name="rule_string_content"
                                                        v-model="values.field_details.text.content"
                                                        @change="stringRuleChange"
                                                        v-data-change
                                                    >
                                                    <label :for="rule">@{{translations[rule]}}</label>
                                                </div>
                                                {{-- 英數組合 --}}
                                                <div
                                                    class="ts checkboxes"
                                                    style="margin-left: 3.2rem; margin-top: 0.8rem; margin-bottom: 0.8rem;"
                                                    v-if="rule == 'rule_letter_numeric'"
                                                >
                                                    <div
                                                        class="ts checkbox letter_numeric_items"
                                                        :class="{disabled: letter_numeric_disable || parent.config.mode=='view'}"
                                                        v-for="(itemValue,item) in letter_numeric_items"
                                                    >
                                                        <input
                                                            :id="item"
                                                            type="checkbox"
                                                            name="letter_numeric"
                                                            :value="itemValue"
                                                            v-model="values.field_details.text.letter_numeric_selected"
                                                            v-data-change
                                                        >
                                                        <label v-if="item != 'underline_hyphen'" :for="item">@{{translations[item]}}</label>
                                                        <label v-else :for="item">@{{translations.underline}}( _ ) @{{translations.and}} @{{translations.hyphen}}( - )</label>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- In --}}
                                            <div class="ts labeled input" style="margin-bottom: .4em;">
                                                <div
                                                    class="ts label"
                                                    style="padding-left: 0px; background: none; border-top: none; border-bottom: none; border-left: none;"
                                                >
                                                    <div class="ts radio checkbox" :class="{disabled: parent.config.mode=='view'}">
                                                        <input
                                                            id="rule_in"
                                                            type="radio"
                                                            name="rule_string_content"
                                                            value="rule_in"
                                                            v-model="values.field_details.text.content"
                                                            @change="stringRuleChange"
                                                            v-data-change
                                                        >
                                                        <label for="rule_in">@{{translations.rule_in}}</label>
                                                    </div>
                                                </div>
                                                <input
                                                    type="text"
                                                    class="ts fluid input"
                                                    :class="{disabled: in_disabled || parent.config.mode=='view'}"
                                                    v-model="values.field_details.text.in"
                                                    v-data-change
                                                >
                                            </div>
                                            {{-- Not In --}}
                                            <div class="ts labeled input" style="margin-bottom: .4em;">
                                                <div
                                                    class="ts label"
                                                    style="padding-left: 0px; background: none; border-top: none; border-bottom: none; border-left: none;"
                                                >
                                                    <div class="ts radio checkbox" :class="{disabled: parent.config.mode=='view'}">
                                                        <input
                                                            id="rule_not_in"
                                                            type="radio"
                                                            name="rule_string_content"
                                                            value="rule_not_in"
                                                            v-model="values.field_details.text.content"
                                                            @change="stringRuleChange"
                                                            v-data-change
                                                        >
                                                        <label for="rule_not_in">@{{translations.rule_not_in}}</label>
                                                    </div>
                                                </div>
                                                <input
                                                    type="text"
                                                    class="ts fluid input"
                                                    :class="{disabled: not_in_disabled || parent.config.mode=='view'}"
                                                    v-model="values.field_details.text.not_in"
                                                    v-data-change
                                                >
                                            </div>
                                            {{-- Other --}}
                                            <div class="ts labeled input">
                                                <div
                                                    class="ts label"
                                                    style="padding-left: 0px; background: none; border-top: none; border-bottom: none; border-left: none;"
                                                >
                                                    <div class="ts radio checkbox" :class="{disabled: parent.config.mode=='view'}">
                                                        <input
                                                            id="other"
                                                            type="radio"
                                                            name="rule_string_content"
                                                            value="other"
                                                            v-model="values.field_details.text.content"
                                                            @change="stringRuleChange"
                                                            v-data-change
                                                        >
                                                        <label for="other">@{{translations.other}}</label>
                                                    </div>
                                                </div>
                                                <div class="ts basic label" style="background: none; border-right: none; border-top-right-radius: 0em; border-bottom-right-radius: 0em;">
                                                    regex:/
                                                </div>
                                                <input
                                                    type="text"
                                                    class="ts fluid input"
                                                    :class="{disabled: other_disabled || parent.config.mode=='view'}"
                                                    style="border-left: none; border-right: none; border-radius: 0em;"
                                                    v-model="values.field_details.text.other"
                                                    v-data-change
                                                >
                                                <div class="ts basic label" style="background: none; border-left: none; border-top-left-radius: 0em; border-bottom-left-radius: 0em;">
                                                    /
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        {{-- 數字設定(integer、decimal) --}}
                            <div v-else-if="parseFieldType === 'number'">
                                {{-- number limit --}}
                                <fieldset class="tertiary">
                                    <legend>@{{translations.rule_number_limit}}</legend>
                                    <div class="fields">
                                        <div class="field">
                                            <label>@{{translations.rule_max}}</label>
                                            <input
                                                class="max"
                                                id="number-max"
                                                type="number"
                                                max="2147483647"
                                                min="-2147483648"
                                                v-bind="{disabled: parent.config.mode=='view'}"
                                                v-model.number="values.field_details.number.max"
                                                v-number-limit-validate
                                                v-max-min-validate
                                                v-data-change
                                            >
                                        </div>
                                        <div class="field">
                                            <label>@{{translations.rule_min}}</label>
                                            <input
                                                class="min"
                                                id="number-min"
                                                type="number"
                                                max="2147483647"
                                                min="-2147483648"
                                                v-bind="{disabled: parent.config.mode=='view'}"
                                                v-model.number="values.field_details.number.min"
                                                v-number-limit-validate
                                                v-max-min-validate
                                                v-data-change
                                            >
                                        </div>
                                    </div>
                                </fieldset>
                                {{-- decimal digits --}}
                                <fieldset class="tertiary" v-if="field_type === 'decimal'">
                                    <legend>@{{translations.decimal_options}}</legend>
                                    <div class="fields">
                                        <div class="field">
                                            <label>@{{translations.integer_digits}}</label>
                                            <input
                                                id="number-integer_digits"
                                                type="number"
                                                max="10"
                                                min="1"
                                                v-bind="{disabled: parent.config.mode=='view'}"
                                                v-model.number="values.field_details.number.integer_digits"
                                                v-number-limit-validate
                                                v-data-change
                                            >
                                        </div>
                                        <div class="field">
                                            <label>@{{translations.decimal_digits}}</label>
                                            <input
                                                id="number-decimal_digits"
                                                type="number"
                                                max="28"
                                                min="0"
                                                v-bind="{disabled: parent.config.mode=='view'}"
                                                v-model.number="values.field_details.number.decimal_digits"
                                                v-number-limit-validate
                                                v-data-change
                                            >
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        {{-- 選項設定(select、checkboxes、radio) --}}
                            <div v-else-if="parseFieldType === 'options'">
                                <fieldset class="tertiary">
                                    <legend>@{{translations.options_options}}</legend>
                                    <div class="inline field" v-for="(item,index) in values.field_options.options">
                                        <label>Item @{{index+1}}</label>
                                        <input
                                            v-bind="{
                                                key: 'item'+(index+1),
                                                disabled: parent.config.mode=='view',
                                                id: 'item'+(index+1)
                                            }"
                                            v-model="values.field_options.options[index]"
                                            v-data-change
                                        >
                                        <i
                                            v-if="parent.config.mode!='view'"
                                            class="remove large icon tr-remover"
                                            :id="'remove-item-'+index"
                                            @click="removeItem"
                                        ></i>
                                    </div>
                                    <button
                                        v-if="parent.config.mode!='view'"
                                        class="ts fluid mini very compact info basic button"
                                        style="margin-top:1.2rem; border-style: dashed; border-width: medium"
                                        @click.prevent="addItem"
                                    >
                                        @{{translations.new}} @{{translations.item}}
                                    </button>
                                </fieldset>
                            </div>
                        {{-- 時間設定(date、time、datetime) --}}
                            <div v-else-if="parseFieldType === 'datetime'">
                            </div>
                        {{-- 檔案設定(file) --}}
                            <div v-else-if="field_type === 'file'">
                                <fieldset class="tertiary">
                                    <legend>@{{translations.file_type}}</legend>
                                    <div class="ts checkboxes">
                                        {{-- 一般檔案類型選擇 --}}
                                        <div
                                            class="ts checkbox"
                                            :class="{disabled: parent.config.mode=='view'}"
                                            v-for="(item, key) in file_types"
                                            v-if="key != 'other'"
                                        >
                                            <input
                                                :id="key"
                                                type="checkbox"
                                                name="file_type_selected"
                                                :value="key"
                                                v-model="values.field_details.file.type_selected"
                                                v-data-change
                                            >
                                            <label :for="key">@{{translations[('file_'+key)]}}@{{file_type_examples[key] != undefined ? ` (${file_type_examples[key]})` : ''}}</label>
                                        </div>
                                        {{-- 其他檔案類型 --}}
                                        <div class="ts labeled input" :class="{disabled: parent.config.mode=='view'}">
                                            <div
                                                class="ts label"
                                                style="padding-left: 0px; background: none; border-top: none; border-bottom: none; border-left: none;"
                                            >
                                                <div class="ts checkbox" >
                                                    <input
                                                        id="other"
                                                        type="checkbox"
                                                        name="file_type_selected"
                                                        value="other"
                                                        v-model="values.field_details.file.type_selected"
                                                        v-data-change
                                                    >
                                                    <label for="other">@{{translations.other}}</label>
                                                </div>
                                            </div>
                                            <textarea
                                                type="text"
                                                rows="5"
                                                placeholder="e.g. text/json,audio/mpeg,application/x-tar..."
                                                v-model="values.field_details.file.other"
                                                {{-- v-bind="{disabled: parent.config.mode=='view'}" --}}
                                                v-data-change
                                            ></textarea>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        {{-- 引用設定(reference) --}}
                            <div v-else-if="field_type === 'reference'">
                                <parent-child-selector
                                    v-if="parent.config.mode!='view'"
                                    :main-data="pageModules"
                                    :translations="translations"
                                    input-id="reference_module_selector"
                                    parent-key="page_module"
                                    item-name-key="page_name"
                                    key="reference_module_selector"
                                    ref="reference_module_selector"
                                    v-model="reference_page_selected.page_id"
                                    @cancel="reference_page_selected_cancel"
                                ></parent-child-selector>
                                <button
                                    v-if="parent.config.mode!='view'"
                                    class="ts button"
                                    style="margin-left: 0.85em"
                                    @click="showReferenceFields"
                                >@{{translations.field_list}}</button>
                                {{-- Reference Fields Select --}}
                                <div
                                    v-if="reference_fields_show && parent.config.mode!='view'"
                                    style="padding-left: 5.25rem; padding-right: 5.25rem;"
                                >
                                    <table
                                        id="reference_page_selected"
                                        class="ts attached table"
                                        :class="{
                                            bottom: formIndex != 0,
                                        }"
                                        v-for="(form,formIndex) in reference_page_selected.datas.forms"
                                    >
                                        <thead>
                                            <tr>
                                                <th colspan="3" class="center aligned">
                                                    @{{formIndex == 0 ? translations.page_head : translations.page_body+formIndex}}
                                                </th>
                                            </tr>
                                            <tr>
                                                <th>@{{field_data.field_code.translation}}</th>
                                                <th>@{{translations.name}}</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="field in form.fields">
                                                <td class="six wide">@{{field.field_code}}</td>
                                                <td class="nine wide">@{{field.translation}}</td>
                                                <td class="one wide center aligned">
                                                    <i
                                                        class="add large icon add-icon-button"
                                                        @click="addField(form,formIndex,field)"
                                                    ></i>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                {{-- reference.fields --}}
                                <fieldset class="tertiary" {{-- v-if="reference_fields > 0" --}}  style="margin-top:2.15rem">
                                    <legend>@{{translations.reference_source_field}}</legend>
                                    <table class="ts table">
                                        <thead>
                                            <tr>
                                                <th>@{{translations.module}}</th>
                                                <th>@{{translations.submodule}}</th>
                                                <th>@{{translations.page}}</th>
                                                <th>@{{translations.position}}</th>
                                                <th>@{{translations.field}}</th>
                                                <th>@{{translations.show}}</th>
                                                <th>@{{translations.order}}</th>
                                                <th>@{{translations.target}}</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="field in values.field_options.reference.fields">
                                                <td v-for="f in field.view_datas">@{{f}}</td>
                                                <td>
                                                    <div class="ts toggle checkbox" :class="{disabled: parent.config.mode =='view'}">
                                                        <input
                                                            type="checkbox"
                                                            v-model.lazy="field.show"
                                                            :id="'ref-'+field.field_code"
                                                            v-data-change
                                                        >
                                                        <label :for="'ref-'+field.field_code"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="ts field">
                                                        <input
                                                            type="number"
                                                            v-bind="{
                                                                min: 0,
                                                                max: values.field_options.reference.fields.length-1,
                                                                disabled: parent.config.mode =='view'
                                                            }"
                                                            v-model.number="field.order"
                                                            v-number-limit-validate
                                                            v-data-change
                                                            @change="resortField"
                                                        >
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="ts field">
                                                        <select
                                                            class="ts basic dropdown"
                                                            v-bind="{disabled: parent.config.mode=='view'}"
                                                            v-model="field.target"
                                                            v-data-change
                                                        >
                                                            <option :value="null"></option>
                                                            <optgroup
                                                                v-for="targets in reference_target_select"
                                                                :label="targets.form_name"
                                                            >
                                                                <option
                                                                    v-for="target in targets.fields"
                                                                    :value="target.field_code"
                                                                >@{{target.translation}}</option>
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <i
                                                        v-if="parent.config.mode!='view'"
                                                        class="remove large icon tr-remover"
                                                        @click="removeField(field)"
                                                    ></i>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </fieldset>
                                {{-- reference.tables --}}
                                <fieldset class="tertiary" {{-- v-if="reference_fields > 0"  --}} style="margin-top:2.15rem">
                                    <legend>@{{translations.reference_source_table}}</legend>
                                    <table class="ts table">
                                        <thead>
                                            <tr>
                                                <th>@{{translations.module}}</th>
                                                <th>@{{translations.submodule}}</th>
                                                <th>@{{translations.page}}</th>
                                                <th>@{{translations.position}}</th>
                                                <th>@{{translations.join_left}}</th>
                                                <th>@{{translations.comparison_operator}}</th>
                                                <th colspan="2">@{{translations.join_right}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(table,tableIndex) in values.field_options.reference.tables">
                                                <td v-for="t in table.view_datas">@{{t}}</td>
                                                <td>
                                                    <div
                                                        class="ts field"
                                                        :class="{disabled: tableIndex==0 || parent.config.mode=='view'}"
                                                    >
                                                        <select
                                                            class="ts basic dropdown"
                                                            v-model="table.join.left_column"
                                                            v-data-change
                                                        >
                                                            <option :value="null"></option>
                                                            <option
                                                                v-for="f in table.view_fields"
                                                                :value="f.field_code"
                                                            >@{{f.translation}}</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div
                                                        class="ts field"
                                                        :class="{disabled: tableIndex==0 || parent.config.mode=='view'}"
                                                    >
                                                        <select
                                                            class="ts basic dropdown"
                                                            v-bind="{disabled: tableIndex==0}"
                                                            v-model="table.join.comparison_operator"
                                                            v-data-change
                                                        >
                                                            <option value="="> = </option>
                                                            <option value=">"> > </option>
                                                            <option value="<"> < </option>
                                                            <option value=">="> >= </option>
                                                            <option value="<="> <= </option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div
                                                        class="ts field"
                                                        :class="{disabled: tableIndex==0 || parent.config.mode=='view'}"
                                                    >
                                                        <select
                                                            class="ts basic dropdown"
                                                            v-bind="{disabled: tableIndex==0}"
                                                            v-model="table.join.right_table"
                                                            @change="forceUpdate"
                                                            v-data-change
                                                        >
                                                            <option :value="null"></option>
                                                            <option
                                                                v-for="target in setReferenceTableSelect(table)"
                                                                :value="target.table_name"
                                                            >@{{target.view_datas.page+'_'+target.view_datas.form}}</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div
                                                        class="ts field"
                                                        :class="{disabled: tableIndex==0 || parent.config.mode=='view'}"
                                                    >
                                                        <select
                                                            class="ts basic dropdown"
                                                            v-model="table.join.right_column"
                                                            v-data-change
                                                        >
                                                            <option :value="null"></option>
                                                            <option
                                                                v-for="target in setReferenceJoinFieldSelect(tableIndex)"
                                                                :value="target.field_code"
                                                            >@{{target.translation}}</option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </fieldset>
                                {{-- reference.sql.expression.where --}}
                                <fieldset class="tertiary" {{-- v-if="reference_fields > 0" --}}  style="margin-top:2.15rem">
                                    <legend>@{{translations.reference_where}}</legend>
                                    <table class="ts table">
                                        <thead>
                                            <tr>
                                                <th>@{{translations['filter.group']}}</th>
                                                <th>@{{translations.logical_operator}}</th>
                                                <th>@{{translations.field}}</th>
                                                <th>@{{translations.comparison_operator}}</th>
                                                <th>@{{translations.value}}</th>
                                                <th>
                                                    <i
                                                        v-if="parent.config.mode!='view'"
                                                        class="add large icon add-icon-button"
                                                        @click="addWhere()"
                                                    ></i>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(expression,index) in values.field_options.reference.sql.expression.where">
                                                <td style="width: 150px">
                                                    <div
                                                        class="ts field"
                                                        :class="{disabled: index===0 || parent.config.mode=='view'}"
                                                    >
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            v-model="expression.group"
                                                            v-number-limit-validate
                                                            v-data-change
                                                            @change="resortWhere"
                                                        >
                                                    </div>
                                                </td>
                                                <td>
                                                    <div
                                                        class="ts field"
                                                        :class="{disabled: index===0 || parent.config.mode=='view'}"
                                                    >
                                                        <select v-model="expression.logical_operator" v-data-change>
                                                            <option value="AND">@{{translations.and}}</option>
                                                            <option value="OR">@{{translations.or}}</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div
                                                        class="ts field"
                                                        :class="{disabled: parent.config.mode=='view'}"
                                                    >
                                                        <select v-model="expression.column" v-data-change>
                                                            <option :value="null"></option>
                                                            <optgroup
                                                                v-for="(form) in setReferenceWhereSelect()"
                                                                :label="form.text"
                                                            >
                                                                <option
                                                                    v-for="field in form.fields"
                                                                    :value="field.value"
                                                                >@{{field.text}}</option>
                                                            </optgroup>
                                                        </select>
                                                    </div>

                                                </td>
                                                <td>
                                                    <div
                                                        class="ts field"
                                                        :class="{disabled: parent.config.mode=='view'}"
                                                    >
                                                        <select v-model="expression.comparison_operator" v-data-change>
                                                            <option value="="> = </option>
                                                            <option value=">"> > </option>
                                                            <option value="<"> < </option>
                                                            <option value=">="> >= </option>
                                                            <option value="<="> <= </option>
                                                            <option value="<>"> <> </option>
                                                            <option value="like"> LIKE </option>
                                                            <option value="not like"> NOT LIKE </option>
                                                        </select>
                                                    </div>

                                                </td>
                                                <td>
                                                    <div
                                                        class="ts field"
                                                        :class="{disabled: parent.config.mode=='view'}"
                                                    >
                                                        <input type="text" v-model="expression.operand" v-data-change>
                                                    </div>
                                                </td>
                                                <td>
                                                    <i
                                                        v-if="parent.config.mode!='view'"
                                                        class="remove large icon tr-remover"
                                                        @click="removeWhere(index)"
                                                    ></i>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </fieldset>
                                {{-- reference.other --}}
                                <fieldset class="tertiary" {{-- v-if="reference_fields > 0" --}}  style="margin-top:2.15rem">
                                    <legend>@{{translations.reference_other}}</legend>
                                    {{-- reference.type --}}
                                    <p>@{{translations.type}}</p>
                                    <div class="ts horizontal checkboxes" style="margin-bottom: 2.5em">
                                        <div class="ts radio checkbox" :class="{disabled: parent.config.mode =='view'}">
                                            <input
                                                type="radio"
                                                id="reference_list"
                                                name="reference_type"
                                                value="list"
                                                v-model="values.field_options.reference.type"
                                                v-data-change
                                            >
                                            <label for="reference_list">@{{translations.list}}</label>
                                        </div>
                                        <div class="ts radio checkbox" :class="{disabled: parent.config.mode =='view'}">
                                            <input
                                                type="radio"
                                                id="reference_select"
                                                name="reference_type"
                                                value="select"
                                                v-model="values.field_options.reference.type"
                                                v-data-change
                                            >
                                            <label for="reference_select">@{{translations.reference_select}}</label>
                                        </div>
                                        <div class="ts radio checkbox" :class="{disabled: parent.config.mode =='view'}">
                                            <input
                                                type="radio"
                                                id="reference_multiple"
                                                name="reference_type"
                                                value="multiple"
                                                v-model="values.field_options.reference.type"
                                                v-data-change
                                            >
                                            <label for="reference_multiple">@{{translations.reference_multiple}}</label>
                                        </div>
                                        <div class="ts radio checkbox" :class="{disabled: parent.config.mode =='view'}">
                                            <input
                                                type="radio"
                                                id="reference_readonly"
                                                name="reference_type"
                                                value="readonly"
                                                v-model="values.field_options.reference.type"
                                                v-data-change
                                            >
                                            <label for="reference_readonly">@{{translations.readonly}}</label>
                                        </div>
                                    </div>
                                    {{-- reference.front_field --}}
                                    <div class="ts inline field">
                                        <label>@{{translations.reference_front}}</label>
                                        <div
                                            class="ts toggle checkbox"
                                            :class="{disabled: parent.config.mode=='view'}"
                                            style="margin-right: 1.25rem"
                                        >
                                            <input
                                                type="checkbox"
                                                id="reference_front"
                                                v-model="values.field_options.reference.front_field.enabled"
                                                v-data-change
                                            >
                                            <label for="reference_front"></label>
                                        </div>
                                        <div class="ts fluid dropdowns">
                                            <select
                                                class="ts basic dropdown"
                                                v-bind="{disabled: parent.config.mode=='view'}"
                                                v-model="values.field_options.reference.front_field.form_id"
                                                @change="forceUpdate"
                                                v-data-change
                                            >
                                                <option :value="null"></option>
                                                <option
                                                    v-for="form in setReferenceFrontForm()"
                                                    :value="form.form"
                                                >@{{form.text}}</option>
                                            </select>
                                            <select
                                                class="ts basic dropdown"
                                                v-bind="{disabled: parent.config.mode=='view'}"
                                                v-model="values.field_options.reference.front_field.field_code"
                                                v-data-change
                                            >
                                                <option :value="null"></option>
                                                <option
                                                    v-for="field in setReferenceFrontField()"
                                                    :value="field.field_code"
                                                >@{{field.translation}}</option>
                                            </select>
                                            <select
                                                class="ts basic dropdown"
                                                v-bind="{disabled: parent.config.mode=='view'}"
                                                v-model="values.field_options.reference.front_field.target"
                                                v-data-change
                                            >
                                                <option :value="null"></option>
                                                <option
                                                    v-for="field in values.field_options.reference.fields"
                                                    :value="field.field_code"
                                                >@{{field.view_datas.field}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    {{-- reference.sql.native --}}
                                    <div class="ts inline field">
                                        <label>@{{translations.native_sql}}</label>
                                        <div
                                            class="ts toggle checkbox"
                                            :class="{disabled: parent.config.mode=='view'}"
                                            style="margin-right: 1.25rem"
                                        >
                                            <input
                                                type="checkbox"
                                                id="reference_native"
                                                v-model="values.field_options.reference.sql.native.enabled"
                                                v-data-change
                                            >
                                            <label for="reference_native"></label>
                                        </div>
                                        <textarea
                                            class="ts fluid input"
                                            :class="{disabled: parent.config.mode=='view'}"
                                            rows="6"
                                            v-model="values.field_options.reference.sql.native.sql"
                                            v-data-change
                                        ></textarea>
                                    </div>
                                    {{-- TODO: reference.order --}}
                                </fieldset>
                            </div>
                        {{-- 小視窗設定(reference_page) --}}
                            <div v-else-if="field_type === 'reference_page'">
                                <parent-child-selector
                                    :main-data="pageModules"
                                    :translations="translations"
                                    :disabled="parent.config.mode=='view'"
                                    :ignore-item="reference_page_ignoreItem"
                                    :value="values.field_options.reference_page.page_id"
                                    input-id="reference_page_selector"
                                    parent-key="page_module"
                                    item-name-key="page_name"
                                    key="reference_page_selector"
                                    ref="reference_page_selector"
                                    v-model="values.field_options.reference_page.page_id"
                                    {{-- @cancel="reference_page_cancel" --}}
                                ></parent-child-selector>
                            </div>
                        {{-- 按鈕設定(button) --}}
                            <div v-else-if="field_type === 'button'">
                            </div>
                    </div>
                    <div :class="field_type.indexOf('reference')!==-1?specialWide['reference-side']:specialWide['normal-side']"></div>
                </div>
            </form>
        </div>
        <div class="actions">
            <div class="ts fluid separated stackable buttons">
                <button class="ts positive button" id="modal_confirm" @click="confirmClick">
                    @{{translations.confirm}}
                </button>
                <button class="ts deny button" id="modal_cancel" @click="cancelClick">
                    @{{translations.cancel}}
                </button>
            </div>
        </div>
    </dialog>
</div>

<div id="vueel" v-cloak>
    <h3 class="ts header">@{{page_data.page.translation}}</h3>
    {{-- errors --}}
    <div class="ts inverted icon negative message" v-if="errors.length > 0">
        <i class="remove circle icon"></i>
        <div class="content">
            <p v-for="error in errors">@{{error.message}} <a v-if="error.link != null && error.link != undefined" class="link" id="#" @click="error.link">Click me</a></p>
        </div>
    </div>
    {{-- tabs --}}
    <div class="ts top attached tabbed menu">
        <a class="active item" data-tab="page_setting">@{{ translations.page_setting }}</a>
        <a class="item" data-tab="translation_setting">@{{ translations.SY_TRANSLATION }}</a>
        <a class="item" data-tab="field_setting">@{{ translations.field_setting }}</a>
    </div>
    {{-- for page --}}
    <div class="ts active bottom attached tab segment" data-tab="page_setting">
        <form class="ts horizontal form" id="page_setting">
            {{-- Code --}}
            <div class="required field" {{-- :class="{disabled: config.mode == 'update'}" --}}>
                <label>@{{page_fields.page_code.translation}}</label>
                <input
                    type="text"
                    v-model="input.page_setting.page_code"
                    v-bind="{id: page_fields.page_code.field_code, disabled: config.mode != 'insert'}"
                >
            </div>
            {{-- Module --}}
            <div class="required field">
                <label>@{{page_fields.page_module.translation}}</label>
                <parent-child-selector
                    :main-data="pageModules"
                    :ignore-type="['page']"
                    :translations="translations"
                    :disabled="config.mode == 'view'"
                    input-id="page_module"
                    parent-key="page_module"
                    item-name-key="page_name"
                    key="page_module"
                    ref="page_module"
                    {{-- v-model="input.page_setting.page_module" --}}
                    @any-change="putPageModule"
                ></parent-child-selector>
            </div>
            {{-- Template --}}
            <div class="required field">
                <label>@{{translations.page_template}}</label>
                <div
                    class="ts left aligned insetted fluid slate"
                    :class="{disabled: config.mode=='view'}"
                    style="padding-top: 1em; padding-bottom: 1em"
                >
                    <div class="ts description tiny link images" style="text-align: left;" id="templates">
                        <img
                            v-for="template in pageTemplates"
                            @click="templateClick(template)"
                            style="margin: 0.25rem"
                            :style="templateIsSelected(template)"
                            :src="getURL('image/image.png')"
                            :id="'template'+template"
                            :alt="template"
                        >
                    </div>
                </div>
            </div>
            {{-- Page Details --}}
            {{-- <div class="field" :class="isFieldRequired(page_fields.page_remarks)">
                <label></label>
                <button class="ts button" @click.prevent="pageDetailShow">ㄏ埃</button>
            </div> --}}
            {{-- Body --}}
            <div class="field">
                <label>@{{translations.page_has_body}}</label>
                <div>
                    <div class="ts toggle checkbox" :class="{disabled: body_switch_disabled}">
                        <input type="checkbox" id="page_has_body" v-model="input.page_setting.page_has_body">
                        <label for="page_has_body"></label>
                    </div>
                </div>
                <div class="ten wide field" :class="{required: input.page_setting.page_has_body}">
                    <label>@{{translations.page_body_number}}</label>
                    <input
                        type="number"
                        step="1"
                        :min="limit.body_number.min"
                        :max="limit.body_number.max"
                        id="page_body_number"
                        v-bind="{disabled: !input.page_setting.page_has_body||config.mode=='view'}"
                        v-model.number="input.page_setting.page_body_number"
                        v-number-limit-validate
                    >
                </div>
            </div>
            <div class="field">
                <label>@{{translations.page_allow_empty_body}}</label>
                <div>
                    <div class="ts toggle checkbox" :class="{disabled: !input.page_setting.page_has_body}">
                        <input type="checkbox" id="page_allow_empty_body" v-model="input.page_setting.page_options.allow_empty_body">
                        <label for="page_allow_empty_body"></label>
                    </div>
                </div>
            </div>
            {{-- Data Limit --}}
            <div class="field">
                <label>@{{translations.page_max}}</label>
                <input
                    type="number"
                    step="1"
                    :min="limit.data_max.min"
                    v-bind="{disabled: config.mode=='view'}"
                    v-model.number="input.page_setting.page_options.data_max"
                    v-number-limit-validate
                >
                <small>@{{translations.page_max_message}}</small>
            </div>
            {{-- Savable --}}
            <div class="field">
                <label>@{{translations.savable}}</label>
                <div>
                    <div class="ts toggle checkbox" :class="{disabled: config.mode=='view'}">
                        <input type="checkbox" id="page_savable" v-model="input.page_setting.page_options.savable">
                        <label for="page_savable"></label>
                    </div>
                </div>
            </div>
            {{-- Query Mode --}}
            <div class="field">
                <label>@{{translations.query_mode}}</label>
                <div>
                    <div class="ts toggle checkbox" :class="{disabled: config.mode=='view'}">
                        <input type="checkbox" id="page_query_mode" v-model="input.page_setting.page_options.query_mode.enabled">
                        <label for="page_query_mode"></label>
                    </div>
                </div>
            </div>
            {{-- Native SQL --}}
            <div class="field">
                <label>@{{translations.native_sql}}</label>
                <div>
                    <div class="ts toggle checkbox" :class="{disabled: config.mode=='view'}">
                        <input type="checkbox" id="page_native" v-model="input.page_setting.page_options.query_mode.native.enabled">
                        <label for="page_native"></label>
                    </div>
                </div>
                {{-- <div class="twelve wide field">
                    <label style="margin-top: 3.9%">SQL</label>
                </div> --}}
            </div>
            <div class="field">
                <label></label>
                <textarea
                    id="page_sql"
                    rows="5"
                    v-bind="{disabled: config.mode=='view'}"
                    v-model="input.page_setting.page_options.query_mode.native.sql"
                ></textarea>
            </div>
            {{-- Visible, Readonly --}}
            <div class="field" v-for="item in ['page_visible'{{-- , 'page_readonly' --}}]">
                <label>@{{page_fields[item].translation}}</label>
                <div>
                    <div class="ts toggle checkbox" :class="{disabled: config.mode=='view'}">
                        <input type="checkbox" :id="page_fields[item].field_code" v-model="input.page_setting[item]">
                        <label :for="page_fields[item].field_code"></label>
                    </div>
                </div>
            </div>
            {{-- Remarks --}}
            <div class="field" :class="isFieldRequired(page_fields.page_remarks)">
                <label>@{{page_fields.page_remarks.translation}}</label>
                <input
                    type="text"
                    v-model="input.page_setting.page_remarks"
                    v-bind="{
                        readOnly: isFieldReadOnly(page_fields.page_remarks),
                        id: page_fields.page_remarks.field_code,
                        disabled: config.mode=='view'
                    }"
                >
            </div>
        </form>
    </div>
    {{-- for translation --}}
    <div class="ts bottom attached tab segment" data-tab="translation_setting">
        <form class="ts horizontal form" id="SY_TRANSLATION">
            <div class="ts field" v-for="language in languages">
                <label>@{{language.language_name}}</label>
                <input
                    type="text"
                    v-bind="{id: language.language_id, disabled: config.mode=='view'}"
                    v-model="input.translation_setting[language.language_id]"
                >
            </div>
        </form>
    </div>
    {{-- for form & fields --}}
    <div class="ts bottom attached tab segment" data-tab="field_setting" style="overflow-x: auto">
        <form class="ts form" id="field_setting">
            <table :id="'form'+(formIndex-1)" :key="'form'+(formIndex-1)" class="ts stackable striped single line compact table" v-for="formIndex in field_setting_tables">
                <thead>
                    <tr>
                        <th colspan="11" style="font-weight: bold;">
                            @{{(formIndex-1) === 0 ? translations.page_head : translations.page_body + (formIndex-1)}}
                        </th>
                    </tr>
                    <tr>
                        <th style="text-align: center;">#</th>
                        {{-- <th style="text-align: center;">@{{translations.position}}</th> --}}
                        <th v-for="field in field_fields_show" style="text-align: center;">@{{field.translation}}</th>
                        <th class="one wide" style="text-align: center;" v-if="config.mode != 'view'">
                            <button
                                v-if="formIndex > 1 && !formSaved(formIndex-1)"
                                class="ts tiny very compact negative basic button"
                                @click="formRemove($event,formIndex-1)"
                            >
                                @{{translations.remove}}
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, rowIndex) in input.field_setting[formIndex-1]" :key="'row'+(rowIndex+1)" >
                        <td style="text-align: center;">@{{rowIndex+1}}</td>
                        {{-- <td style="text-align: center;"></td> --}}
                        <td v-for="(field) in field_fields_show" style="text-align: center;">
                            <div
                                class="ts field"
                                v-if="field.field_code == 'field_order'"
                                :class="{disabled: rowIndex == input.field_setting[formIndex-1].length-1}"
                            >
                                <input
                                    v-bind.prop="{
                                        type: parseFieldType(field.field_type),
                                        id: (formIndex-1) + field.field_code + rowIndex,
                                        disabled: config.mode=='view' || row.field_options.system_field,
                                        min: limit.field_order.min,
                                        max: input.field_setting[formIndex-1].length-1
                                    }"
                                    v-model="input.field_setting[(formIndex-1)][rowIndex][field.field_code]"
                                    v-input-auto-width
                                    v-number-limit-validate
                                    @change="rowResort(formIndex-1)"
                                >
                            </div>
                            <div
                                class="ts field"
                                v-else-if="field.field_code == 'field_code'"
                            >
                                <input
                                    v-bind="{
                                        type: parseFieldType(field.field_type),
                                        id: (formIndex-1) + field.field_code + rowIndex,
                                        disabled: fieldSaved(formIndex-1, rowIndex) || config.mode=='view' || row.field_options.system_field,
                                    }"
                                    v-model="input.field_setting[(formIndex-1)][rowIndex][field.field_code]"
                                    v-input-auto-width
                                    @input="autoAddOrRemoveRow((formIndex-1))"
                                >
                                <button
                                    {{-- v-if="row.field_options.system_field != true" --}}
                                    class="ts button"
                                    :id="'translation-form'+(formIndex-1)+'-row'+rowIndex"
                                    @click.prevent="translationShow(formIndex-1, rowIndex)"
                                >@{{translations.translation}}</button>
                            </div>
                            <div
                                class="ts field"
                                v-else-if="['text','number','date','time','datetime'].includes(parseFieldType(field.field_type))"
                            >
                                <input
                                    v-bind="{
                                        type: parseFieldType(field.field_type),
                                        id: (formIndex-1) + field.field_code + rowIndex,
                                        disabled: config.mode=='view' || row.field_options.system_field
                                    }"
                                    v-model="input.field_setting[(formIndex-1)][rowIndex][field.field_code]"
                                    v-input-auto-width
                                    @input="autoAddOrRemoveRow((formIndex-1))"
                                >
                            </div>
                            <div
                                class="ts field"
                                v-else-if="field.field_code == 'field_type'"
                            >
                                <select
                                    class="ts basic dropdown"
                                    v-bind="{
                                        type: parseFieldType(field.field_type),
                                        id: (formIndex-1) + field.field_code + rowIndex,
                                        readOnly: isFieldReadOnly(field),
                                        disabled: config.mode=='view' || row.field_options.system_field
                                    }"
                                    v-model="input.field_setting[(formIndex-1)][rowIndex][field.field_code]"
                                    style="width: 10.5em !important;"
                                    @change="autoAddOrRemoveRow((formIndex-1))"
                                >
                                    <option v-for="opt in showFieldType(field,(formIndex-1),rowIndex)" :value="opt">
                                        @{{translations[opt]}}
                                    </option>
                                </select>
                                <button
                                    v-if="row.field_options.system_field != true"
                                    class="ts button"
                                    :class="{disabled: input.field_setting[(formIndex-1)][rowIndex].field_type === null}"
                                    :id="'field_details-form'+(formIndex-1)+'-row'+rowIndex"
                                    @click.prevent="detailShow(formIndex-1, rowIndex)"
                                >@{{translations.field_details}}</button>
                            </div>
                            <div
                                v-else-if="parseFieldType(field.field_type) == 'boolean'"
                                class="ts toggle checkbox"
                                :class="{disabled: config.mode=='view' || (row.field_options.system_field && !['field_show_on_form','field_show_on_list'].includes(field.field_code))}"
                            >
                                <input
                                    type="checkbox"
                                    :id="(formIndex-1) + field.field_code + rowIndex"
                                    v-model="input.field_setting[(formIndex-1)][rowIndex][field.field_code]"
                                    @change="autoAddOrRemoveRow((formIndex-1))"
                                >
                                <label :for="(formIndex-1) + field.field_code + rowIndex"></label>
                            </div>
                        </td>
                        <td class="one wide" style="text-align: center;" v-if="config.mode != 'view'">
                            <i
                                v-if="!fieldSaved(formIndex-1, rowIndex) && row.field_options.system_field != true"
                                class="remove large icon tr-remover"
                                :id="'remove-form'+(formIndex-1)+'-row'+rowIndex"
                                @click="rowRemove(formIndex-1,rowIndex)"
                            ></i>
                        </td>
                    </tr>
                </tbody>
                {{-- TODO:串接資料(v-model)，刪除form時的程式也需要串聯資料 --}}
                <tfoot v-if="(formIndex-1) > 0">
                    <tr>
                        <td colspan="1">@{{translations.attached_to}}</td>
                        <td colspan="10">
                            <div
                                class="ts field"
                            >
                                <select
                                    v-model.number="input.form_setting[formIndex-1].form_parent"
                                    v-bind="{disabled: config.mode=='view'}"
                                >
                                    <option
                                        v-for="i in formIndex-1"
                                        :value="i-1"
                                    >@{{(i-1) === 0 ? translations.page_head : translations.page_body + (i-1)}}</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
    {{-- save button --}}
    <button
        v-if="config.mode != 'view'"
        class="ts primary button"
        :class="{disabled: config.sending}"
        @click="save"
    >
        @{{translations.save}}
    </button>
</div>

@endsection

@section('script')
@parent
<script>
    const page_data = @json($page_data);
    const field_data = @json($field_data);
    const translations = @json($translations);
    const languages = @json($languages);
    const pageModules = @json($modules);
    const pageType = "{{$type}}";
    const file_types = {
        image: ['image/bmp','image/gif','image/jpeg','image/svg+xml','image/tiff','image/x-icon','image/png'], /* 圖片檔 */
        video: ['video/mp4','video/ogg','video/quicktime','video/mpeg','video/mpeg4-generic','video/x-msvideo','video/webm','video/3gpp','video/3gpp2'], /* 影片檔 */
        audio: ['audio/mpeg','audio/ogg','audio/aac','audio/midi','audio/x-midi','audio/wav','audio/weba','audio/3gpp','audio/3gpp2','audio/flac','audio/x-flac','audio/x-aiff'], /* 音訊檔 */
        document: ['application/msword','application/vnd.oasis.opendocument.text','application/vnd.openxmlformats-officedocument.wordprocessingml.document'], /* 文件檔 */
        spread_sheet: ['application/vnd.ms-excel','application/vnd.oasis.opendocument.spreadsheet','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], /* 試算表 */
        presentation: ['appliction/vnd.ms-poewrpoint','appliction/vnd.oasis.opendocument.presentation','appliction/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.ms-pps','application/mspowerpoint'], /* 報表檔 */
        pdf: ['application/pdf'], /* PDF很常用 獨立出來 */
        csv: ['text/csv'], /* CSV很常用 獨立出來 */
        archive: ['application/x-rar.compressed','application/x-tar','application/zip','application/7z'], /* 壓縮檔 */
        text: ['text/plain','application/rtf'], /* 文字檔 xml, json還有js要放進來嗎? */
        other: '' /* 其他 */
    };
    const dataId = @json($dataId);

    @if(isset($data))
    let originData = @json($data);
    @else
    let originData = null;
    @endif

    const detailError = document.createElement("div");
    detailError.className = "ts inverted negative segment";
    detailError.style.marginTop = "0";
    detailError.appendChild(document.createElement("p"));

    Vue.directive('data-change', {
        bind: function(el, binding, vnode) {
            this.dataChange = () => {
                detailModal.dataChanged = true;
            }
            el.addEventListener('change', dataChange);
            // console.log(this, detailModal);
        },
        unbind: function(el, binding, vnode) {
            el.removeEventListener('change', this.dataChange);
        }
    });

    let loadingDimmer = new Vue({
        el:'#loading-dimmer',
        methods: {
            toggle: function(){
                const el = document.querySelector("#loading-dimmer");
                el.classList.toggle("active",!el.classList.contains("active"));
            }
        },
    });

    let translationModal = new Vue({
        el:'#translation-modal',
        data: {
            onShow: false,
            dataChanged: false,
            translations,
            languages,
            src: {},
            defaultLangauge:{},
            customLangauge:{}
        },
        computed: {},
        methods: {
            init: function(datas){
                this.parent = vueel;
                const src_setting = datas.fieldSetting;
                for(let language of languages){
                    this.defaultLangauge[language.language_id] = src_setting.translation.default[language.language_id];
                    this.customLangauge[language.language_id] = src_setting.translation.custom[language.language_id];
                }
                this.dataChanged = false;
            },
            dataIsChanged: function(){
                this.dataChanged = true;
            }
            /* isDisabled: function(type, language_id){
                const value = this.defaultLangauge[language_id];
                if(type == "default"){
                    return value != "" && value != null;
                }else if(type == "custom"){
                    return value == "" || value == null;
                }
            }, */
        },
    });

    let detailModal = new Vue({
        el:'#detail-modal',
        data:{
            onShow: false,
            isHead: true,
            hasWide: true,
            translations,
            pageModules,
            field_data: field_data.forms[0].fields,
            src: {},
            parent: null,
            modalStyle: {
                'overflow-y': 'auto',
                'width': '900px'
            },
            values:{},
            wides:['four', 'eight', 'twelve', 'sixteen'],
            rules:{
                unrestricted: 'unrestricted',
                rule_letter_numeric: 'rule_letter_numeric',
                rule_url: 'url',
                rule_email: 'email:rfc',
            },
            letter_numeric_items:{
                number: '0-9',
                upper_case: 'A-Z',
                lower_case: 'a-z',
                underline_hyphen: '\\-\\_'
            },
            file_types,
            file_type_examples:{
                image: ".jpg, .gif, .png...",
                video: ".mp4, .webm, .avi...",
                audio: ".mp3, .wav, .aac...",
                document: ".doc, .docx, .odt",
                spread_sheet: ".xls, .xlsx, .ods",
                presentation: ".ppt, .pptx, .odp...",
                archive: ".rar, .tar, .zip, .7z",
                text: ".txt, .rtf"
            },
            letter_numeric_disable: true,
            in_disabled: true,
            not_in_disabled: true,
            other_disabled: true,
            field_type: null,
            dataChanged: false,
            specialWide:{
                'normal-side': ["three","wide","column"],
                'normal-content': ["ten","wide","column"],
                'reference-side': [],
                'reference-content': ["sixteen","wide","column"]
            },
            reference_translations: {},
            reference_page_selected: {
                page_id: -1,
                datas: {},
            },
            reference_fields: 0,
            reference_fields_show: false,
            reference_target_select: [],
            reference_select:{
                fields_loading: false,
                field_id: null,
            }
        },
        computed:{
            parseFieldType: function(){
                const typeOf = {
                    text: ['string','textarea'],
                    number: ['integer','decimal'],
                    options: ['select','checkboxes','radio'],
                    // datetime: ['date','time','datetime'],
                };
                let result = '';
                for(x in typeOf){
                    if(typeOf[x].includes(this.field_type)){
                        result = x;
                        break;
                    }
                }
                result = result === '' ? this.field_type : result
                // console.log(this.field_type,result)
                return result;
            },
            hasSpecial: function(){
                const typeHasSpecial = ['text','number','options','reference_select','file','reference','reference_page'];
                return typeHasSpecial.includes(this.parseFieldType);
            },
            reference_page_ignoreItem: function(){
                let result = [];
                if(!isEmpty(this.parent.originData)){
                    result.push(this.parent.config.id);
                }
                return result;
            },
        },
        methods:{
            forceUpdate: function(){
                this.$forceUpdate();
            },
            async init(datas){
                // TODO: 檔案上傳不顯示能否複製，固定為false
                const src_setting = datas.fieldSetting;
                const typeHasWide = ['string','integer','decimal','select','date','time','datetime','file','reference','button'];

                this.parent = vueel;
                this.values = {
                    field_options: {
                        cloneable: true,
                        editable: true,
                    },
                    field_details: {
                        unique: false,
                    },
                };
                this.field_type = src_setting.field_type;
                this.dataChanged = false;
                this.modalStyle.width = "900px";
                this.src = deepClone(datas.src);
                this.src.readonly = src_setting.field_readonly;
                // this.$forceUpdate();
                this.isHead = this.src.form == 0;
                this.hasWide = typeHasWide.includes(this.field_type) && this.isHead;
                if(this.hasWide){
                    this.values.field_options.wide = 'four';
                }
                if(!this.isHead){
                    this.values.field_details.distinct = false;
                }

                const detail_type = this.parseFieldType;
                this.values.field_details[detail_type] = {};

                // TODO: 各型態欄位設定，改成在選擇型態時就放進field_details
                if(src_setting.field_details[detail_type] !== undefined){
                    // console.log("src has type");
                    for(let x in src_setting.field_details[detail_type]){
                        if(detail_type == "number"){
                            if((x == "integer_digits" || x == "decimal_digits") && this.field_type == "decimal"){
                                this.values.field_details[detail_type][x] = src_setting.field_details[detail_type][x];
                            }else if((x == "digits_max" || x == "digits_min") && this.field_type == "integer"){
                                this.values.field_details[detail_type][x] = src_setting.field_details[detail_type][x];
                            }
                            this.values.field_details[detail_type][x] = src_setting.field_details[detail_type][x];
                        }
                        this.values.field_details[detail_type][x] = src_setting.field_details[detail_type][x];
                    }
                }else if(detail_type == "text"){
                    this.values.field_details.text.content = "unrestricted";
                    this.values.field_details.text.letter_numeric_selected = [];
                    this.values.field_details.text.max = 2000;
                    this.values.field_details.text.min = 1;
                    this.values.field_details.text.in = "";
                    this.values.field_details.text.not_in = "";
                    this.values.field_details.text.other = "";
                }else if(detail_type == "number"){
                    this.values.field_details.number.max = 2147483647;
                    this.values.field_details.number.min = -2147483648;
                    this.values.field_details.number.integer_digits = 10;
                    this.values.field_details.number.decimal_digits = 0;
                    this.values.field_details.number.digits_max = 10;
                    this.values.field_details.number.digits_min = 1;
                }else if(detail_type == "file"){
                    this.values.field_details.file.type_selected = [];
                    this.values.field_details.file.other = '';
                }

                if(detail_type == "text"){this.stringRuleChange();}
                else if(detail_type == "options"){this.values.field_options.options = [''];}
                else if(detail_type == "file"){this.values.field_options.file_type = [];}
                else if(detail_type == "reference"){
                    this.values.field_options.reference = {
                        tables: [],
                        fields: [],
                        front_field: {
                            enabled: false,
                            form_id: null,
                            field_code: null,
                            target: null
                        },
                        type: "list",
                        select_field: null,
                        sql: {
                            native: {
                                enabled: false,
                                sql: null
                            },
                            expression: {
                                where: [
                                    {
                                        group: 0,
                                        logical_operator: "AND",
                                        column: null,
                                        comparison_operator: null,
                                        operand: null
                                    }
                                ]
                            }
                        }
                    };
                    this.reference_fields_show = false;
                    if(src_setting.field_options.reference != undefined){
                        this.reference_fields = src_setting.field_options.reference.fields.length;
                        if(src_setting.field_options.reference.type === undefined){
                            src_setting.field_options.reference.type = "list";
                        }
                    }else{
                        this.reference_fields = 0;
                    }
                    // console.log(this.$refs);
                    if(this.$refs.reference_module_selector != undefined){
                        this.$refs.reference_module_selector.cancelClick();
                    }
                    this.setReferenceTargetSelect();
                    this.modalStyle.width = "100%";
                    this.reference_page_selected = {
                        page_id: -1,
                        datas: {},
                    }
                }else if(detail_type == "reference_page"){
                    this.values.field_options.reference_page = {
                        page_id: -1,
                    };
                    // this.modalStyle.width = "1200px";
                }

                for(let x in this.values.field_options){
                    if(!isEmpty(src_setting.field_options[x])){
                        this.values.field_options[x] = deepClone(src_setting.field_options[x]);
                    }
                }
                for(let x in this.values.field_details){
                    if(typeof this.values.field_details[x] != "object" && src_setting.field_details[x] != undefined){
                        this.values.field_details[x] = deepClone(src_setting.field_details[x]);
                    }
                }
                console.log("this.value =>\r\n",this.values);
                await this.$forceUpdate();
                const options = this.values.field_options.options;
                if(!isEmpty(options)){
                    const last = options.length;
                    const lastEl = document.querySelector(`#item${last}`);
                    if(!isEmpty(lastEl)) lastEl.focus();
                }
            },
            async addItem(){
                this.values.field_options.options.push('');
                await this.$forceUpdate();

                const last = this.values.field_options.options.length;
                const lastEl = document.querySelector(`#item${last}`);
                if(!isEmpty(lastEl)) lastEl.focus();
            },
            removeItem: function(event){
                const remover = /^(remove\-item\-)([0-9])+$/i;
                if(remover.test(event.target.id) && this.values.field_options.options.length > 1){
                    const id = event.target.id;
                    const index = id.match(remover)[2];
                    let temp = [];
                    for(let i in this.values.field_options.options){
                        if(i !== index){
                            temp.push(this.values.field_options.options[i])
                        }
                    }
                    this.values.field_options.options = temp;
                    this.$forceUpdate();
                }
            },
            detailChanged: function(){
                if(!this.dataChanged){this.dataChanged = true}
            },
            stringRuleChange: function(){
                const content = this.values.field_details.text.content;
                this.letter_numeric_disable = content !== 'rule_letter_numeric';
                this.in_disabled = content !== 'rule_in';
                this.not_in_disabled = content !== 'rule_not_in';
                this.other_disabled = content !== 'other';
                // this.$forceUpdate();
            },
            showReferenceFields: function(event){
                event.preventDefault();
                if(this.reference_page_selected.page_id !== -1 && (this.reference_page_selected.datas.page == undefined || this.reference_page_selected.datas.page.page_id != this.reference_page_selected.page_id)){
                    event.target.classList.add("loading");
                    event.target.classList.add("disabled");
                    sendAPIRequest(getURL(`api/system/pages/getPageFields/${this.reference_page_selected.page_id}`),"get",null).then(result => {
                        this.reference_page_selected.datas = result;
                        this.reference_fields_show = result.forms != undefined && result.forms.length > 0;
                        event.target.classList.remove("loading");
                        event.target.classList.remove("disabled");
                    });
                }
            },
            reference_page_selected_cancel: function(event){
                this.reference_fields_show = false;
                this.reference_page_selected.datas = {};
            },
            addField: function(form, formIndex, field){
                const srcTableName = `${this.reference_page_selected.datas.page.page_code}_${form.form_id}`;
                const viewDatas = {
                    module: this.$refs.reference_module_selector.$refs.module[0].selectedText,
                    submodule: this.$refs.reference_module_selector.$refs.submodule[0].selectedText,
                    page: this.reference_page_selected.datas.page.translation,
                    form: formIndex == 0 ? this.translations.page_head : this.translations.page_body+formIndex,
                    field: field.translation
                };
                const reference_field = {
                    field_code: field.field_code,
                    table_name: srcTableName,
                    show: false,
                    target: null,
                    order: this.values.field_options.reference.fields.length,
                    view_datas: deepClone(viewDatas)
                };
                delete viewDatas.field;
                const reference_table = {
                    table_name: srcTableName,
                    join:
                    {
                        left_column: null,
                        comparison_operator: "=",
                        right_table: null,
                        right_column: null
                    },
                    view_datas: deepClone(viewDatas),
                    view_fields: form.fields
                };

                if(this.values.field_options.reference.fields.find(x => x.field_code == field.field_code) === undefined){
                    this.values.field_options.reference.fields.push(reference_field);
                    this.reference_fields++;
                    // console.log(reference_field);
                    if(this.values.field_options.reference.tables.find(x => x.table_name == srcTableName) === undefined){
                        delete reference_table.view_datas.field;
                        this.values.field_options.reference.tables.push(reference_table);
                        dataChanged = true;
                    }
                }
                this.$forceUpdate();
            },
            removeField: function(field){
                const toDelFieldCode = field.field_code;
                const toDelFiledIndex = this.values.field_options.reference.fields.findIndex(x => x.field_code === toDelFieldCode);
                if(toDelFiledIndex != -1){
                    delete this.values.field_options.reference.fields[toDelFiledIndex];
                    this.values.field_options.reference.fields = this.values.field_options.reference.fields.filter(() => true);
                    this.reference_fields--;
                    if(this.values.field_options.reference.fields.find(x => x.table_name === field.table_name) == undefined){
                        const toDelTableIndex = this.values.field_options.reference.tables.findIndex(x => x.table_name === field.table_name);
                        delete this.values.field_options.reference.tables[toDelTableIndex];
                        this.values.field_options.reference.tables = this.values.field_options.reference.tables.filter(() => true);
                        dataChanged = true;
                    }
                    this.resortField();
                }
            },
            resortField: function(){
                this.values.field_options.reference.fields.sort(function(a,b){
                    return a.order - b.order;
                });
                for(let f in this.values.field_options.reference.fields) {
                    this.values.field_options.reference.fields[f].order = Number(f);
                }
                this.$forceUpdate();
            },
            addWhere: function(){
                this.values.field_options.reference.sql.expression.where.push({
                    group: 0,
                    logical_operator: "AND",
                    column: null,
                    comparison_operator: null,
                    operand: null
                });
                this.resortWhere();
            },
            removeWhere: function(index){
                delete this.values.field_options.reference.sql.expression.where[index]
                this.values.field_options.reference.sql.expression.where = this.values.field_options.reference.sql.expression.where.filter(() => true);
                if(this.values.field_options.reference.sql.expression.where-1 <= 0) this.addWhere();
                this.resortWhere();
            },
            resortWhere: function(){
                this.values.field_options.reference.sql.expression.where.sort(function(a,b){
                    return a.group - b.group;
                });
                this.$forceUpdate();
            },
            getReferenceFieldTranslation: function(field){
                const trans = field.translation;
                const language = 1; //window.language;
                const default_language = 1; //window.default_language;
                let result = field.field_code;
                for(x of ["custom","default"]){
                    for(y of [language,default_language]){
                        if(trans[x][y] != null && trans[x][y] != "") result = trans[x][y];
                        break;
                    }
                }
                return result;
            },
            setReferenceTargetSelect: function(){
                let result = [];
                const numOfForms = this.parent.field_setting_tables-1;
                const srcForm = this.src.form;
                const formName = srcForm == 0 ? this.translations.page_head : this.translations.page_body+srcForm
                result.push({
                    form_name: formName,
                    fields: []
                });
                for(let j of this.parent.input.field_setting[srcForm]){
                    if(j.field_code != null && j.field_code != ""){
                        let temp = {
                            field_code: j.field_code,
                            translation: this.getReferenceFieldTranslation(j)
                        };
                        result[0].fields.push(temp);
                    }
                }
                if(result[0].fields.length <= 0) delete result[0];
                result = result.filter(() => true);
                this.reference_target_select = result;
            },
            setReferenceTableSelect: function(table){
                let result = [];
                const allTable = this.values.field_options.reference.tables;
                const tableIndex = allTable.findIndex(x => x.table_name == table.table_name);
                for(let i = 0; i < tableIndex; i++){
                    result.push(allTable[i]);
                }
                /* for(let t of this.values.field_options.reference.tables){
                    if(t.table_name != table.table_name){
                        result.push(t);
                    }
                } */
                return result;
            },
            setReferenceJoinFieldSelect: function(tableIndex){
                let result = {};
                const targetTable = this.values.field_options.reference.tables[tableIndex].join.right_table;
                if(targetTable != null){
                    const target = this.values.field_options.reference.tables.find(x => x.table_name == targetTable);
                    if(target != undefined){
                        result = target.view_fields;
                    }
                }
                return result;
            },
            setReferenceWhereSelect: function(){
                let result = {};
                const allFields = this.values.field_options.reference.fields;
                for(let x of allFields){
                    const formName = `${x.view_datas.page}_${x.view_datas.form}`;
                    if(result[x.table_name] === undefined){
                        result[x.table_name] = {
                            text: formName,
                            fields: []
                        };
                    }
                    result[x.table_name].fields.push({
                        text: x.view_datas.field,
                        value: `${x.table_name}.${x.field_code}`
                    });
                }
                return result;
            },
            setReferenceFrontForm: function(){
                let result = [];
                const srcForm = this.src.form;
                const forms = this.parent.input.field_setting;
                for(let form in forms){
                    form = Number(form);
                    if(form == srcForm || form === 0){
                        result.push({
                            form: form,
                            text: form == 0 ? this.translations.page_head : this.translations.page_body+form
                        });
                    }
                }
                return result;
            },
            setReferenceFrontField: function(){
                let result = [];
                const form = this.values.field_options.reference.front_field.form_id;
                // const formName = srcForm == 0 ? this.translations.page_head : this.translations.page_body+srcForm
                if(this.parent.input.field_setting[form] != undefined){
                    for(let j of this.parent.input.field_setting[form]){
                        if(j.field_code != null && j.field_code != ""){
                            let temp = {
                                field_code: j.field_code,
                                translation: this.getReferenceFieldTranslation(j)
                            };
                            result.push(temp);
                        }
                    }
                }
                // console.log(result);
                return result;
            },
            cantEdit: function(){
                if(this.src.readonly){
                    this.values.field_options.readonly = '';
                }
                return this.src.readonly;
            },
            equalityComparison: function(a, b){
                return equalityComparison(a, b, true);
            },
            confirmClick: function(event){
                // console.log(this.values);
            },
            cancelClick: function(event){

            }
        },
    });

    let vueel = new Vue({
        el: '#vueel',
        data:{
            config: {
                sending : false,
                loading: false,
                mode: pageType,
                id: dataId,
                status: "loading"
            },
            originData,
            pageModules,
            page_data,
            page_fields: page_data.forms[0].fields,
            pageTemplates:['universal','report','electric_board','chart'],
            field_data,
            field_fields: field_data.forms[0].fields,
            languages,
            translations,
            input:{
                page_setting:{
                    page_code: "",
                    page_module: 0,
                    page_visible: true,
                    page_readonly: false,
                    page_form_template: 'universal',
                    page_has_body: false,
                    page_body_number: 1,
                    page_options: {
                        data_max: -1,
                        allow_empty_body: false,
                        savable: true,
                        query_mode: {
                            enabled: false,
                            native: {
                                enabled: false,
                                sql: '',
                            },
                        },
                    },
                    page_remarks: null,
                },
                translation_setting:{},
                form_setting:[],
                field_setting: [],
            },
            file_types,
            limit:{
                body_number: {
                    min: 1,
                    max: 10
                },
                data_max:{
                    min: -1,
                },
                field_order:{
                    min: -1,
                },
            },
            errors:[],
        },
        mounted() {
            for(let i = 0; i <= this.limit.body_number.max; i++){
                this.autoAddOrRemoveRow(i);
            }
            if(this.config.mode != 'insert'){
                if(this.originData != null){
                    console.log(this.originData);
                    this.input.page_setting = deepClone(this.originData.page_setting);
                    /* 向前相容 */
                    for(let p in this.pageInit.page_options){
                        if(this.input.page_setting.page_options[p] === undefined){
                            this.input.page_setting.page_options[p] = this.pageInit.page_options[p];
                        }
                    }
                    /* 以後要拿掉 */
                    this.$refs.page_module.inputValue(this.input.page_setting.page_module);
                    for(let i in this.originData.translation_setting){
                        const temp = this.originData.translation_setting[i];
                        if(temp != null && temp != ""){
                            this.input.translation_setting[i] = temp;
                        }
                    }
                    const formNum = this.originData.page_setting.page_has_body ? this.originData.page_setting.page_body_number : 0;
                    this.limit.body_number.min = formNum===0 ? 1 : formNum;
                    for(let i = 0; i <= formNum; i++){
                        const formTemp = deepClone(this.originData.form_setting[i]);
                        const fieldTemp = deepClone(this.originData.field_setting[i]);
                        if(!isEmpty(formTemp)) this.input.form_setting[i] = formTemp;
                        if(!isEmpty(fieldTemp)) this.input.field_setting[i] = fieldTemp;
                        // 回填parent_form
                        const parent_id = Number(this.input.form_setting[i].form_parent);
                        const parent_index = this.input.form_setting.findIndex(x => x.form_id == parent_id);
                        this.input.form_setting[i].form_parent = parent_index == -1 ? null : parent_index;

                        for(let j in this.input.field_setting[i]){
                            const field = this.input.field_setting[i][j];
                            if(field.field_options != null && field.field_options.reference != undefined){
                                const front = field.field_options.reference.front_field;
                                if(front.form_id != null){
                                    const front_form = this.input.form_setting.findIndex(x => x.form_id == front.form_id);
                                    this.input.field_setting[i][j].field_options.reference.front_field.form_id = front_form == -1 ? null : front_form;
                                }
                            }
                        }
                        this.autoAddOrRemoveRow(i);
                    }

                    // 卡控
                    this.limit.body_number_min = this.originData.page_setting.page_body_number;
                }
            }
            loadingDimmer.toggle();
            console.log(`forms =>`,this.input.form_setting);
            console.log(`fields =>`,this.input.field_setting);
        },
        computed:{
            field_fields_show: function(){
                let result = [];
                for(let f of this.fieldResort(this.field_fields)){
                    if(f.field_show_on_form == "1"){
                        result.push(f);
                    }
                }
                return result;
            },
            field_setting_tables: function(){
                let result = 1;
                if(this.input.page_setting.page_has_body && this.input.page_setting.page_body_number >= 1 && this.input.page_setting.page_body_number <= 10){
                    result = this.input.page_setting.page_body_number + 1;
                }
                return result;
            },
            body_switch_disabled: function(){
                let result = false;
                if(this.config.mode == 'update'){
                    if(this.originData != null && this.originData.page_setting.page_has_body){
                        result = true;
                    }
                }else if(this.config.mode == 'view'){
                    result = true;
                }
                return result;
            },
            pageInit(){
                return deepClone({
                    page_code: "",
                    page_module: 0,
                    page_visible: true,
                    page_readonly: false,
                    page_form_template: 'universal',
                    page_has_body: false,
                    page_body_number: 1,
                    page_options: {
                        data_max: -1,
                        allow_empty_body: false,
                        savable: true,
                        query_mode: {
                            enabled: false,
                            native: {
                                enabled: false,
                                sql: '',
                            },
                        },
                    },
                    page_remarks: null,
                });
            },
            page_details: function(){
                const details = {
                    hidden: false,
                    body: {
                        hidden: false,
                        page_has_body: {
                            'default-value': false,
                            hidden: false,
                            disabled: false,
                        },
                        page_body_number: {
                            'default-value': 1,
                            hidden: false,
                            disabled: true,
                        },
                        allow_empty_body: {
                            'default-value': false,
                            hidden: false,
                            disabled: true,
                        },
                    },
                    special:{
                        data_max: {
                            'default-value': -1,
                            hidden: false,
                            disabled: false,
                        },
                        query_mode: {
                            enabled: false,
                        },
                    },
                };
                const template = this.input.page_setting.page_form_template;

                if(template == "universal"){
                    return details;
                }
            },
        },
        methods:{
            async save(){
                const unknownError = () => {
                    this.saveFailed([{
                        message: this.translations['error.unknown']+this.translations['contact_maintenance'],
                        link: null
                    }]);
                    return false;
                };
                if(this.config.mode != 'view'){
                    this.errors = [];
                    // fullscreenDimmer.loading();
                    this.config.sending = true;
                    this.config.status = "processing";
                    this.loadingText();
                    await this.delay(1600);

                    let inputClone = deepClone(this.input);
                    let url = getURL(`pages/save/${this.config.mode}`);
                    if(this.config.mode == 'update'){
                        url += `/${this.config.id}`;
                    }
                    try {
                        for(let formIndex = 0; formIndex <= this.field_setting_tables-1; formIndex++){
                            let saveFields = [];
                            formIndex = Number(formIndex);
                            for(let rowIndex in this.input.field_setting[formIndex]){
                                rowIndex = Number(rowIndex);
                                let row = deepClone(this.input.field_setting[formIndex][rowIndex]);
                                if(this.checkRowIsEmpty(row)){
                                    this.rowRemove(formIndex,rowIndex,false,true);
                                }
                                // console.log(formIndex, rowIndex, this.input.field_setting[formIndex][rowIndex])
                                if(this.input.field_setting[formIndex][rowIndex] != undefined){
                                    const detail_type = this.parseDetailType(row.field_type);
                                    if((row.field_details[detail_type] == undefined && ["text","number","reference","file"].includes(detail_type))){
                                        row.field_details.edited = false;
                                    }
                                    row.field_rule = [];

                                    if(row.field_details.edited){
                                        const ruleWithValue = [
                                            "max",
                                            "min"
                                        ];
                                        const ruleWithoutValue = [
                                            "url",
                                            "email:rfc",
                                            "unique",
                                            "distinct",
                                        ];

                                        let required_type = "required"
                                        if(row.field_type == "checkboxes"){
                                            row.field_rule.push("checkboxes_in");
                                            required_type = "checkboxes_required";
                                        }else if(["text","options","reference"].includes(detail_type)){
                                            row.field_rule.push("string");
                                        }else if(row.field_type == "decimal"){
                                            row.field_rule.push("numeric");
                                        }else if(row.field_type == "integer"){
                                            row.field_rule.push("integer");
                                        }else if(detail_type == "boolean"){
                                            row.field_rule.push("boolean");
                                        }else if(detail_type == "date"){
                                            row.field_rule.push("date_format:Y-m-d");
                                        }else if(detail_type == "time"){
                                            row.field_rule.push("date_format:H:i:s");
                                        }else if(detail_type == "datetime"){
                                            row.field_rule.push("date_format:Y-m-d H:i:s");
                                        }/* else if(!["file","button"].includes(detail_type)){
                                            row.field_rule.push(detail_type);
                                        } */

                                        row.field_rule.push(row.field_required ? required_type : "nullable");

                                        for(let detailKey in row.field_details){
                                            if(detailKey == detail_type){
                                                for(let specialKey in row.field_details[detail_type]){
                                                    const special = row.field_details[detail_type][specialKey];

                                                    const ignoreDetail = ["integer_digits","decimal_digits"];
                                                    if(specialKey == "content"){
                                                        if(special == "rule_letter_numeric"){
                                                            let val = "";
                                                            for(x of row.field_details[detail_type].letter_numeric_selected){
                                                                val += x;
                                                            }
                                                            row.field_rule.push(`regex:\/\^\[${val}\]\*\$\/`);
                                                        }else if(special == "rule_in"){
                                                            let val = [];
                                                            for(x of row.field_details[detail_type].in.split(",")){
                                                                val.push(x);
                                                            }
                                                            row.field_rule.push({
                                                                in: val
                                                            });
                                                        }else if(special == "rule_not_in"){
                                                            let val = [];
                                                            for(x of row.field_details[detail_type].not_in.split(",")){
                                                                val.push(x);
                                                            }
                                                            row.field_rule.push({
                                                                not_in: val
                                                            });
                                                        }else if(special == "other"){
                                                            let val = '';
                                                            row.field_rule.push(`regex:\/${row.field_details[detail_type].other}\/`);
                                                        }else if(ruleWithoutValue.includes(special)){
                                                            row.field_rule.push(special);
                                                        }
                                                    }else if(ruleWithValue.includes(specialKey)){
                                                        row.field_rule.push(`${specialKey}:${special}`);
                                                    }else if(ruleWithoutValue.includes(specialKey)){
                                                        row.field_rule.push(specialKey);
                                                    }
                                                }
                                            }else if(typeof row.field_details[detailKey] != "object"){
                                                const detail = row.field_details[detailKey];
                                                if(ruleWithValue.includes(detailKey)){
                                                    row.field_rule.push(`${detailKey}:${detail}`);
                                                }else if(ruleWithoutValue.includes(detailKey)){
                                                    if(detail === true){
                                                        row.field_rule.push(detailKey);
                                                    }
                                                }
                                            }
                                        }

                                        if(row.field_type == "decimal"){
                                            const int_digits = row.field_details.number.integer_digits;
                                            const dec_digits = row.field_details.number.decimal_digits;
                                            if(int_digits == undefined || dec_digits == undefined){
                                                row.field_details.edited = false;
                                            }else{
                                                row.field_options.decimal = {
                                                    total: int_digits+dec_digits,
                                                    decimal: dec_digits
                                                };
                                            }
                                        }else{
                                            delete row.field_options.decimal;
                                        }

                                        if(row.field_type == "file"){
                                            let file_rule = "";
                                            const file = row.field_details.file;
                                            if(file == undefined){
                                                row.field_details.edited = false;
                                            }else{
                                                for(let f of file.type_selected){
                                                    if(f == "other"){
                                                        row.field_options.file_type.push(file.other.split(",").map(function(item){return item.trim()}));
                                                    }else{
                                                        row.field_options.file_type.push(f);
                                                    }
                                                }
                                            }
                                        }else{
                                            delete row.field_options.file_type;
                                        }

                                        if(row.field_type == "select" || row.field_type == "checkboxes" || row.field_type == "radio"){
                                            if(row.field_options.options === undefined){
                                                row.field_details.edited = false;
                                            }else if(row.field_type !== "checkboxes"){
                                                row.field_rule.push({
                                                    in: row.field_options.options
                                                });
                                            }
                                        }else{
                                            delete row.field_options.options;
                                        }

                                        if(row.field_type == "reference"){
                                            const reference = row.field_options.reference;
                                            if(reference === undefined){
                                                row.field_details.edited = false;
                                            }else{
                                                const main = reference.fields.findIndex(x => x.target == row.field_code);
                                                if(reference.fields.length > 0){
                                                    reference.select_field = main > -1 ? reference.fields[main].field_code : reference.fields[0].field_code;
                                                }
                                            }
                                        }else{
                                            delete row.field_options.reference;
                                        }

                                        if(row.field_type == "reference_page"){
                                            if(row.field_options.reference_page === undefined){
                                                row.field_details.edited = false;
                                            }
                                        }else{
                                            delete row.field_options.reference_page;
                                        }
                                    }
                                    saveFields.push(row);
                                }
                            }
                            inputClone.field_setting[formIndex] = saveFields;
                        }
                        console.log("save input=>",inputClone);
                    } catch (error) {
                        console.error(error);
                        return unknownError();
                    }
                    this.config.status = "accessing";
                    sendAPIRequest(url,"post",inputClone).then(result => {
                        if(result.success){
                            this.config.status = "redirecting";
                            document.location.href = getURL('pages/list');
                        }else{
                            console.log(result);
                            let validationErrors = [];
                            if(result.errors != undefined && result.errors.length > 0){
                                for(let error of result.errors){
                                    let link = function(){vueel.focusError(error.tab, error.id)};
                                    if(error.type == "no_details"){
                                        link = function(){vueel.detailShow(error.formIndex,error.rowIndex)};
                                    }else if(error.type == "field_type_error"){
                                        // this.input.field_setting[error.formIndex][error.rowIndex] = deepClone(this.originData.field_setting[error.formIndex][error.rowIndex]);
                                        const field = this.input.field_setting[error.formIndex][error.rowIndex];
                                        const originField = deepClone(this.originData.field_setting[error.formIndex].find(x => x.field_code == field.field_code))
                                        if(originField != undefined){
                                            field.field_type = originField.field_type;
                                        }
                                    }
                                    validationErrors.push({
                                        message: error.message,
                                        link
                                    });
                                }
                            }
                            this.saveFailed(validationErrors);
                            return false;
                        }
                    }).catch(e => {
                        return unknownError();
                    });
                }
            },
            saveFailed: function(errors){
                const bodyNum = this.input.page_setting.page_has_body ? this.input.page_setting.page_body_number : 0
                for(let i = 0; i <= bodyNum; i++){
                    this.autoAddOrRemoveRow(i);
                }
                // fullscreenDimmer.unloading();
                this.config.sending = false;
                for(let e of errors){
                    this.errors.push(e);
                }
                document.querySelector("#content").scrollTop = 0;
            },
            pageDetailShow: function(){},
            detailShow: function(formIndex, rowIndex){
                const fieldSetting = this.input.field_setting[formIndex][rowIndex];
                if(fieldSetting.field_type == undefined || fieldSetting.field_type == "" || fieldSetting.field_type == null){
                    alert(this.translations.field_type_first);
                    this.focusError("field_setting", `${formIndex}field_type${rowIndex}`);
                }else{
                    const modalDatas = {
                        fieldSetting: fieldSetting,
                        src: {
                            form: Number(formIndex),
                            row: Number(rowIndex)
                        }
                    };
                    detailModal.onShow = true;
                    detailModal.init(modalDatas);
                    ts('#detailModal').modal({
                        onApprove: function() {
                            fieldSetting.field_details.edited = true;
                            for(let x of ['field_details', 'field_options']){
                                for(let y in detailModal.values[x]){
                                    fieldSetting[x][y] = detailModal.values[x][y];
                                }
                            }
                            detailModal.onShow = false;
                        },
                        onDeny: function() {
                            let toClose = true;
                            if(detailModal.dataChanged){
                                toClose = confirm(translations.unsave_confirm);
                            }
                            if(toClose){
                                detailModal.onShow = false;
                            }
                            return toClose;
                        }
                    }).modal("show");
                    setTimeout(() => {
                        ts('.ts.dropdown:not(.basic)').dropdown();
                    },500);
                    document.querySelector("#detailModal").scrollTop = 0;
                }
            },
            translationShow: function(formIndex, rowIndex){
                const fieldSetting = this.input.field_setting[formIndex][rowIndex];
                const modalDatas = {
                    fieldSetting: fieldSetting,
                };
                translationModal.onShow = true;
                translationModal.init(modalDatas);
                ts('#translationModal').modal({
                    onApprove: function() {
                        fieldSetting.translation.custom = deepClone(translationModal.customLangauge);
                        fieldSetting.translation.default = deepClone(translationModal.defaultLangauge);
                        translationModal.onShow = false;
                    },
                    onDeny: function() {
                        let toClose = true;
                        if(translationModal.dataChanged){
                            toClose = confirm(translations.unsave_confirm);
                        }
                        if(toClose){
                            translationModal.onShow = false;
                        }
                        return toClose;
                    }
                }).modal("show");
                document.querySelector("#translationModal").scrollTop = 0;
            },
            focusError: function(tab, id = null){
                const tabEl = document.querySelector(`.ts.tabbed.menu a[data-tab="${tab}"]`);
                if(tabEl != undefined){
                    tabEl.dispatchEvent(new Event('click'));
                }
                if(id !== null){
                    const el = document.getElementById(id);
                    if(el != undefined){
                        // console.log(el);
                        setTimeout(() => {
                            el.focus();
                        },100);
                    }
                }
            },
            putPageModule:function(moduleType, value){
                this.input.page_setting.page_module = value;
            },
            isFieldReadOnly: function(field){
                let isReadonly = false;
                if(field.field_readonly == "1"){
                    if(field.field_options != null && field.field_options.readonly != undefined){
                        isReadonly = field.field_options.readonly == this.config.mode;
                    }else{
                        isReadonly = true;
                    }
                }
                return isReadonly;
            },
            isFieldRequired: function(field){
                return {required: field.field_required == '1'};
            },
            formSaved: function(formIndex){
                for(rowIndex in this.input.field_setting[formIndex]){
                    if(this.fieldSaved(formIndex, rowIndex))
                        return true;
                }
                return false;
            },
            fieldSaved: function(formIndex, rowIndex){
                const field = this.input.field_setting[formIndex][rowIndex];
                return field.field_details.saved !== undefined && field.field_details.saved;
            },
            getURL: function(URL){
                return getURL(URL);
            },
            templateClick: function(template){
                this.input.page_setting.page_form_template = template;
            },
            templateIsSelected: function(item){
                if(this.input.page_setting.page_form_template == item){
                    return {border: '0.3em solid #1e7fcb'};
                }else{
                    return {};
                }
            },
            parseFieldType: function(type){
                const typeOf = {
                    text: ['string'],
                    number: ['integer','decimal'],
                };
                let result = '';
                for(x in typeOf){
                    if(typeOf[x].includes(type)){
                        result = x;
                        break;
                    }
                }
                result = result === '' ? type : result
                // console.log(type,result)
                return result;
            },
            parseDetailType: function(type){
                const typeOf = {
                    text: ['string','textarea'],
                    number: ['integer','decimal'],
                    options: ['select','checkboxes','radio'],
                    // datetime: ['date','time','datetime'],
                };
                let result = '';
                for(x in typeOf){
                    if(typeOf[x].includes(type)){
                        result = x;
                        break;
                    }
                }
                result = result === '' ? type : result
                // console.log(this.field_type,result)
                return result;
            },
            showFieldType: function(fieldSetting,formIndex,rowIndex){
                const field = this.input.field_setting[formIndex][rowIndex];
                const code = field.field_code;
                let originField;
                if(this.originData != null && this.originData.field_setting[formIndex] != undefined){
                    originField = this.originData.field_setting[formIndex].find(x => x.field_code == code);
                }
                // console.log(field);
                const allTypes = fieldSetting.field_options.options;
                const stringTypes = ['string','textarea','select','checkboxes','radio','file','reference','reference_page'];

                if(originField !== undefined && field.field_details.saved !== undefined && field.field_details.saved){
                    let result = stringTypes;
                    if(!stringTypes.includes(originField.field_type)){
                        result.push(originField.field_type);
                    }
                    return allTypes.filter(x => result.includes(x));
                }else{
                    return allTypes;
                }
            },
            checkRowIsEmpty(row) {
                // console.log(row);
                let status = true;
                for (let [key, field] of Object.entries(row)) {
                    const field_datas = this.field_fields[key];
                    // console.log(key,field_datas);
                    if(key != "form_id" && key != "field_order" && key != "field_options" && key != "translation" && key != "field_details" && key != "field_rule"){
                        if (field_datas.field_default_value == null) {
                            if (!(row[key] == null || row[key] == "" || row[key] == undefined || (row[key] === '0' && field_datas.field_type == 'boolean'))) status = false;
                        } else {
                            if (row[key] != field_datas.field_default_value) status = false;
                        }
                    }

                }
                return status;
            },
            getInitForm: function(formIndex){
                return {
                    form_id: null,
                    form_parent: Number(formIndex) === 0 ? null : 0
                };
            },
            getInitFields: function(formIndex = 0){
                const initFields = {};
                for(field of this.fieldResort(this.field_fields)){
                    let defaultValue = field.field_default_value;
                    // console.log(field);
                    if(field.field_code == "field_order"){
                        defaultValue = this.input.field_setting[formIndex].length-1;
                    }else if(field.field_code == "field_options"){
                        defaultValue = {};
                    }else if(field.field_code == "field_rule"){
                        defaultValue = [];
                    }else if(this.parseFieldType(field.field_type) == 'number'){
                        defaultValue = Number(defaultValue);
                    }else if(this.parseFieldType(field.field_type) == 'boolean'){
                        defaultValue = defaultValue === '1';
                    }
                    initFields[field.field_code] = defaultValue;
                }
                initFields.field_details = {
                    edited: false,
                };
                initFields.translation = {
                    default: {},
                    custom: {}
                };
                for(language of this.languages){
                    initFields.translation.default[language.language_id] = null;
                    initFields.translation.custom[language.language_id] = null;
                }
                const formId = this.input.form_setting[formIndex];
                initFields.form_id = formIndex;
                return initFields;
            },
            autoAddOrRemoveRow(formIndex) {
                let hasEmptyRow = false;
                if(this.input.form_setting[formIndex] === undefined){
                    this.input.form_setting.push(this.getInitForm(formIndex));
                }
                if(this.input.field_setting[formIndex] === undefined){
                    this.input.field_setting.push([]);
                }
                this.input.field_setting[formIndex].forEach(row => {
                    if (this.checkRowIsEmpty(row)) hasEmptyRow = true;
                });

                if (hasEmptyRow) {
                    this.input.field_setting[formIndex].forEach((row, rowIndex) => {
                        if (this.checkRowIsEmpty(row)) delete this.input.field_setting[formIndex][rowIndex];
                    });
                }
                if(this.config.mode != 'view'){
                    const initFields = this.getInitFields(formIndex);
                    this.input.field_setting[formIndex].push(initFields);
                }
                this.input.field_setting[formIndex] = this.input.field_setting[formIndex].filter(() => true);
                this.rowResort(formIndex);
                // console.log(this.input.field_setting);
            },
            formRemove(event, formIndex, toConfirm = true){
                event.preventDefault();
                formIndex = Number(formIndex);
                let canRemove = true;
                if(this.formSaved(formIndex)){
                    const msg = this.translations.cannot_remove_saved;
                    alert(msg.replace(":item",this.translations.form));
                    canRemove = false;
                }else if(toConfirm){
                    canRemove = confirm(this.translations["delete.confirm"]);
                }
                if(canRemove){
                    delete this.input.field_setting[formIndex];
                    this.input.field_setting = this.input.field_setting.filter(() => true);
                    const bodyNum = --this.input.page_setting.page_body_number;
                    if(bodyNum == 0){
                        this.input.page_setting.page_has_body = false;
                        this.input.page_setting.page_body_number = 1;
                    }
                    this.autoAddOrRemoveRow(this.input.field_setting.length);
                    console.log(`body${formIndex+1} has been removed!`);
                }
            },
            rowRemove(formIndex, rowIndex, toConfirm = true, removeFinal = false){
                formIndex = Number(formIndex);
                rowIndex = Number(rowIndex);

                let canRemove = true;
                if(this.input.field_setting[formIndex].length-1 == rowIndex){
                    this.input.field_setting[formIndex][rowIndex] = this.getInitFields(formIndex);
                    canRemove = removeFinal;
                }else if(this.fieldSaved(formIndex, rowIndex)){
                    const msg = this.translations.cannot_remove_saved;
                    alert(msg.replace(":item",this.translations.field));
                    canRemove = false;
                }else if(toConfirm){
                    canRemove = confirm(this.translations["delete.confirm"]);
                }
                if(canRemove){
                    if(this.input.field_setting[formIndex].length-1 > 0){
                        delete this.input.field_setting[formIndex][rowIndex];
                        this.input.field_setting[formIndex] = this.input.field_setting[formIndex].filter(() => true);
                        const formName = formIndex == 0 ? "head" : "body";
                        console.log(`row${rowIndex+1} of ${formName} has been removed!`);
                    }
                    this.rowResort(formIndex);
                }
            },
            rowResort(formIndex){
                allRow = this.input.field_setting[formIndex];
                allRow.sort(function(a,b){
                    return a.field_order - b.field_order;
                });
                for(i in allRow){
                    allRow[i].field_order = Number(i);
                }
                this.$forceUpdate();
            },
            fieldResort(fields){
                let tempArray = [];
                for(i in fields){
                    tempArray.push(fields[i]);
                }
                tempArray.sort((a,b) => {return a.field_order - b.field_order});
                return tempArray;
            },
            async loadingText(){
                let i = -1;
                fullscreenDimmer.loading();
                while(this.config.sending){
                    let temp = '';
                    const text = this.translations[this.config.status];
                    for(let j = 0; j <= i; j++){
                        temp += '.';
                    }
                    fullscreenDimmer.text(text+temp);
                    await this.delay(500);
                    i++;
                    if(i >= 3){
                        i = -1;
                    }
                }
                fullscreenDimmer.untext();
                fullscreenDimmer.unloading();
            },
            delay(t){
                return new Promise(resolve => {
                    setTimeout(resolve, t);
                });
            },
        },
    });

    ts('.tabbed.menu .item').tab();
    ts('.ts.checkbox').checkbox();

    document.querySelector("#content").style['overflow-y'] = 'auto';
</script>
@endsection
