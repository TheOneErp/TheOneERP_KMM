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
        input-id="module_selector"
        parent-key="page_module"
        item-name-key="page_name"
        key="module_selector"
        ref="module_selector"
        @any-change="module_select"
    ></parent-child-selector>
    {{-- Error Message --}}
    <div class="ts inverted icon negative message" v-if="errors.length > 0">
        <i class="remove circle icon"></i>
        <div class="content">
            <p v-for="error in errors">@{{error.message}}</p>
        </div>
    </div>
    {{-- List --}}
    <table class="ts selectable stackable celled table" id="list">
        <thead>
            <tr>
                <th></th>
                <th>@{{translations.module}}</th>
                <th>@{{translations.submodule}}</th>
                <th v-for="field in fields">
                    @{{field.translation}}
                </th>
            </tr>
        </thead>
        <tbody id="listItems">
            <tr v-for="page in show_page">
                <td class="glm list actions">
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
                    <div>
                        @{{page[field.field_code]}}
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- <div id="formVueElement"
    style="position: absolute; top: 0; left: 100%; width: 80%; height: 100%; background: white; transition: 1s; z-index: 2; padding: 2em; overflow: auto;">
    @include("system.form.verifies")
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
            errors: [],
        },
        mounted() {
            this.tempPageData = deepClone(this.pageModule.page);
        },
        computed: {
            show_page: function(){
                let pageToShow = [];
                let show_modules = [];

                if(this.module_selected == -1){
                    for(let x in this.pageModule.module){
                        show_modules.push(Number(x));
                    }
                    for(let x in this.pageModule.submodule){
                        show_modules.push(Number(x));
                    }
                }else{
                    const subModuleSelector = this.$refs.module_selector.$refs.submodule[0];
                    show_modules.push(this.module_selected);
                    if(subModuleSelector.itemSelected == -1){
                        show_modules = show_modules.concat(subModuleSelector.showItems);
                    }
                }

                const mainModule = this.pageModule.module;
                const subModule = this.pageModule.submodule;
                // console.log(mainModule,subModule)
                for(let x in this.pageModule.page){
                    const pageData = deepClone(this.pageModule.page[x]);
                    if(show_modules.includes(pageData.page_module) && !pageData.page_readonly){
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
                        pageToShow.push(pageData);
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

                return pageToShow;
            },
        },
        methods: {
            viewPage: function(pageData){
                location.href = getURL(`verifies/form/view/${pageData.page_id}`);
            },
            editPage: function(pageData){
                if(!pageData.page_readonly){
                    location.href = getURL(`verifies/form/update/${pageData.page_id}`);
                }
            },
            deletePage: function(pageData){
                if(!pageData.page_readonly){
                    let url = getURL(`verifies/delete/${pageData.page_id}`);
                    let accept = confirm("{{ $commonTranslations['delete.confirm'] }}")
                    if(accept){
                        fullscreenDimmer.loading();
                        sendAPIRequest(url,"DELETE","").then(result => {
                            if(result.status){
                                alert("{{ $commonTranslations['delete.successful'] }}")
                            }else{
                                if(result.message)
                                    alert(result.message);
                                else
                                    alert("{{ $commonTranslations['delete.failed'] }}");
                            }
                            fullscreenDimmer.unloading();
                        }).catch(err => {
                            alert("{{ $commonTranslations['error.unknown'] . $commonTranslations['contact_maintenance'] }}")
                            fullscreenDimmer.unloading();
                        });
                    }
                }
            },
            module_select: function(dropdownType, moduleSelected){
                this.module_selected = moduleSelected;
            }
        }
    });
</script>
@endsection
