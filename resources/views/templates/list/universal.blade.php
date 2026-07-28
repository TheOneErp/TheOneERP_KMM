@extends('layouts.default')
@section('title', $pageData["page"]["translation"])
@includeIf('inject/'.$pageData['path'])

@section('content')

<!-- Notice Messages -->
@if(isset($errors) && ((is_object($errors) && count($errors->all()) > 0 ) || count($errors) > 0))
<div class="ts inverted icon negative message">
    <i class="remove circle icon"></i>
    <div class="header">
        @if(is_object($errors))
        @foreach($errors->all() as $key=>$message)
        {{$message}}<br>
        @endforeach
        @else
        {{$errors}}
        @endif
    </div>
</div>
@endif

@if(Session::has('success'))
<div class="ts inverted icon positive message">
    <i class="check circle icon"></i>
    <div class="header">
        {{Session::get('success')}}
    </div>
</div>
@endif

<!-- Table -->
<div style="width:100%;overflow:hidden">
    <div style="width:100%;overflow:hidden">
        <div id="listVueElement" style="width:100%;transition:1s;" v-cloak>

            <div class="ts grid">
                <div class="ten wide column">
                    <h3>{{ $pageData["page"]["translation"] }}</h3>
                </div>
                <div class="six wide column">
                    <button v-if="!loading" style="margin-right:1em;margin-bottom:0.2em;"
                        :class="{'ts primary active right floated button' : showFilter ,'ts primary right floated button' : !showFilter }"
                        @click="() => {showFilter = !showFilter}">{{ $commonTranslations['filter'] }}</button>
                        <!--<button v-if="!loading" style="margin-right:1em;margin-bottom:0.2em;"
                            :class="{'ts primary active right floated button' : report.show ,'ts primary right floated button' : !report.show }"
                            @click="() => {report.show = !report.show}">{{ $commonTranslations['report'] }}</button> -->
                    <button v-if="!loading" style="margin-right:1em;" class="ts primary right floated button"
                        @click="queryData">{{ $commonTranslations['query'] }}</button>
                </div>
            </div>

            @yield('list_before_list')

            <!-- Loading -->
            <div v-if="loading">
                <div class="ts active centered inline massive loader">
                    <div class="ts text loader">{{ $commonTranslations['loading'] }}</div>
                </div>
            </div>

            <div v-if="!loading">

                <div v-if='message != null' class="ts secondary inverted message"
                    :class="{'negative':message.type == 'error','positive' : message.type =='success'}">
                    <div class="header">@{{message.title}}</div>
                    <p>@{{message.text}}</p>
                </div>

                <!-- Filter form -->
                <div class="ts form" v-if="report.show">
                    <br>
                    <fieldset class="tertiary">
                        <legend>{{ $commonTranslations['report'] }}</legend>
                        <div class="ts fields" :class="{'glm flex direction column':showForm}">
                            <div class="ts field">
                                <label>{{ $commonTranslations['output_format'] }} : </label>
                                <select v-model="report.fileType">
                                    <option value="pdf">PDF</option>
                                    <option value="csv">CSV</option>
                                    <option value="docx">Word</option>
                                    <option value="xlsx">Excel</option>
                                    <option value="pptx">PowerPoint</option>
                                </select>
                            </div>
                            <div class="ts field">
                                <label>&nbsp;</label>
                                <div>
                                    <button class="ts small right floated primary button" :disabled="report.loading" :class="{'loading' : report.loading}"
                                        @click="downloadReport">{{ $commonTranslations['output'] }}</button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <br>
                </div>

                <!-- Filter form -->
                <div class="ts form" v-if="showFilter">
                    <br>
                    <fieldset class="tertiary">
                        <legend>{{ $commonTranslations['filter'] }}</legend>
                        <div class="ts fields" :class="{'glm flex direction column':showForm}">
                            <div class="ts field">
                                <label>{{ $commonTranslations['filter.group'] }} :</label>
                                <input type="number" v-model="filterForm.group" min=1 />
                            </div>
                            <div class="ts field">
                                <label>{{ $commonTranslations['filter.condition'] }} :</label>
                                <select class="ts basic dropdown" v-model="filterForm.condition">
                                    <option v-for="option in filterForm.conditionOptions" :value="option.value">
                                        @{{ option.text }}</option>
                                </select>
                            </div>
                            <div class="ts field">
                                <label>{{ $commonTranslations['field'] }} :</label>
                                <select class="ts basic dropdown" v-model="filterForm.field">
                                    <option v-for="option in filterForm.fieldOptions" :value="option.value">
                                        @{{ option.text }}</option>
                                </select>
                            </div>
                            <div class="ts field">
                                <label>{{ $commonTranslations['filter.operator'] }} :</label>
                                <select class="ts basic dropdown" v-model="filterForm.operator">
                                    <option v-for="option in filterForm.operatorOptions" :value="option.value">
                                        @{{ option.text }}</option>
                                </select>
                            </div>
                            <div class="ts field">
                                <label>{{ $commonTranslations['content'] }} :</label>
                                <div class="ts input">
                                    <universal-field v-model="filterForm.value"
                                        :field="pageOptions['headForm']['fields'][filterForm.field]"
                                        :options="{mode:'search'}" />
                                </div>
                            </div>
                            <div class="ts field">
                                <label>&nbsp;</label>
                                <div>
                                    <button class="ts small right floated primary button"
                                        @click="addFilter">{{ $commonTranslations['add'].$commonTranslations['filter'] }}</button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <br>
                </div>

                <!-- Filters -->
                <div v-if="filters.length > 0" class="ts stackable grid">
                    <div :class="{'inline four wide column':!showForm,'inline sixteen wide column':showForm}"
                        v-for="filter in filters">
                        <div class="ts primary card">
                            <div class="content">
                                <div class="description">
                                    <button class="ts close button" @click='deleteFilter(filter)'></button>
                                    <pre>@{{filter.text}}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                </div>

                <br>

                <!-- Table -->
                <div style="width:100%;overflow:auto">
                    <table class="ts sortable single line selectable stackable very compact celled table" id="list">
                        <thead>
                            <tr>
                                <th><a v-if='config.permission.permission_insert && !config.queryMode' @click="addRow"
                                        class="ts primary very compact labeled icon small button"><i
                                            class="add icon"></i>{{ $commonTranslations['add'] }}</a></th>
                                <th v-for="field in pageOptions['headForm']['fields']" @click='handleSortClick(field)'
                                    v-if="field.field_show_on_list">
                                    @{{field.translation}}
                                    <i v-if="sortBy.findIndex(sortBy => sortBy.field == field.field_code) != -1"
                                        :class="{'chevron circle down icon' : sortBy.find(sortBy => sortBy.field == field.field_code).order == 'asc' , 'chevron circle up icon' : sortBy.find(sortBy => sortBy.field == field.field_code).order == 'desc'}">
                                        @{{ sortBy.findIndex(sortBy => sortBy.field == field.field_code) + 1 }}
                                    </i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for='row in data'>
                                <td class="ts buttons">
                                    <button v-if='config.permission.permission_read' @click="viewRow(row)"
                                        class="ts icon  very compact small button"><i class="eye icon"></i></button>
                                    <button v-if='config.permission.permission_update && !config.queryMode'
                                        :disabled="row.data_options && row.data_options.editable != undefined && !row.data_options.editable"
                                        @click="editRow(row)" class="ts icon info very compact small button"><i
                                            class="pencil icon"></i></button>
                                    <button v-if='(config.permission.permission_insert && config.permission.permission_read) && !config.queryMode'
                                        :disabled="row.data_options && row.data_options.cloneable != undefined && !row.data_options.cloneable"
                                        @click="copyRow(row)" class="ts icon positive very compact small button"><i
                                            class="copy icon"></i></button>
                                    <button v-if='config.permission.permission_delete && !config.queryMode'
                                        :disabled="row.data_options && row.data_options.deletable != undefined && !row.data_options.deletable"
                                        @click="deleteRow(row)" class="ts icon negative very compact small button"><i
                                            class="delete icon"></i></button>
                                </td>
                                <td v-for="field in pageOptions['headForm']['fields']" v-if="field.field_show_on_list">
                                    @{{row[field.field_code]}}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th v-for="field in pageOptions['headForm']['fields']" v-if="field.field_show_on_list">
                                    @{{field.translation}}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <br>

                <!-- Pages -->
                顯示數量 :
                <select v-model='paginationCount' @change="queryData" class="ts basic small dropdown">
                    <option value=5>5</option>
                    <option value=10>10</option>
                    <option value=25>25</option>
                    <option value=50>50</option>
                    <option value=100>100</option>
                    <option value=200>200</option>
                </select>
                <div class="ts buttons">
                    <a v-if="pagination.current_page > 1" class="ts icon button"
                        @click="changePage(pagination.current_page - 1)"><i class="chevron left icon"></i></a>
                    <a v-for="page in pages" class="ts button" :class="{'active': page == pagination.current_page}"
                        @click="changePage(page)">@{{ page }}</a>
                    <a v-if="pagination.current_page < pagination.last_page" class="ts icon button"
                        @click="changePage(pagination.current_page + 1)"><i class="chevron right icon"></i></a>
                </div>
            </div>

            @yield('list_after_list')

        </div>
    </div>
