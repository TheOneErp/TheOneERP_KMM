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
                <div class="ts form" v-if="showFilter">
                    <br>
                    <fieldset class="tertiary">
                        <legend>{{ $commonTranslations['filter'] }}</legend>
                        <div class="ts fields">
                            <div class="ts field">
                                <label>{{ $commonTranslations['field'] }} :</label>
                                <select class="ts basic dropdown" v-model="filterForm.field">
                                    <option v-for="option in filterForm.fieldOptions"  v-if="!(option.value == 'form_id')" :value="option.value">
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
                                    <select v-if='filterForm.field == "created_by"' v-model="filterForm.value">
                                        <option v-for="user in users" :value="user.user_id">@{{ user.name }}</option>
                                    </select>
                                    <page-selector v-else-if='filterForm.field == "page_id"' :items="pageTree" v-model="filterForm.value"></page-selector>
                                    <universal-field v-else v-model="filterForm.value"  :field="pageData.forms[0]['fields'][filterForm.field]" mode="search"></universal-field>
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
                                <th></th>
                                <th v-for="field in pageData.forms[0]['fields']" @click='handleSortClick(field)'
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
                                <td class="ts buttons" @click="viewRow(row)">
                                    <button class="ts icon  very compact small button"><i class="eye icon"></i></button>
                                </td>
                                <td v-for="field in pageData.forms[0]['fields']" v-if="field.field_show_on_list ">
                                    @{{row[field.field_code]}}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th v-for="field in pageData.forms[0]['fields']" v-if="field.field_show_on_list">
                                    @{{field.translation}}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <br>

                <!-- Pages -->
                {{$commonTranslations['data_per_page']}} :
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
        </div>

        <script type="text/x-template" id="page-selector">
            <div>
                <select v-model='selected' @change='() => {selectedItem = items.find(item => item.page_id == selected); $emit("input", selected)}' >
                    <option v-for="item in items" :key="item.page_id" :value="item.page_id">@{{ item.translation }}</option>
                </select>
                <page-selector :key='selected' v-if="selectedItem && selectedItem.subItems.length != 0" :items="selectedItem.subItems" @input="$emit('input', $event)"/>
            </div>
        </script>

        <div id="formVueElement"
            style="position: fixed; top: 0; left: 100%; width: 80%; height: 100%; background: white; transition: 1s; z-index: 2; padding: 2em; overflow: auto;">

            <div v-if="loading">
                <div class="ts active centered inline massive loader">
                    <div class="ts text loader">{{ $commonTranslations['loading'] }}</div>
                </div>
            </div>

            <h2 v-if='!loading && pageData'><button style="font-size:20px;" class="ts huge close button"
                    @click.prevent="closeForm"></button>&nbsp;@{{ pageData.page.translation }} - @{{logData.action}}
            </h2>

            <div v-if='!loading && pageData && ["1","2","3"].includes(logData.ids.action)'>
                <button class="ts primary button" @click.prevent="filterUpLevel">{{ $commonTranslations['log.search.parent'] }}</button>
                <button class="ts primary button" @click.prevent="filterChilds">{{ $commonTranslations['log.search.child'] }}</button>
                <button class="ts primary button" @click.prevent="filterThisData">{{ $commonTranslations['log.search.relation'] }}</button>
                <h2> {{ $commonTranslations['log'] }} : </h2>
                <div v-for="field in formData.fields" v-if="data[field['field_code']] && field.field_type !== 'button'">
                    <div class="ts input">
                        <div class="inline field">
                            <label>@{{ field.translation }} : </label>
                            <span v-if="field['field_type'] == 'reference_page'">
                                <span v-if="data[field['field_code']].constructor == Object">
                                    <button class="ts primary button"
                                        @click.prevent="filterReferencePage(field,data[field['field_code']]['old'])">{{ $commonTranslations['log.search.old'] }}</button>
                                    => <button class="ts primary button"
                                        @click.prevent="filterReferencePage(data[field['field_code']]['new'])">{{ $commonTranslations['log.search.new'] }}</button>
                                </span>
                                <span v-else>
                                    <button class="ts primary button"
                                        @click.prevent="filterReferencePage(field,data[field['field_code']])">{{ $commonTranslations['log.search.this'] }}</button>
                                </span>
                            </span>
                            <span v-else-if="data[field['field_code']].constructor == Object">
                                @{{data[field['field_code']]['old']}} -> @{{data[field['field_code']]['new']}}
                            </span>
                            <span v-else>
                                @{{ data[field['field_code']] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if='!loading && pageData && ["4","5","6","7"].includes(logData.ids.action)'>
                <h2> {{ $commonTranslations['log'] }} : </h2>
                {{-- <h3> {{ $commonTranslations["level"] }} : @{{ data["level"] }}</h3> --}}
                {{-- <h3> {{ $commonTranslations["verifier"] }} :</h3> --}}
                <div class="ts input">
                    <div class="inline field">
                        <label>{{ $commonTranslations["level"] }} : </label>
                        <span>
                            @{{ data["level"] }}
                        </span>
                    </div>
                </div>
                <div class="ts input">
                    <div class="inline field">
                        <label>{{ $commonTranslations["verifier"] }} : </label>
                        <span>
                            @{{ data["verifier"]["name"] }}
                        </span>
                    </div>
                </div>
                <div class="ts input">
                    <div class="inline field">
                        <label>{{ $commonTranslations["verify_at"] }} : </label>
                        <span>
                            @{{ data["verifier"]["verify_at"] }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('script')
@parent

<script>
    const pageData = @json($pageData);
    const pageDatas = @json($pages);
    const users = @json($users);

    Vue.component('page-selector',{
        template: "#page-selector",
        props:  ['items','value'],
        data(){
            return {
                selected : null,
                selectedItem : null
            }
        }
    })

    let buildPageTree = (pages,itemsArray,items) => {
        for(let page of pages){
            let pageObject = {
                page_id: page.page_id,
                page_module: page.page_module,
                translation: page.translation,
                order: page.page_order,
                subItems: []
            }
            itemsArray.push(pageObject)
            buildPageTree(items.filter(item => item.page_module == page.page_id),pageObject.subItems,items)
        }
        itemsArray.sort((a,b) => {
            if(a.subItems.length != 0 && b.subItems.length != 0){
                return a.order - b.order
            }else{
                return b.subItems.length - a.subItems.length
            }
        })
    }

    let pageTree = [];
    buildPageTree(pageDatas.filter(item => item.page_module == 0),pageTree,pageDatas);

    const listVue = new Vue({
        el: '#listVueElement',
        data() {
            let fieldOptions = [];
            for(let field of Object.values(pageData.forms[0].fields)){
                field.field_readonly = false
                if(!['button','reference_page'].includes(field.field_type) && (field.field_show_on_list || field.field_show_on_form))
                    fieldOptions.push({
                        value : field.field_code,
                        text : field.translation
                    })
            }

            return {
                pageData,
                pageDatas,
                pageTree,
                users,

                loading : true,
                showForm : false,
                showFilter: false,

                config : {
                    queryMode : true,
                },

                message:null,

                pagination: {},
                paginationCount: 10,
                filters : [],
                sortBy: [],

                filterForm : {
                    field:'',
                    operator:'',
                    value:'',
                    fieldOptions,
                    conditionOptions : window.defaultSelectOptions.conditionOptions,
                    operatorOptions : window.defaultSelectOptions.operatorOptions
                },

                data: []
            }
        },
        mounted() {
            this.queryData()
        },
        computed: {
            pages() {
                if (!this.pagination.to) {
                    return [];
                }
                let from = (this.pagination.current_page - 5) < 1 ? 1 : this.pagination.current_page - 5;
                let to = (from + 10) > this.pagination.last_page ? this.pagination.last_page : (from + 10) > this.pagination.last_page;
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
            viewRow(row){
                formVue.viewData(row)
                this.openForm()
            },

            // Filter
            addFilter(){
                const getOptionText = (options,value) => {
                    return (options.find(option => option.value == value).text)
                }
                if( this.filterForm.field != '' && this.filterForm.operator != '' && this.filterForm.value != '' && this.filterForm.group != ''){
                    let tmp = {
                        field : this.filterForm.field,
                        operator : this.filterForm.operator,
                        value : this.filterForm.value,
                        text : `${getOptionText(this.filterForm.fieldOptions,this.filterForm.field)} ${getOptionText(this.filterForm.operatorOptions,this.filterForm.operator)} ${this.filterForm.value}`
                    }

                    if(this.filterForm.field == 'page_id'){
                        tmp.text = `${getOptionText(this.filterForm.fieldOptions,this.filterForm.field)} ${getOptionText(this.filterForm.operatorOptions,this.filterForm.operator)} ${pageDatas.find(item => item.page_id == this.filterForm.value)['translation']}`
                    }

                    if(this.filterForm.field == 'created_by'){
                        tmp.text = `${getOptionText(this.filterForm.fieldOptions,this.filterForm.field)} ${getOptionText(this.filterForm.operatorOptions,this.filterForm.operator)} ${users.find(user => user.user_id == this.filterForm.value)['name']}`
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

    const formVue = new Vue({
        el: '#formVueElement',
        data() {
            return {
                loading: false,
                pageData : null,
                formData : null,
                data : null,
                logData : null
            }
        },
        mounted() {
        },
        methods:{
            async viewData(data){
                this.loading = true;

                let url = "{{ route('system.log.view',['logID' => '']) }}/" + data.log_id
                let response = await fetch(url).then(_ => _.json());

                this.pageData = response.pageData;
                this.formData = response.formData;
                this.data = response.data.data;
                this.logData = response.data;

                this.loading = false;
            },

            closeForm(){
                listVue.closeForm()
            },

            addFilter(field,operator,value){
                listVue.filterForm.field = field;
                listVue.filterForm.operator = operator;
                listVue.filterForm.value = value;
                listVue.addFilter()
            },
            filterChilds(){
                listVue.filters = [];
                this.addFilter('parent_id','=',this.logData.id)
                this.addFilter('page_id','=',this.logData.ids.page_id)
                this.pageData.forms.filter(form => form.form_parent == this.formData.form_id).forEach(form => this.addFilter('form_id','=',form.form_id))
                listVue.queryData();
            },
            filterUpLevel(){
                listVue.filters = [];
                this.addFilter('id','=',this.logData.parent_id)
                this.addFilter('page_id','=',this.logData.ids.page_id)
                this.pageData.forms.filter(form => form.form_id == this.formData.form_parent).forEach(form => this.addFilter('form_id','=',form.form_id))
                listVue.queryData();
            },
            filterThisData(){
                listVue.filters = [];
                this.addFilter('id','=',this.logData.id)
                this.addFilter('page_id','=',this.logData.ids.page_id)
                this.addFilter('form_id','=',this.formData.form_id)
                listVue.queryData();
            },
            filterReferencePage(field,id){
                listVue.filters = [];
                this.addFilter('id','=',id)
                this.addFilter('page_id','=',field['field_options']['reference_page']['page_id'])
                listVue.queryData();
            }
        }
    })
</script>

@endsection
