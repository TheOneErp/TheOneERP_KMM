@extends('layouts.default')
@section('title', $languages["page_name"])
@section('content')

<!-- Table -->
<div id="listVue" v-cloak>
    <h3 class="ts header">{{$languages["page_name"]}}</h3>
    {{-- Selector --}}
    <parent-child-selector
        :main-data="pageModule"
        :ignore-type="['submodule','page']"
        :translations="translations"
        input-id="module_selector"
        parent-key="page_module"
        item-name-key="page_name"
        key="module_selector"
        ref="module_selector"
        @any-change="module_select"
    ></parent-child-selector>
    {{-- Order Button --}}
    <span v-if="!reorderMode.enabled" style="position: absolute; right: 0px;">
        <button
            class="ts info button"
            @click="reorderEnable"
        >@{{translations.edit_order}}</button>
    </span>
    {{-- Save & Cancel Button --}}
    <span v-else-if="reorderMode.enabled" style="position: absolute; right: 0px;">
        <button
            class="ts positive button"
            :class="{loading: reorderMode.saving, disabled: reorderMode.saving}"
            style="margin-right: 0.5rem"
            @click="reorderSave"
        >@{{translations.save}}</button>
        <button
            class="ts negative button"
            :class="{loading: reorderMode.saving, disabled: reorderMode.saving}"
            @click="reorderCancel"
        >@{{translations.cancel}}</button>
    </span>
    {{-- Success Message --}}
    <div class="ts inverted icon positive message" v-if="reorderMode.success">
        <i class="checkmark circle icon"></i>
        <div class="content">
            <p>@{{translations.save_success}}</p>
        </div>
    </div>
    {{-- Error Message --}}
    <div class="ts inverted icon negative message" v-else-if="reorderMode.errors.length > 0">
        <i class="remove circle icon"></i>
        <div class="content">
            <p v-for="error in errors">@{{error.message}}</p>
        </div>
    </div>
    {{-- List --}}
    <table class="ts selectable stackable celled table" id="list">
        <thead>
            <tr>
                <th v-if="!reorderMode.enabled">
                    <button
                        class="ts primary very compact labeled icon small button"
                        id="add-button"
                        @click="newPage"
                    >
                        <i class="add icon"></i>@{{translations.new}}
                    </button>
                </th>
                {{-- <th>@{{translations.module}}</th>
                <th>@{{translations.submodule}}</th> --}}
                <th v-for="field in fields">
                    @{{field.translation}}
                </th>
            </tr>
        </thead>
        <tbody id="listItems">
            <tr v-for="page in show_module">
                <td class="glm list actions"  v-if="!reorderMode.enabled">
                    <div class="ts buttons">
                        <button
                            class="ts icon very compact small button"
                            @click="viewPage(page)"
                        >
                            <i class="eye icon"></i>
                        </button>
                        <button
                            class="ts icon info very compact small button"
                            :class="{disabled:page.page_readonly}"
                            @click="editPage(page)"
                        >
                            <i class="pencil icon"></i>
                        </button>
                        <button
                            class="ts icon negative very compact small button"
                            :class="{disabled:page.page_readonly}"
                            @click="deletePage(page)"
                        >
                            <i class="delete icon"></i>
                        </button>
                    </div>
                </td>
                {{-- <td>@{{page.mainModule}}</td>
                <td>@{{page.subModule}}</td> --}}
                <td v-for="field in fields">
                    <div v-if="field.field_type == 'boolean'">
                        @{{page[field.field_code] ? translations.yes : translations.no}}
                    </div>
                    <div
                        v-else-if="field.field_code == 'page_order' && reorderMode.enabled"
                        class="ts input"
                    >
                        <input
                            type="number"
                            :min="-1"
                            :max="show_module.length"
                            v-model.lazy.number="pageModule[page.type][page.page_id].page_order"
                            v-number-limit-validate
                            @change="changeOrder"
                        >
                    </div>
                    <div v-else-if="field.field_code == 'type'">
                        @{{translations[page.type]}}
                    </div>
                    <div v-else>
                        @{{page[field.field_code]}}
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- <div id="formVueElement"
    style="position: absolute; top: 0; left: 100%; width: 80%; height: 100%; background: white; transition: 1s; z-index: 2; padding: 2em; overflow: auto;">
    @include("system.form.pages")
