@extends('layouts.default')
@section('title', $languages["page_name"])
@section('content')

<!-- Table -->
<div id="listVue" v-cloak>
    <h3 class="ts header">{{$languages["page_name"]}}</h3>
    {{-- Selector --}}
    <parent-child-selector
        :main-data="pageModule"
        :ignore-type="['page']"
        :translations="translations"
        :cancel-hidden="reorderMode.enabled"
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
            :class="{untooltipped: module_selected != -1}"
            @click="reorderEnable"
            :data-tooltip="translations['page_module.min']"
            data-tooltip-delay="disabled"
            data-tooltip-position="left"
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
            :class="{loading: reorderMode.saving, disabled: reorderMode.saving}"
            class="ts negative button"
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
                <th>@{{translations.module}}</th>
                <th>@{{translations.submodule}}</th>
                <th v-for="field in fields">
                    @{{field.translation}}
                </th>
            </tr>
        </thead>
        <tbody id="listItems">
            <tr v-for="page in show_page">
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
                <td>@{{page.mainModule}}</td>
                <td>@{{page.subModule}}</td>
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
                            :max="show_page.length"
                            v-model.lazy.number="pageModule.page[page.page_id].page_order"
                            v-number-limit-validate
                            @change="changeOrder"
                        >
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
            tempPageData: {},
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
            show_page: function(){
                let pageToShow = [];
                let show_modules = [];

                if(this.module_selected == -1){
                    this.reorderMode.enabled = false;
                    for(let x in this.pageModule.module){
                        show_modules.push(Number(x));
                    }
                    for(let x in this.pageModule.submodule){
                        show_modules.push(Number(x));
                    }
                }else{
                    const subModuleSelector = this.$refs.module_selector.$refs.submodule[0];
                    show_modules.push(this.module_selected);
                    if(!this.reorderMode.enabled){
                        if(subModuleSelector.itemSelected == -1){
                            show_modules = show_modules.concat(subModuleSelector.showItems);
                        }
                    }
                }

                const mainModule = this.pageModule.module;
                const subModule = this.pageModule.submodule;
                // console.log(mainModule,subModule)
                for(let x in this.pageModule.page){
                    const pageData = deepClone(this.pageModule.page[x]);
                    if(show_modules.includes(pageData.page_module)){
                        let findParent = pageData.page_module;
                        let pageSubModule = subModule[findParent];

                        if(pageSubModule != undefined){
                            pageData.subModule = pageSubModule.page_name;
                            findParent = pageSubModule.page_module;
                        }else{
                            pageData.subModule = null;
                        }
                        let pageMainModule = mainModule[findParent];
                        if(pageMainModule != undefined){
                            pageData.mainModule = pageMainModule.page_name;
                        }else{
                            pageData.mainModule = null;
                        }
                        pageData.page_id = x;
                        if(this.reorderMode.enabled){
                            if(pageData.page_visible){
                                pageToShow.push(pageData);
                            }
                        }else{
                            pageToShow.push(pageData);
                        }
                    }
                }

                pageToShow.sort(function(a,b){
                    if(a.page_module == b.page_module){
                        if(a.page_visible == b.page_visible){
                            return a.page_order - b.page_order;
                        }else if(a.page_visible){
                            return -1;
                        }else{
                            return 1;
                        }
                    }else{
                        return a.page_module - b.page_module;
                    }
                });
                if(this.reorderMode.enabled){
                    for(let x in pageToShow){
                        this.pageModule.page[pageToShow[x].page_id].page_order = x;
                    }
                }

                return pageToShow;
            },
        },
        methods: {
            forceUpdate: function(){
                this.$forceUpdate();
            },
            newPage: function(){
                location.href = getURL("pages/form/insert")
            },
            viewPage: function(pageData){
                location.href = getURL(`pages/form/view/${pageData.page_id}`);
            },
            editPage: function(pageData){
                if(!pageData.page_readonly){
                    location.href = getURL(`pages/form/update/${pageData.page_id}`);
                }
            },
            deletePage: function(pageData){},
            reorderEnable: function(){
                if(this.module_selected > -1){
                    this.tempPageData = deepClone(this.pageModule.page);
                    this.reorderMode.enabled = true;
                    this.reorderMode.dataChanged = false;
                }
            },
            reorderSave: function(){
                if(this.reorderMode.dataChanged){
                    sendAPIRequest(getURL('api/system/pages/savePageOrder'),"post",this.pageModule.page).then(result => {
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
                }

                this.reorderMode.enabled = false;
            },
            reorderCancel: function(){
                let toCancel = true;
                if(this.reorderMode.dataChanged){
                    toCancel = confirm(this.translations.unsave_confirm);
                }
                if(toCancel){
                    this.pageModule.page = deepClone(this.tempPageData);
                }
                this.reorderMode.enabled = !toCancel;
            },
            changeOrder: function(){
                this.reorderMode.dataChanged = true;
            },
            module_select: function(dropdownType, moduleSelected){
                if(this.reorderMode.enabled){
                    if(moduleSelected != -1){
                        this.module_selected = moduleSelected;
                    }else{
                        this.$refs.module_selector.inputValue(this.module_selected);
                    }
                }else{
                    this.module_selected = moduleSelected;
                }
            }
        }
    });
</script>
@endsection
