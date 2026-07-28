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

<style>
    button{
        cursor: pointer;
    }
    button:disabled{
        background-color: #b2b2b2;
    }
    .pimg{
        color: #02acbd;
    }
</style>
<!-- Table -->
<div style="width:100%;overflow:hidden">
    <div style="width:100%;overflow:hidden">
        <div id="listVueElement" style="width:100%;transition:1s;" v-cloak>

            <div class="ts grid">
                <div class="ten wide column">
                    <h3>{{ $pageData["page"]["translation"] }}<i v-if="refreshstatus=='true'" class="large video play icon pimg"></i><i v-if="refreshstatus=='false'" class="large pause icon pimg"></i></h3>
                </div>
                <div class="six wide column">
                    <button v-if="!loading" style="margin-right:1em;margin-bottom:0.2em;"
                        :class="{'ts primary active right floated button' : showFilter ,'ts primary right floated button' : !showFilter }"
                        @click="() => {showFilter = !showFilter}">{{ $commonTranslations['filter'] }}</button>
                    <button v-if="!loading" style="margin-right:1em;" class="ts primary right floated button"
                        @click="queryData">{{ $commonTranslations['query'] }}</button> 
                    <button v-if="!loading" style="margin-right:1em;" :disabled="refreshstatus=='true'" @click="refreshed('true')">自動更新</button>
                    <button v-if="!loading" style="margin-right:1em;" :disabled="refreshstatus=='false'" @click="refreshed('false')">暫停更新</button>
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

            <!-- Table -->
            <div style="width:100%;overflow:auto">
               <!-- Filter form -->
                <div class="ts form" v-if="showFilter">
                    <br>
                    <fieldset class="tertiary">
                        <legend>{{ $commonTranslations['filter'] }}</legend>
                        <div class="ts fields">
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
                                    <option v-for="option in filterForm.fieldOptions" v-if="option.value != 'finished_day' && option.value != 'ship_day'" :value="option.value">
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
                                        :field="pageOptions['headForm']['fields'][filterForm.field]" :options="{mode:'search'}" />
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
               
               <div class="ts grid">
                    <div class="ten wide column row">
                    
                        <div class="right aligned one wide column">
                            <span style="padding: 10px;background: #06bc37;border-radius: 70%/70%"></span>
                        </div>
                        <div class="two wide column">
                            <span>期限內數量達標</span>
                        </div>
                        <div class="right aligned one wide column">
                            <span style="padding: 10px;background: #1469dd;border-radius: 70%/70%"></span>
                        </div>
                        <div class="three wide column">
                            <span>期限內數量尚不足</span>
                        </div>
                        <div class="right aligned one wide column">
                            <span style="padding: 10px;background: #ffdb17;border-radius: 70%/70%"></span>
                        </div>
                        <div class="two wide column">
                            <span>期限後數量達標</span>
                        </div>
                        <div class="right aligned one wide column">
                            <span style="padding: 10px;background: #ff0045;border-radius: 70%/70%"></span>
                        </div>
                        <div class="three wide column">
                            <span>期限已過數量尚不足</span>
                        </div>
                    </div>
                </div>
                <table class="ts sortable single line selectable stackable very compact celled table" id="list">
                    <thead>
                        <tr>
                            <th v-for="field in pageOptions['headForm']['fields']" @click='handleSortClick(field)' v-if="field.field_show_on_list">
                                @{{field.translation}}
                                <i v-if="sortBy.findIndex(sortBy => sortBy.field == field.field_code) != -1" :class="{'chevron circle down icon' : sortBy.find(sortBy => sortBy.field == field.field_code).order == 'asc' , 'chevron circle up icon' : sortBy.find(sortBy => sortBy.field == field.field_code).order == 'desc'}">
                                    @{{ sortBy.findIndex(sortBy => sortBy.field == field.field_code) + 1 }}
                                </i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for='row in data'>
                            <template v-for="field in pageOptions['headForm']['fields']" v-if="field.field_show_on_list">
                                <td>
                                    <span :style="styleList(field.field_code,row.finished_light)" v-if="field.field_code == 'finishedbtn'"></span>
                                    <button v-if="field.field_code == 'finishedbtn'" :disabled="isdisabledfinish(row.finished_light,row.finished_day)" @click="exportbtn(row,field.field_code)">@{{field.translation}}</button>
                                    <span :style="styleList(field.field_code,row.ship_light)" v-if="field.field_code == 'shipbtn'"></span>
                                    <button v-if="field.field_code == 'shipbtn'" :disabled="isdisabledship(row.ship_light,row.ship_day,row.ship_backstatus)" @click="exportbtn(row,field.field_code)">@{{field.translation}}</button>
                                    @{{row[field.field_code]}}
                                </td>
                            </template>
                            
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
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
            
            let fieldOptions = [{
                value: '*',
                text: '{{ $commonTranslations['filter.all_field'] }}'
            }];
            
            for (let field of Object.values(pageOptions.headForm.fields)) {
                field.field_readonly = false
                if(
                    !['button','reference_page'].includes(field.field_type) &&
                    (field.field_show_on_list || field.field_show_on_form) &&
                    (field.field_type != "reference" || field.field_options.reference.type != "readonly" )
                )
                    fieldOptions.push({
                        value: field.field_code,
                        text: field.translation
                    })
            }

            return {
                loading: true,
                showForm: false,
                showFilter: false,

                config: {
                    queryMode: pageData.page.page_options.query_mode ? pageData.page.page_options.query_mode.enabled : false,
                    permission,

                },

                message: null,

                pagination: {},
                paginationCount: 10,
                filters: [],
                sortBy: [],

                filterForm: {
                    group: '0',
                    field: '*',
                    condition: 'or',
                    operator: 'like',
                    value: '',
                    fieldOptions,
                    conditionOptions: window.defaultSelectOptions.conditionOptions,
                    operatorOptions: window.defaultSelectOptions.operatorOptions
                },

                data: [],
                pageOptions,
                timer: 0,
                refreshstatus: 'true',
            }
        },
        mounted() {
            this.queryData() 
            this.refreshData(this.refreshstatus) 
            
        },
        destroyed(){    
            clearInterval(this.timer)  
        },
        computed: {
            pages() {
                if (!this.pagination.to) {
                    return [];
                }
                let from = (this.pagination.current_page - 5) < 1 ? 1 : this.pagination.current_page - 5;
                let to = this.pagination.last_page >  (from + 10)  ? from + 10 : this.pagination.last_page ;
                let pages = [];
                for (let page = from; page <= to; page++) {
                    pages.push(page);
                }
                return pages;
            },
            
        },
        methods: {
            refreshData(refreshstatus){
                if(refreshstatus == 'false'){      
                    clearInterval(this.timer)    
                }else{      
                    this.timer = setInterval(()=>{   
                        this.queryData()     
                    },60000)

                }  
            },
            
            changePage(page) {
                this.pagination.current_page = page
                this.queryData(page)
            },

            // Request
            queryData(page = null) {
                this.loading = true
                let baseURL = new URL('{{route('FA101_list',['page_id' => $pageData['page']['page_id']])}}');
                baseURL.searchParams.append('filters', JSON.stringify(this.filters))
                baseURL.searchParams.append('sortby', JSON.stringify(this.sortBy))
                baseURL.searchParams.append('paginationCount', this.paginationCount)
                if (!isNaN(page)) baseURL.searchParams.append('page', page)
                    sendAPIRequest(baseURL,"GET",null).then(result => {
                        this.pagination = result
                        this.data = result.data
                        this.loading = false
                    })
            },
            
            //按鈕是否可點
            isdisabledfinish(light,day) {
                if(light == '4' || light == '5'){
                    return false
                }else{
                    return true
                }
            },
            isdisabledship(light,day,backstatus) {
                if(light == '4' || light == '5'){
                    if(backstatus == true){
                        return true
                    }else{
                        return false
                    }
                }else{
                    
                }
            },
            
            styleList(btn,light){
                if(btn == 'finishedbtn'){
                 if(light == '2'){
                        return {padding: '10px',
                            background: '#06bc37',
                            'border-radius': '70%/70%',}
                    }else if(light == '3'){
                        return {padding: '10px',
                            background: '#ffdb17',
                            'border-radius': '70%/70%',}
                    }else if(light == '4'){
                        return {padding: '10px',
                            background: '#1469dd',
                            'border-radius': '70%/70%',}
                    }else if(light == '5'){
                        return {padding: '10px',
                            background: '#ff0045',
                            'border-radius': '70%/70%',}
                    }
                      }else if(btn == 'shipbtn'){
                    if(light == '2'){
                        return {padding: '10px',
                            background: '#06bc37',
                            'border-radius': '70%/70%',}
                    }else if(light == '3'){
                        return {padding: '10px',
                            background: '#ffdb17',
                            'border-radius': '70%/70%',}
                    }else if(light == '4'){
                        return {padding: '10px',
                            background: '#1469dd',
                            'border-radius': '70%/70%',}
                    }else if(light == '5'){
                        return {padding: '10px',
                            background: '#ff0045',
                            'border-radius': '70%/70%',}
                    }
                }
                
            },
            
            //轉出完工單、出貨單
            exportbtn(rowdata,btn) {
                if(btn == 'finishedbtn'){
                    localStorage.setItem("finsheddata",rowdata.no);
                    window.open(getURL("page/61/list#add"));
                }else if(btn == 'shipbtn'){
                    localStorage.setItem("shipdata",JSON.stringify(rowdata));
                   // console.log(localStorage);
                    window.open(getURL("page/59/list#add"));
                }
                
            },
            
            //是否自動刷新狀態
            refreshed(status) {
                if(status == 'true'){
//                    alert('已開啟自動更新')
                    this.refreshData('true')
                    return this.refreshstatus = 'true'
                }else{
//                    alert('已暫停自動更新')
                    this.refreshData('false')
                    return this.refreshstatus = 'false'
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
        },
        
    })

</script>

@yield('list_after_script')

@endsection