</div> --}}

@endsection
@section('script')
@parent
<script>
    const fields = @json($fields);
    const pageModule = @json($pageModule);
    const translations = @json($languages);

    let listVue = new Vue({
        el: "#listVue",
        data: {
            fields,
            pageModule,
            tempModuleData: {},
            translations,
            module_selected: -1,
            reorderMode: {
                enabled: false,
                dataChanged: false,
                saving: false,
                success: false,
                errors: [],
            },
        },
        mounted() {
            this.tempPageData = deepClone(this.pageModule.page);
        },
        computed: {
            show_module: function(){
                let moduleToShow = [];
                let show_modules = [];

                if(this.module_selected == -1){
                    for(let x in this.pageModule.module){
                        show_modules.push(Number(x));
                    }
                }else{
                    show_modules.push(this.module_selected);
                }

                const mainModule = deepClone(this.pageModule.module);
                const subModule = deepClone(this.pageModule.submodule);
                let mainModuleArray = [];
                let subModuleArray = [];
                // console.log(mainModule,subModule)
                for(let x in mainModule){
                    mainModule[x].page_id = Number(x);
                    mainModule[x].type = 'module';
                    mainModuleArray.push(mainModule[x]);
                }
                for(let x in subModule){
                    subModule[x].page_id = Number(x);
                    subModule[x].type = 'submodule';
                    subModuleArray.push(subModule[x]);
                }
                mainModuleArray.sort((a,b) => a.page_order - b.page_order);
                for(let i in mainModuleArray){
                    if(show_modules.includes(mainModuleArray[i].page_id)){
                        if(this.module_selected == -1){
                            this.pageModule.module[mainModuleArray[i].page_id].page_order = i;
                            moduleToShow.push(mainModuleArray[i]);
                        }
                        if(this.module_selected != -1 || !this.reorderMode.enabled){
                            subOfMain = subModuleArray.filter(x => x.page_module == mainModuleArray[i].page_id).sort((a,b) => a.page_order - b.page_order);
                            for(let j in subOfMain){
                                this.pageModule.submodule[subOfMain[j].page_id].page_order = j;
                                moduleToShow.push(subOfMain[j]);
                            }
                        }
                    }
                }
                // console.log(moduleToShow);

                return moduleToShow;
            },
        },
        methods: {
            forceUpdate: function(){
                this.$forceUpdate();
            },
            newPage: function(){
                location.href = getURL("modules/form/insert")
            },
            viewPage: function(pageData){
                location.href = getURL(`modules/form/view/${pageData.page_id}`);
            },
            editPage: function(pageData){
                if(!pageData.page_readonly){
                    location.href = getURL(`modules/form/update/${pageData.page_id}`);
                }
            },
            deletePage: function(pageData){},
            reorderEnable: function(){
                this.tempModuleData = deepClone(this.pageModule);
                this.reorderMode.enabled = true;
                this.reorderMode.dataChanged = false;
            },
            reorderSave: function(){
                if(this.reorderMode.dataChanged){
                    let toSave = {};
                    for(let x of ['module', 'submodule']){
                        for(let y in this.pageModule[x]){
                            toSave[y] = this.pageModule[x][y];
                        }
                    }
                    // console.log(toSave);
                    this.reorderMode.saving = true;
                    sendAPIRequest(getURL('api/system/pages/savePageOrder'),"post",toSave).then(result => {
                        if(result.success){
                            this.reorderMode.success = true;
                        }
                    }).then(() => {
                        setTimeout(() => {
                            this.reorderMode.success = false;
                        },2500);
                        this.reorderMode.enabled = false;
                        this.reorderMode.saving = false;
                    });
                }else{
                    this.reorderMode.enabled = false;
                }
            },
            reorderCancel: function(){
                let toCancel = true;
                if(this.reorderMode.dataChanged){
                    toCancel = confirm(this.translations.unsave_confirm);
                }
                if(toCancel){
                    this.pageModule = deepClone(this.tempModuleData);
                }
                this.reorderMode.enabled = !toCancel;
            },
            changeOrder: function(){
                this.reorderMode.dataChanged = true;
            },
            module_select: function(dropdownType, moduleSelected){
                this.module_selected = moduleSelected;
            }
        }
    });
</script>
@endsection
