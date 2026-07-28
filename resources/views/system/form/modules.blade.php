@extends('layouts.default')
@section('title', $module_data["page"]["translation"])
@section('content')

<!-- Notice Messages -->
<div id="loading-dimmer" class="ts active dimmer">
    <div class="ts loader"></div>
</div>

<div id="formVue" v-cloak>
    <h3 class="ts header">@{{module_data.page.translation}}</h3>
    {{-- Error --}}
    <div class="ts inverted icon negative message" v-if="errors.length > 0">
        <i class="remove circle icon"></i>
        <div class="content">
            <p v-for="error in errors">@{{error.message}} <a v-if="error.link != null && error.link != undefined" class="link" id="#" @click="error.link">Click me</a></p>
        </div>
    </div>
    {{-- 分頁籤 --}}
    <div class="ts top attached tabbed menu">
        <a class="active item" data-tab="module_setting">@{{ translations.module_setting }}</a>
        <a class="item" data-tab="translation_setting">@{{ translations.SY_TRANSLATION }}</a>
    </div>
    {{-- 頁面設定 --}}
    <div class="ts active bottom attached tab segment" data-tab="module_setting">
        <form class="ts horizontal form" id="module_setting">
            {{-- Code --}}
            <div class="required field" {{-- :class="{disabled: config.mode == 'update'}" --}}>
                <label>@{{page_fields.page_code.translation}}</label>
                <input
                    type="text"
                    v-model="input.module_setting.page_code"
                    v-bind="{id: page_fields.page_code.field_code, disabled: config.mode != 'insert'}"
                >
            </div>
            {{-- Module --}}
            <div class="required field">
                <label>@{{page_fields.page_module.translation}}</label>
                <parent-child-selector
                    :main-data="pageModules"
                    :ignore-type="['submodule','page']"
                    :translations="translations"
                    :disabled="config.mode == 'view'"
                    input-id="page_module"
                    parent-key="page_module"
                    item-name-key="page_name"
                    key="page_module"
                    ref="page_module"
                    {{-- v-model="input.module_setting.page_module" --}}
                    @any-change="putPageModule"
                    @cancel="moduleCancel"
                ></parent-child-selector>
            </div>
            {{-- Visible, Readonly --}}
            <div class="field" v-for="item in ['page_visible'{{-- , 'page_readonly' --}}]">
                <label>@{{page_fields[item].translation}}</label>
                <div>
                    <div class="ts toggle checkbox" :class="{disabled: config.mode=='view'}">
                        <input type="checkbox" :id="page_fields[item].field_code" v-model="input.module_setting[item]">
                        <label :for="page_fields[item].field_code"></label>
                    </div>
                </div>
            </div>
            {{-- Remarks --}}
            <div class="field" :class="isFieldRequired(page_fields.page_remarks)">
                <label>@{{page_fields.page_remarks.translation}}</label>
                <input
                    type="text"
                    v-model="input.module_setting.page_remarks"
                    v-bind="{
                        readOnly: isFieldReadOnly(page_fields.page_remarks),
                        id: page_fields.page_remarks.field_code,
                        disabled: config.mode=='view'
                    }"
                >
            </div>
        </form>
    </div>
    {{-- 翻譯設定 --}}
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
    {{-- 儲存按鈕 --}}
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
    const module_data = @json($module_data);
    const translations = @json($translations);
    const languages = @json($languages);
    const pageModules = @json($modules);
    const pageType = "{{$type}}";
    const dataId = @json($dataId);

    @if(isset($data))
    let originData = @json($data);
    @else
    let originData = null;
    @endif

    let loadingDimmer = new Vue({
        el:'#loading-dimmer',
        methods: {
            toggle: function(){
                const el = document.querySelector("#loading-dimmer");
                el.classList.toggle("active",!el.classList.contains("active"));
            }
        },
    });

    let formVue = new Vue({
        el: '#formVue',
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
            page_fields: page_data.forms[0].fields,
            module_data,
            languages,
            translations,
            input:{
                module_setting:{
                    page_code: "",
                    page_module: 0,
                    page_visible: true,
                    page_readonly: false,
                    page_options: {},
                    page_remarks: null,
                },
                translation_setting:{},
            },
            errors:[],
        },
        mounted() {
            if(this.config.mode != 'insert'){
                if(this.originData != null){
                    console.log(this.originData);
                    this.input.module_setting = deepClone(this.originData.module_setting);
                    if(this.input.module_setting.page_module > 0){
                        this.$refs.page_module.inputValue(this.input.module_setting.page_module);
                    }
                    for(let i in this.originData.translation_setting){
                        const temp = this.originData.translation_setting[i];
                        if(temp != null && temp != ""){
                            this.input.translation_setting[i] = temp;
                        }
                    }
                }
            }
            loadingDimmer.toggle();
        },
        computed:{
            pageInit(){
                return deepClone({
                    page_code: "",
                    page_module: 0,
                    page_visible: true,
                    page_readonly: false,
                    page_options: {},
                    page_remarks: null,
                });
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
                    let url = getURL(`modules/save/${this.config.mode}`);
                    if(this.config.mode == 'update'){
                        url += `/${this.config.id}`;
                    }
                    this.config.status = "accessing";
                    sendAPIRequest(url,"post",inputClone).then(result => {
                        if(result.success){
                            this.config.status = "redirecting";
                            document.location.href = getURL('modules/list');
                        }else{
                            console.log(result);
                            let validationErrors = [];
                            if(result.errors != undefined && result.errors.length > 0){
                                for(let error of result.errors){
                                    let link = function(){formVue.focusError(error.tab, error.id)};
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
                // fullscreenDimmer.unloading();
                this.config.sending = false;
                for(let e of errors){
                    this.errors.push(e);
                }
                document.querySelector("#content").scrollTop = 0;
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
                this.input.module_setting.page_module = value;
            },
            moduleCancel: function(){
                this.input.module_setting.page_module = 0;
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
            getURL: function(URL){
                return getURL(URL);
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