</div>

@include($pageOptions['formView'])

@endsection

@section('script')
@parent

@yield('list_before_script')

<script>
    const pageData = @json($pageData);
    const pageOptions = @json($pageOptions);
    const permission = @json($permission);
    const dataKey = "{{$dataKey}}";

    const listVue = new Vue({
        el: '#listVueElement',
        data() {
            let fieldOptions = [{value : '*',text : '{{ $commonTranslations['filter.all_field'] }}'}];
            for(let field of Object.values(pageOptions.headForm.fields)){
                field.field_readonly = false
                if(
                    !['button','reference_page'].includes(field.field_type) &&
                    (field.field_show_on_list || field.field_show_on_form) &&
                    (field.field_type != "reference" || field.field_options.reference.type != "readonly" )
                )
                    fieldOptions.push({
                        value : field.field_code,
                        text : field.translation
                    })
            }

            return {
                loading : true,
                showForm : false,
                showFilter: false,

                report: {
                    loading : false,
                    show : false,
                    fileType : null
                },

                config : {
                    queryMode : pageData.page.page_options.query_mode ? pageData.page.page_options.query_mode.enabled : false,
                    permission
                },

                message:null,

                pagination: {},
                paginationCount: 10,
                filters : [],
                sortBy: [],

                filterForm : {
                    group:'0',
                    field:'*',
                    condition:'or',
                    operator:'like',
                    value:'',
                    fieldOptions,
                    conditionOptions : window.defaultSelectOptions.conditionOptions,
                    operatorOptions : window.defaultSelectOptions.operatorOptions
                },

                data: [],
                pageOptions,
            }
        },
        mounted() {
            this.queryData();
            function openHash(){
                if(formVue){
                    let hashID = location.hash.replace('#','').split('.')[1];
                    hashID = hashID == undefined ? '' : hashID
                    switch(location.hash.replace('#','').split('.')[0]){
                        case 'add':
                            listVue.addRow();
                            break;
                        case 'edit':
                            listVue.editRow(hashID);
                            break;
                        case 'copy':
                            listVue.copyRow(hashID);
                            break;
                        case 'view':
                            listVue.viewRow(hashID);
                            break;
                    }
                }
                else{
                    setTimeout(openHash,100)
                }
            }
            setTimeout(openHash,100)
        },
        computed: {
            pages() {
                if (!(this.pagination.to)) {
                    return [];
                }
                let from = (this.pagination.current_page - 5) < 1 ? 1 : this.pagination.current_page - 5;
                let to = this.pagination.last_page >  (from + 10)  ? from + 10 : this.pagination.last_page ;
                let pages = [];
                for (let page = from; page <= to; page++) {
                    pages.push(page);
                }
                return pages;
            }
        },
        methods:{

            changePage(page){
                this.pagination.current_page = page
                this.queryData(page)
            },
            reloadPage(){
                this.queryData(this.pagination.current_page)
            },

            // Request
            queryData(page = null) {
                this.loading = true
                let baseURL = new URL('{{route($routes['filter'],['page_id' => $pageData['page']['page_id']])}}');
                baseURL.searchParams.append('filters',JSON.stringify(this.filters))
                baseURL.searchParams.append('sortby',JSON.stringify(this.sortBy))
                baseURL.searchParams.append('paginationCount',this.paginationCount)
                if(!isNaN(page))baseURL.searchParams.append('page',page)
                sendAPIRequest(baseURL,"GET",null).then(response => {
                    this.pagination = response
                    this.data = response.data
                    this.loading = false
                })
            },

            // Form utils
            toggleForm(){
                document.getElementById("formVueElement").style.left = document.getElementById("formVueElement").style.left == '20%' ? '100%' : '20%';
                document.getElementById("formVueElement").style.boxShadow = document.getElementById("formVueElement").style.boxShadow == '0px 0px 10px 0px rgba(0,0,0,0.25)' ? '0px 0px 0px 0px rgba(0,0,0,0)' : '0px 0px 10px 0px rgba(0,0,0,0.25)';
                document.getElementById("listVueElement").style.width = document.getElementById("listVueElement").style.width == "18%" ? "100%" : "18%";
                this.showForm = document.getElementById("listVueElement").style.width == "18%"
            },
            openForm(){
                document.getElementById("formVueElement").style.left = '20%' ;
                document.getElementById("formVueElement").style.boxShadow = '0px 0px 10px 0px rgba(0,0,0,0.25)';
                document.getElementById("listVueElement").style.width = "18%";
                this.showForm = true
            },
            closeForm(){
                document.getElementById("formVueElement").style.left = '100%' ;
                document.getElementById("formVueElement").style.boxShadow = '0px 0px 0px 0px rgba(0,0,0,0)';
                document.getElementById("listVueElement").style.width = "100%";
                this.showForm = false
            },

            // Row methods
            addRow(){
                formVue.addData()
                this.toggleForm()
            },
            viewRow(row){
                let id = isNaN(row) ? row[dataKey] : row;
                formVue.viewData(id)
                this.openForm()
            },
            editRow(row){
                let id = isNaN(row) ? row[dataKey] : row;
                formVue.editData(id)
                this.openForm()
            },
            copyRow(row){
                let id = isNaN(row) ? row[dataKey] : row;
                formVue.copyData(id)
                this.openForm()
            },
            deleteRow(row){
                let url = "{{ route($routes['delete'],['page_id' => $pageData['page']['page_id'],'id' => '']) }}/" + row[dataKey]
                let accept = confirm("{{ $commonTranslations['delete.confirm'] }}")
                if(accept){
                    this.closeForm()
                    this.loading = true;
                    sendAPIRequest(url,"DELETE","")
                        .then(result => {
                            if(result.status){
                                this.queryData()
                            }else{
                                if(result.message)
                                    alert(result.message)
                                else
                                    alert("{{ $commonTranslations['delete.failed'] }}")
                                this.loading = false;
                            }
                        }).catch(err => {
                            alert("{{ $commonTranslations['error.unknown'] . $commonTranslations['contact_maintenance'] }}")
                            this.loading = false;
                        }
                    );
                }
            },

            // Filter
            addFilter(){
                const getOptionText = (options,value) => {
                    return (options.find(option => option.value == value).text)
                }
                if(this.filterForm.condition != '' && this.filterForm.field != '' && this.filterForm.operator != '' && this.filterForm.value != '' && this.filterForm.group != ''){
                    let tmp = {
                        group : this.filterForm.group,
                        condition : this.filterForm.condition,
                        field : this.filterForm.field,
                        operator : this.filterForm.operator,
                        value : this.filterForm.value,
                        text : `{{ $commonTranslations['filter.group'] }} : ${this.filterForm.group}\n${getOptionText(this.filterForm.conditionOptions,this.filterForm.condition)} ${getOptionText(this.filterForm.fieldOptions,this.filterForm.field)} ${getOptionText(this.filterForm.operatorOptions,this.filterForm.operator)} ${this.filterForm.value}`
                    }

                    if(this.filterForm.operator == "like" || this.filterForm.operator == "not like"){
                        tmp.value = `%${this.filterForm.value}%`;
                    }
                    this.filters.push(tmp)
                }else{
                    this.showMessage('error','{{ $commonTranslations['warning'] }}','{{ $commonTranslations['messages.fillOrSelectAll'] }}')
                }
            },
            deleteFilter(filter){
                delete this.filters[this.filters.findIndex(el => el.text == filter.text)]
                this.filters = this.filters.filter(() => true)
            },

            //Report
            downloadReport(){
                // this.report.loading = true
                if( this.report.fileType ){
                    fullscreenDimmer.loading()

                    let url = "{{ route($routes['report'],['id' => $pageData['page']['page_id']]) }}"

                    let formData = new FormData()
                    formData.append('filters',JSON.stringify(this.filters));
                    formData.append('format',this.report.fileType);

                    return sendAPIRequest(url,"POST",formData).then(async function (result){
                        if(result.status){
                            window.open(result.file);
                            // this.report.loading = false;
                            fullscreenDimmer.unloading()
                        }else{
                            alert("{{ $commonTranslations['error.unknown'] . $commonTranslations['contact_maintenance'] }}")
                            console.error(result);
                            // this.report.loading = false;
                            fullscreenDimmer.unloading()
                        }
                    }).catch(e => {
                        alert("{{ $commonTranslations['error.unknown'] . $commonTranslations['contact_maintenance'] }}")
                        console.error(e);
                        // this.report.loading = false;
                        fullscreenDimmer.unloading()
                    });
                }else{
                    alert("{{ $commonTranslations['please'] . $commonTranslations['selecting'] . $commonTranslations['output_format']  }}")
                }



            },

            // Utils
            showMessage(type,title,text){
                this.message = {
                    type,
                    title,
                    text
                }
                setTimeout(_ => {this.message = null},3000)
            },

            //Events
            handleSortClick(field){
                let sortByIndex = this.sortBy.findIndex(sortBy => sortBy.field == field.field_code)
                if(sortByIndex == -1){
                    sortByIndex = this.sortBy.push({field :field.field_code , order : "asc"})
                }else{
                    let sortBy = this.sortBy[sortByIndex]
                    if(sortBy.order == "asc"){
                        sortBy.order = "desc"
                    }else if(sortBy.order == 'desc'){
                        this.sortBy.splice(sortByIndex,1)
                        this.sortBy = this.sortBy.filter(() => true)
                    }
                }
                this.queryData()
            }
        }
    })

</script>

@yield('list_after_script')

@endsection
