@extends('layouts.default')
@section('title', $page_data["page"]["translation"])
@section('content')

<!-- Notice Messages -->
<div id="loading-dimmer" class="ts active dimmer">
    <div class="ts loader"></div>
</div>

<div id="formVue" v-cloak>
    <h3 class="ts header">@{{page_data.page.translation}}</h3>
    {{-- Error --}}
    <div class="ts inverted icon negative message" v-if="errors.length > 0">
        <i class="remove circle icon"></i>
        <div class="content">
            <p v-for="error in errors">@{{error.message}} <a v-if="error.link != null && error.link != undefined" class="link" id="#" @click="error.link">Click me</a></p>
        </div>
    </div>
    {{-- 分頁籤 --}}
    <div class="ts top attached tabbed menu">
        <a class="active item" data-tab="user_setting">@{{ translations.user_setting }}</a>
        <a v-if="config.mode != 'insert'" class="item" data-tab="agent_setting">@{{ translations.agent_setting }}</a>
    </div>
    {{-- 用戶設定 --}}
    <div class="ts active bottom attached tab segment" data-tab="user_setting">
        <form class="ts horizontal form" id="user_setting">
            {{-- Name --}}
            <div class="required field">
                <label>@{{page_fields.name.translation}}</label>
                <input
                    type="text"
                    v-model="input.user_setting.name"
                    v-bind="{id: page_fields.name.field_code, disabled: config.mode === 'view'}"
                >
            </div>
            {{-- Username --}}
            <div class="required field">
                <label>@{{page_fields.username.translation}}</label>
                <input
                    type="text"
                    v-model="input.user_setting.username"
                    v-bind="{id: page_fields.username.field_code, disabled: config.mode != 'insert'}"
                >
            </div>
            {{-- Password --}}
            <div
                v-if="config.mode != 'view'"
                class="field"
                :class="{required: config.mode === 'insert'}"
            >
                <label>@{{page_fields.password.translation}}</label>
                <input
                    type="password"
                    autocomplete="new-password"
                    v-model="input.user_setting.password"
                    v-bind="{id: page_fields.password.field_code, disabled: config.mode === 'view'}"
                >
            </div>
            <div
                v-if="config.mode != 'view'"
                class="field"
                :class="{
                    required: config.mode === 'insert' || !isEmpty(input.user_setting.password)
                }"
            >
                <label>@{{translations.password_confirmation}}</label>
                <input
                    type="password"
                    v-model="input.user_setting.password_confirmation"
                    v-bind="{id: 'password_confirmation', disabled: config.mode === 'view'}"
                >
            </div>
            {{-- Notification --}}
            <div class="field" v-for="f in notificationUser_fields">
                <label>@{{f.translation}}</label>
                <input
                    type="text"
                    v-model="input.notification_user_setting[f.field_code]"
                    v-bind="{id: f.field_code, disabled: config.mode === 'view'}"
                >
            </div>
            {{-- Disabled --}}
            <div class="field">
                <label>@{{page_fields.user_disabled.translation}}</label>
                <div>
                    <div class="ts toggle checkbox" :class="{disabled: config.mode=='view'}">
                        <input type="checkbox" :id="page_fields.user_disabled.field_code" v-model="input.user_setting.user_disabled">
                        <label :for="page_fields.user_disabled.field_code"></label>
                    </div>
                </div>
            </div>
            {{-- Remarks --}}
            <div class="field">
                <label>@{{page_fields.user_remarks.translation}}</label>
                <input
                    type="text"
                    v-model="input.user_setting.user_remarks"
                    v-bind="{
                        readOnly: isFieldReadOnly(page_fields.user_remarks),
                        id: page_fields.user_remarks.field_code,
                        disabled: config.mode=='view'
                    }"
                >
            </div>
        </form>
    </div>
    {{-- 代理人設定 --}}
    <div v-if="config.mode != 'insert'" class="ts bottom attached tab segment" data-tab="agent_setting">
        <form class="ts form" id="agent_setting">
            <div style="text-align: center;">
                <div
                    class="ts center aligned compact horizontal checkboxes"
                    style="margin-bottom: 1.75rem;"
                >
                    <div
                        class="ts toggle checkbox"
                        :class="{disabled: config.mode === 'view'}"
                    >
                        <input
                            type="checkbox"
                            id="user_agent_enabled"
                            v-model="input.user_agent_setting.user_agent_enabled"
                        >
                        <label for="user_agent_enabled">@{{agent_fields.user_agent_enabled.translation}}</label>
                    </div>
                </div>
                <div class="field" style="text-align: left;">
                    <label>@{{agent_fields.user_agent_enabled_at.translation}}</label>
                    <div class="two fields">
                        <div
                            class="inline field"
                            :class="{disabled: config.mode === 'view'}"
                        >
                            <input
                                id="user_agent_enabled_at"
                                type="text"
                                v-model="input.user_agent_setting.user_agent_enabled_at"
                            >
                        </div>
                        <div
                            class="inline field"
                            :class="{disabled: config.mode === 'view'}"
                        >
                            <label>～</label>
                            <input
                                id="user_agent_disabled_at"
                                type="text"
                                v-model="input.user_agent_setting.user_agent_disabled_at"
                            >
                        </div>
                    </div>
                </div>
                <parent-child-selector
                    v-if="input.user_agent_setting.pages.length > 0"
                    :main-data="pageModules"
                    :ignore-type="['page']"
                    :translations="translations"
                    {{-- :disabled="config.mode=='view'" --}}
                    input-id="module_selector"
                    parent-key="page_module"
                    item-name-key="page_name"
                    key="module_selector"
                    ref="module_selector"
                    @any-change="module_select"
                ></parent-child-selector>
                <table v-if="input.user_agent_setting.pages.length > 0" class="ts striped single line table">
                    <thead>
                        <tr>
                            <th>@{{translations.page}}</th>
                            <th>@{{agentPage_fields.user_agent_target_type.translation}}</th>
                            <th colspan="2">@{{agentPage_fields.user_agent_target_id.translation}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(page,pageIndex) in input.user_agent_setting.pages" :hidden="!page.page_show">
                            <td>@{{page.page_name}}</td>
                            <td {{-- style="width:20%" --}}>
                                <div
                                    class="ts field"
                                    :class="{disabled: config.mode === 'view'}"
                                >
                                    <select
                                        class="ts basic dropdown"
                                        :id="'user_agent_target_type'+pageIndex"
                                        v-model="page.user_agent_target_type"
                                        @change="targetTypeChange(pageIndex,page.user_agent_target_type)"
                                    >
                                        <option
                                            v-for="opt in agentPage_fields.user_agent_target_type.field_options.options"
                                            :value="opt"
                                        >
                                            @{{translations[opt]}}
                                        </option>
                                    </select>
                                </div>
                            </td>
                            <td {{-- style="width:20%" --}}>
                                <div
                                    class="ts floated field"
                                    :class="{disabled: isEmpty(page.user_agent_target_type) || config.mode === 'view'}"
                                >
                                    <universal-reference
                                        :field="agentPage_fields.user_agent_target_id"
                                        :ref="'user_agent_target_id'+pageIndex"
                                        :key="'user_agent_target_id'+pageIndex"
                                        :value="page.user_agent_target_id"
                                        :dataset="{form_id: agentPage_form_id, data:{'user_agent_target_type': page.user_agent_target_type}}"
                                        {{-- @input="$emit('input', $event);" --}}
                                        {{-- @change="$emit('change', $event)" --}}
                                        @reference="putReferenceData(pageIndex,$event)"
                                    />
                                </div>

                            </td>
                            <td {{-- style="width:30%" --}}>@{{page.user_agent_target_name}}</td>
                        </tr>
                    </tbody>
                </table>
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
    const notificationUser_data = @json($notificationUser_data);
    const agent_data = @json($agent_data);
    const agentPage_data = @json($agentPage_data);
    const translations = @json($translations);
    const pageModules = @json($pageModules);
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
            page_data,
            page_fields: page_data.forms[0].fields,
            notificationUser_data,
            notificationUser_fields: notificationUser_data.forms[0].fields,
            agent_data,
            agent_fields: agent_data.forms[0].fields,
            agentPage_data,
            agentPage_form_id: agentPage_data.forms[0].form_id,
            agentPage_fields: agentPage_data.forms[0].fields,
            translations,
            input:{
                user_setting:{
                    name: null,
                    username: null,
                    password: null,
                    password_confirmation: null,
                    user_disabled: false,
                    user_remarks: null
                },
                notification_user_setting:{
                    notification_user_phone: '',
                    notification_user_email: '',
                },
                user_agent_setting:{
                    user_agent_enabled: false,
                    user_agent_enabled_at: null,
                    user_agent_disabled_at: null,
                    pages: [],
                },
            },
            errors:[],
            agent_pages: {
                module_selected: -1,
            },
        },
        mounted() {
            if(this.config.mode != 'insert'){
                if(this.originData != null){
                    console.log(this.originData);
                    for(let x of ['user_setting', 'notification_user_setting', 'user_agent_setting']){
                        for(let y in this.originData[x]){
                            this.input[x][y] = this.originData[x][y];
                        }
                    }
                }
                if(this.input.user_agent_setting.user_agent_enabled === null) this.input.user_agent_setting.user_agent_enabled = false;

                const that = this;
                for(let field of ["user_agent_enabled_at","user_agent_disabled_at"]){
                    const el = document.querySelector(`#${field}`);
                    let pickerOptions = {
                        dateFormat: "Y-m-d H:i:00",
                        enableTime: true,
                        minDate: "today",
                        onOpen(selectedDates, dateStr, instance) {
                            if (that.config.mode === 'view') {
                                instance.destroy();
                                return
                            }
                            const value = that.input.user_agent_setting[field];
                            if (!isEmpty(value)) {
                                instance.setDate(value);
                            }
                        },
                        /* onChange(selectedDates, dateStr, instance) {
                            instance.close();
                        }, */
                        onReady() {
                            el.readOnly = true;
                            el.classList.add("flatpickr");
                        },
                    }/*
                    if(field == "user_agent_enabled_at"){
                        pickerOptions["minDate"] = "today";
                    } */
                    flatpickr(el, pickerOptions);
                }
            }

            for(let p in this.pageModules.page){
                const page = this.pageModules.page[p];
                const toPush = this.input.user_agent_setting.pages;
                if(toPush.find(x => x.page_id == p) === undefined){
                    toPush.push({
                        page_id: p,
                        page_name: page.page_name,
                        page_show: true,
                        user_agent_target_type: null,
                        user_agent_target_id: null,
                        user_agent_target_name: null,
                    });
                }
                const pageData = toPush.find(x => x.page_id == p);
                pageData.page_name = page.page_name;
                pageData.page_show = true;
            }
            loadingDimmer.toggle();
        },
        computed:{
            userInit(){
                return deepClone({
                    name: "",
                    username: "",
                    password: "",
                    password_confirmation: "",
                    user_disabled: false,
                    user_remarks: ""
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
                    let url = getURL(`users/save/${this.config.mode}`);
                    if(this.config.mode == 'update'){
                        url += `/${this.config.id}`;
                    }
                    this.config.status = "accessing";
                    sendAPIRequest(url,"post",inputClone).then(result => {
                        if(result.success){
                            this.config.status = "redirecting";
                            let redirect = getURL('users/list');
                            if(result.redirect != undefined) redirect = result.redirect;
                            document.location.href = redirect;
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
            isEmpty: function(value){
                return isEmpty(value);
            },
            isFieldReadOnly: function(field){
                let isReadonly = false;
                if(field.field_readonly){
                    if(field.field_options != null && field.field_options.readonly != undefined){
                        isReadonly = field.field_options.readonly == this.config.mode;
                    }else{
                        isReadonly = true;
                    }
                }
                return isReadonly;
            },
            getURL: function(URL){
                return getURL(URL);
            },
            module_select: function(moduleType, value){
                this.agent_pages.module_selected = value;
                this.show_agent_pages();
            },
            putReferenceData: function(pageIndex, data){
                const page = this.input.user_agent_setting.pages[pageIndex];
                page.user_agent_target_name = data.user_agent_target_name;
                page.user_agent_target_id = data.user_agent_target_id;
            },
            show_agent_pages: function(){
                let pageToShow = [];
                let show_modules = [];

                if(this.agent_pages.module_selected == -1){
                    for(let x in this.pageModules.module){
                        show_modules.push(Number(x));
                    }
                    for(let x in this.pageModules.submodule){
                        show_modules.push(Number(x));
                    }
                }else{
                    const subModuleSelector = this.$refs.module_selector.$refs.submodule[0];
                    show_modules.push(this.agent_pages.module_selected);
                    if(subModuleSelector.itemSelected == -1){
                        show_modules = show_modules.concat(subModuleSelector.showItems);
                    }
                }

                const mainModule = this.pageModules.module;
                const subModule = this.pageModules.submodule;
                // console.log(mainModule,subModule)
                for(let x in this.pageModules.page){
                    const pageData = deepClone(this.pageModules.page[x]);
                    if(show_modules.includes(pageData.page_module)){
                        const toShow = this.input.user_agent_setting.pages.find(y => y.page_id == x);
                        if(toShow != undefined){
                            toShow.page_show = true;
                        }
                    }else{
                        const toHidden = this.input.user_agent_setting.pages.find(y => y.page_id == x);
                        if(toHidden != undefined){
                            toHidden.page_show = false;
                        }
                    }
                }
                this.$forceUpdate();
            },
            targetTypeChange: function(pageIndex, value){
                const page = this.input.user_agent_setting.pages[pageIndex];

                page.user_agent_target_name = null;
                page.user_agent_target_id = null;
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
