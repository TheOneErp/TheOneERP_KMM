@includeIf('inject/'.str_replace('\\','/',$pageData['path']))

<style>
    #formVueElement {
        position: fixed;
        top: 0;
        left: 100%;
        width: 80%;
        height: 100%;
        background: white;
        transition: 1s;
        z-index: 2;
        padding: 2em;
        overflow: auto;
    }

    .glm.bodyForms {
        width: 100%;
    }

    /* table */
    .glm.table.div {
        overflow: auto;
        max-height: 400px;
        margin-top: 0 !important;
    }

    th.glm.table.header {
        z-index: 3;
        position: sticky !important;
        top: 0;
    }

    .glm.table.rows tr td:first-child {
        z-index: 2;
        background: #fff;
        position: sticky !important;
        left: 0;
    }
</style>

<div id="formVueElement">
    <universal-form :page-data="pageData" ref='form' />
</div>

<script type="text/x-template" id="universal-form">
    <div v-if="status.renderComponent" v-cloak>

        <universal-form style="display:none" v-for="pageData in config.referencePages" :key="pageData.page.page_id" :pageData="pageData" :ref="'referencePage_' + pageData.page.page_id" />

        <div v-if="status.loading">
            <div class="ts active centered inline massive text loader">
                {{ $commonTranslations['loading'] }}
                <div class="ts text loader"></div>
            </div>
        </div>

        <div v-if="!status.loading">
            <!-- Title -->
            <h2><button style="font-size:20px;" class="ts huge close button"
                     @click.prevent="closeForm"></button>&nbsp;@{{ config.pageData.page.translation }}@{{ config.modeText }}</h2>

            <div class="ts message" v-if="messages.length > 0">
                <div class="content">
                    <div class="ts relaxed divided list">
                        <div class="item" v-for="message in messages">@{{message}}</div>
                    </div>
                </div>
            </div>

            <div class="ts inverted icon negative message" ref="errors" v-if="errors.length > 0">
                <i class="remove circle icon"></i>
                <div class="content">
                    <div class="ts relaxed divided selection list">
                        <div class="negative inverted item" v-for="error in errors" @click="focusError(error.tmpID)">
                            @{{error.text}}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forms -->
            @yield('form_before_head')
            <form class="ts form">
                <div class="ts stackable grid">
                    <div v-for="field in config.headForm.fields"
                        :key="'head' + field.field_id"
                        v-if="field.field_show_on_form"
                        :class="getFieldClass(field,config.headForm)">
                        <div class="ts input">
                            <div class="inline field">
                                <label v-if="field.field_type !== 'button'">@{{ field.translation }} : </label>
                                <universal-field
                                    v-model="formDatasets[config.headForm.form_id]['data'][field.field_code]"

                                    :field="field"
                                    :dataset='formDatasets[config.headForm.form_id]'
                                    :options="config.fieldOptions"
                                    :override="formDatasets[config.headForm.form_id].override.fields[field.field_code]"
                                    :rootDataset="dataset"

                                    @input="onHeadInput(field,formDatasets[config.headForm.form_id])"
                                    @change="onHeadChange(field,formDatasets[config.headForm.form_id])"
                                    @click="onHeadClick(field,formDatasets[config.headForm.form_id])"
                                    @file="onFileSelected(formDatasets[config.headForm.form_id],field,$event)"
                                    @reference="(data,fields) => writeReferenceData(field,data,fields,formDatasets[config.headForm.form_id])" />
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            @yield('form_after_head')

            @yield('form_before_body')
            <form v-for="form in config.bodyForms" :key="'body_'+form.form_id" v-if="formDatasets[form.form_id]" class="ts form glm bodyForms">
                <br />
                <div class="glm table div" @scroll="onScroll">
                    <table class="ts single line compact celled table">
                        <thead>
                            <tr>
                                <th class="glm table header">#</th>
                                <th class="glm table header" v-for="field in form.fields" :key="'body_'+form.form_id+'_'+field.field_id" v-if="field.field_show_on_form">
                                    @{{ field.translation }}
                                </th>
                                <th class="glm table header" v-if="!config.mode.includes('view')">{{ $commonTranslations['delete'] }}</th>
                            </tr>
                        </thead>
                        <tbody class="glm table rows">
                            <tr v-for="(row, rowIndex) in formDatasets[form.form_id]['subData'][form.form_id]"
                                v-if="row"

                                :key="row.tmpID"
                                :ref="'row'"

                                :style="{ visibility : config.showRows[row.tmpID] ? '' : 'hidden' }"
                                class="glm selectable"

                                :class="row.selected ? 'colSelected' : '' "
                                :tmpID="row.tmpID">
                                <td @click="onRowClicked(formDatasets[form.form_id],row,form.form_id)">@{{ rowIndex + 1 }}</td>
                                <td v-for="(field,index) in form.fields" :key="row.tmpID + '_' + field.field_id" v-if="field.field_show_on_form && config.showRows[row.tmpID]"
                                    @click="_ => { onRowClicked(formDatasets[form.form_id],row,form.form_id) ; onColClick(formDatasets[form.form_id],row,field,form.form_id) } "
                                    :ref="config.showRows[row.tmpID] ? 'fields' : null "
                                    :refid="row.tmpID+field.field_code"
                                    >
                                    <div class="ts fluid compact input">
                                        <universal-field
                                            v-model="row.data[field.field_code]"

                                            v-if="config.showFields[row.tmpID+field.field_code]"

                                            :field="field"
                                            :dataset='row'
                                            :options="config.fieldOptions"
                                            :override="row.override.fields[field.field_code]"
                                            :rootDataset="dataset"

                                            @input="onColInput(formDatasets[form.form_id],row,field,form.form_id)"
                                            @change="onColChange(formDatasets[form.form_id],row,field,form.form_id)"
                                            @file="onFileSelected(row,field,$event)"
                                            @reference="(data,fields) => writeReferenceData(field,data,fields,row)" />
                                    </div>
                                </td>
                                <td v-if="!config.mode.includes('view')">
                                    <button type="button" class="ts negative button" v-if="!row.override.preventDelete"
                                        @click.prevent="onRowDeleteButtonClicked(formDatasets[form.form_id],rowIndex,form.form_id)">
                                        {{ $commonTranslations['delete'] }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
            @yield('form_after_body')

            <br />
            <button v-if="!config.mode.includes('view') && pageData.page.page_options.savable " @click.prevent="save" class="ts positive button" :disabled="status.sending"
                :class="{loading : status.sending}">
                {{ $commonTranslations['save'] }}
            </button>

            <!-- Verify -->
            <div v-if="dataset.verify">
                <button v-if="config.mode.includes('view') && dataset.verify.verifyStart" @click.prevent="verify('execute')" class="ts positive button" :disabled="status.sending"
                    :class="{loading : status.sending}">
                    {{ $commonTranslations['verify.start'] }}
                </button>
                <span v-if="config.mode.includes('view') && dataset.verify.canDoVerify">
                    <button @click.prevent="verify('execute')" class="ts positive button">{{ $commonTranslations['verify.confirm'] }}</button>
                </span>
                <span v-if="config.mode.includes('view') && dataset.verify.canInitAndReturn">
                    <button @click.prevent="verify('return')" class="ts inverted button">{{ $commonTranslations['verify.return'] }}</button>
                    <button @click.prevent="verify('init')" class="ts negative button">{{ $commonTranslations['verify.init'] }}</button>
                </span>
            </div>

        </div>
    </div>
</script>
@section('script')
@parent

@yield('form_before_script')

<!-- Form script -->
<script>
    //TODO: Number Step
    //TODO: Reference page check empty

    let tmpID = 0;
    Vue.component('universal-form',{
        template: "#universal-form",
        props:['pageData'],
        data() {
            let that = this
            this.returnVue = () => {
                return that
            }
            return {
                status : {
                    sending : false,
                    loading: true,
                    renderComponent:true
                },

                config: {
                    mode: "add",
                    modeText: "",

                    fieldOptions: {},
                    formOptions: {},

                    onScrollStatus: 0,
                    showRows:{},
                    showFields:{},

                    pageData: this.pageData,

                    headForm: this.pageData.forms.find(form => form.form_type == "head"),
                    bodyForms: this.pageData.forms.filter(form => form.form_type == "body"),
                    referencePages: (() => {
                        let tmp = {};
                        for(let form of this.pageData.forms){
                            for(let [fieldKey,field] of Object.entries(form.fields)){
                                if(field.pageData){
                                    tmp[field.pageData.page.page_id] = field.pageData
                                }
                            }
                        }
                        return tmp
                    })(),
                    schema: convertArrayToObject(this.pageData.forms, 'form_id'),

                    injectFunctions: window.injects,
                },

                injectData : {},

                errors : [],
                messages: [],
                deletedData : [],

                formDatasets: {},

                dataset : null,
                tmpFormData : new FormData(),

                parentDataset: null,
                parentVue : null
            }
        },
        mounted(){
            this.$el.parentElement.addEventListener('scroll',this.onScroll)

            window.addEventListener('resize',this.onScroll)
        },
        watch:{
            'dataset':{
                handler(oldDataset,newDataset){
                    this.$emit("input",this.dataset)
                },
                deep:true
            }
        },
        methods: {
            init(){
                // Remove old data
                this.deletedData = []
                this.errors = []
                this.messages = []
                this.config.formOptions = {}
                this.config.showRows = {}
                this.config.showFields = {}
                this.tmpFormData = new FormData()

                if (this.dataset == null) { // If dataset is null , then generate empty dataset
                    Vue.set(this,'dataset',this.generateEmptyDataset(this.config.schema[this.config.headForm.form_id]))
                }

                // Set dataset to display dataset
                this.formDatasets[this.dataset.form_id] = this.dataset
                // Set and show all sub dataset
                this.showRowSubData(this.dataset)
                // Remove empty row , and leave one empty row
                for(let [formID , form] of Object.entries(this.formDatasets))
                    if(form.subData[formID])
                        this.autoAddOrRemoveRow(form,formID)

                this.status.sending = false
                this.status.loading = false;

                this.dataset.vue = this.returnVue

                this.addPropertiesToDataset(this.dataset)
                this.reRender()
                this.injectOnInit(this)
            },

            // Utils
            getFormOption(formID,key){
                if(formID in this.config.formOptions){
                    return this.config.formOptions[formID][key]
                }else{
                    return null
                }
            },
            setFormOption(formID,key,value){
                if(formID in this.config.formOptions){
                    this.$set(this.config.formOptions[formID],key,value)
                }else{
                    this.$set(this.config.formOptions,formID,{key:value})
                }
            },
            async reRender(){
                this.status.renderComponent = false;
                this.$nextTick().then(() => {
                    this.status.renderComponent = true;
                    setTimeout(_ => {
                        this.onScroll()
                        setTimeout(_ => {this.onScroll()},50);
                    },800);
                });
            },
            getFieldClass(field, form) {
                if (field.field_options != null && field.field_options.wide != null) {
                    return field.field_options.wide + " wide column"
                } else {
                    return "four wide column"
                }
            },

            // Requests
            serializeDataset(toProcessData = null) {
                if (toProcessData == null) toProcessData = this.dataset
                if (toProcessData.tmpID == undefined) this.$set(toProcessData,'tmpID',tmpID++)
                let tmp = {
                    form_id: toProcessData.form_id,
                    data: {},
                    tmpID: toProcessData.tmpID,
                    status: toProcessData.status ? toProcessData.status : ""
                };

                for(let [fieldKey,fieldValue] of Object.entries(toProcessData.data)){
                    if(!(fieldValue === null || fieldValue === undefined) && fieldValue.constructor == Object && fieldKey != 'data_options'){
                        if(fieldValue.vue)
                            tmp.data[fieldKey] = fieldValue.vue().serializeDataset(fieldValue)
                        else{
                            this.$refs['referencePage_' + toProcessData.schema.fields[fieldKey].field_options.pageData.page.page_id].openReferencePageData(fieldValue)
                            tmp.data[fieldKey] = fieldValue.vue().serializeDataset(fieldValue)
                        }
                    }else{
                        tmp.data[fieldKey] = fieldValue
                    }
                }

                if(toProcessData.subData)
                    for (let [formID, subDatas] of Object.entries(toProcessData.subData)) {
                        if(tmp.subData == undefined) tmp.subData = {}
                        tmp.subData[formID] = []
                        for (let subData of subDatas)
                            if(subDatas.length == 1 && !(this.pageData.page.page_options.allow_empty_body))
                                tmp.subData[formID].push(this.serializeDataset(subData))
                            else if(!this.checkRowIsEmpty(subData, this.config.schema[formID]))
                                    tmp.subData[formID].push(this.serializeDataset(subData))
                    }

                return tmp
            },
            get(id) {
                let url = "{{ route($routes['view'],['page_id' => $pageData['page']['page_id'],'id' => '']) }}/" + id
                return fetch(url).then(_ => _.json());
            },
            save() {
                this.$emit("input",this.dataset)

                if(this.config.mode.includes("reference")){
                    return this.closeForm()
                    this.status.sending = false
                }

                this.status.sending = true

                let url = "{{ route($routes['save'],['page_id' => $pageData['page']['page_id']]) }}"
                if(this.dataset.id) url = url + "/" + this.dataset.id

                let data = this.serializeDataset()
                data.deletedData = this.deletedData

                this.tmpFormData.set('data',JSON.stringify(data))

                return sendAPIRequest(url,"POST",this.tmpFormData).then(result => {
                    this.status.sending = false
                    if(result.status){
                        listVue.closeForm()
                        listVue.queryData()
                    }else{
                        this.setErrors(result.errors)
                        this.$refs.errors.focus()
                    }
                })

            },
            verify(method) {
                const URLS = {
                    execute: "{{ route($routes['verify'],['execute',$pageData['page']['page_id'],'~id~']) }}",
                    'return': "{{ route($routes['verify'],['return',$pageData['page']['page_id'],'~id~']) }}",
                    init: "{{ route($routes['verify'],['init',$pageData['page']['page_id'],'~id~']) }}"
                }
                let url = URLS[method].replace('~id~', this.dataset.data.id);
                this.status.loading = true;
                sendAPIRequest(url, "GET").then(result => {
                    this.status.loading = false;
                    this.messages = [];
                    for(let message of result.messages){
                        this.messages.push(message)
                    }
                    if (result.success) {
                        this.viewData(this.dataset.data.id)
                        listVue.reloadPage()
                    }
                })
            },

            // Head events
            onHeadInput(field, dataset) {
                this.injectOnHeadInput(this,field, dataset)
                if(dataset['status'] != 'add') this.$set(dataset,'status',"update")
            },
            onHeadChange(field, dataset) {
                this.injectOnHeadChange(this,field, dataset)
            },
            onHeadClick(field, dataset) {
                this.injectOnHeadClick(this,field, dataset)
            },

            // Body events
            onColInput(parentDataset,row,field,formID) {
                let subDataArray = parentDataset.subData[formID]

                this.injectOnBodyInput(this,parentDataset,row,field,formID,subDataArray)

                if(!this.getFormOption(formID,'disableAutoAddOrRemoveRow'))
                    this.autoAddOrRemoveRow(parentDataset,formID)

                this.onBodyFoucs(parentDataset, row, formID)
                if(row['status'] != 'add') this.$set(row,'status',"update")
            },
            onColChange(parentDataset,row,field,formID) {
                let subDataArray = parentDataset.subData[formID]
                this.injectOnBodyChange(this,parentDataset,row,field,formID,subDataArray)
            },
            onColClick(parentDataset,row,field,formID) {
                let subDataArray = parentDataset.subData[formID]
                this.injectOnBodyClick(this,parentDataset,row,field,formID,subDataArray)
            },

            onRowClicked(parentDataset,row,formID) {
                this.onBodyFoucs(parentDataset, row, formID)
            },
            onBodyFoucs(parentDataset,row,formID) {
                let subDataArray = parentDataset.subData[formID]

                this.showRowSubData(row)
                this.setRowSelected(subDataArray, row)
            },
            onRowDeleteButtonClicked(parentDataset,rowIndex,formID){
                let subDataArray = parentDataset.subData[formID]

                this.deleteRow(parentDataset,formID,rowIndex)

                return false
            },
            async onScroll(){

                this.config.onScrollStatus++;
                let status = this.config.onScrollStatus;

                await timeout(40);
                if(status != this.config.onScrollStatus) return

                const height = ((window.innerHeight || document.documentElement.clientHeight) + 400)
                const width = ((window.innerWidth || document.documentElement.clientWidth) + 100)
                if(this.$refs.row){
                    for(let row of this.$refs.row){
                        const rect = row.getBoundingClientRect();
                        this.$set(this.config.showRows,row.getAttribute('tmpid'),(
                            (rect.left >= -100 && rect.left <= width || rect.right >= 0 && rect.right <= width || (rect.left < -100 && rect.right > width)) &&
                            (rect.top >= -400 && rect.top <= height || rect.bottom >= 0 && rect.bottom <= height || (rect.top < -400 && rect.bottom > height))
                        ));
                    }
                }
                await this.$nextTick()
                if(this.$refs.fields){
                    for(let field of this.$refs.fields){
                        const rect = field.getBoundingClientRect();
                        this.$set(this.config.showFields,field.getAttribute('refid'),(
                            (rect.left >= -100 && rect.left <= width || rect.right >= 0 && rect.right <= width || (rect.left < -100 && rect.right > width)) &&
                            (rect.top >= -400 && rect.top <= height || rect.bottom >= 0 && rect.bottom <= height || (rect.top < -400 && rect.bottom > height))
                        ));
                    }
                }

                for(let [key,status] of Object.entries(this.config.showRows)){
                    if(!status) delete this.config.showRows[key]
                }
                for(let [key,status] of Object.entries(this.config.showFields)){
                    if(!status) delete this.config.showFields[key]
                }

            },

            // External methods
            closeForm(){
                if(this.config.mode.includes("reference"))
                    this.$emit("close")
                else
                    listVue.closeForm()
            },
            addData(){
                this.dataset = null
                this.config.mode = "add"
                this.config.modeText = " - {{$commonTranslations['add']}}"
                this.$set(this.config,'fieldOptions',{})
                this.init()

                this.injectOnAdd(this)
            },
            async viewData(id){
                this.status.loading = true
                this.config.mode = "view"
                this.config.modeText = " - {{$commonTranslations['view']}}"
                this.$set(this.config,'fieldOptions',{ mode : 'view' })

                this.$set(this,'dataset',await this.get(id))
                this.init();

                this.injectOnView(this,id)
            },
            async editData(id){
                this.status.loading = true
                this.config.mode = "edit"
                this.config.modeText = " - {{$commonTranslations['edit']}}"
                this.$set(this.config,'fieldOptions',{})

                this.$set(this,'dataset',await this.get(id))
                this.$set(this.dataset,'status',"")
                this.init();

                this.injectOnEdit(this,id)

                this.status.loading = false
            },
            async copyData(id){
                this.status.loading = true
                this.config.mode = "copy"
                this.config.modeText = " - {{$commonTranslations['copy']}}"
                this.config.fieldOptions = {}

                this.$set(this,'dataset',await this.get(id))
                this.$set(this.dataset,'status',"add")

                this.init();

                const clearData = (dataset) => {
                    if(dataset.schema == undefined) return false

                    this.$set(dataset,'status',"add")

                    for(let [fieldKey,fieldValue] of Object.entries(dataset.data)){
                        if(fieldValue !== null && fieldValue.constructor == Object)
                        clearData(fieldValue)
                    }
                    for(let [fieldKey,field] of Object.entries(dataset.schema.fields)){
                        if(field.field_options.cloneable != undefined && !field.field_options.cloneable){
                            dataset.data[fieldKey] = null
                        }
                    }

                    [dataKey,'id','data_options'].forEach(key =>{
                        if(dataset.data[key])
                            delete dataset.data[key]
                    })
                    if(dataset.subData)
                        for(let [formID,datas] of Object.entries(dataset.subData)){
                            for(let data of datas)
                            clearData(data)
                        }
                }

                clearData(this.dataset)

                this.injectOnCopy(this,id)
            },
            async openReferencePageData(dataset,options){
                this.$set(this,'dataset',dataset ? dataset : null)
                this.$set(this.config,'fieldOptions',{  })
                this.config.modeText = ""
                this.config.mode = "reference"
                this.loading = true

                if(options['mode'] == 'view'){
                    this.config.mode = "reference_view"
                    this.$set(this.config,'fieldOptions',{ mode : 'view' })
                }
                if(options['parentVue']){
                    this.parentVue = options['parentVue'];
                    this.parentDataset = options['parentDataset'];
                }

                this.$el.parentElement.parentElement.parentElement.addEventListener('scroll',this.onScroll)

                this.init()
            },
            async setErrors(errors){
                this.errors = errors.filter(error => error)
                for(let error of errors){
                    if(error.errors){
                        let targetTmpID = error.tmpID
                        const getTargetDataset = (dataset) => {
                            if(dataset.tmpID == targetTmpID){
                                return dataset
                            } else if(dataset.subData){
                                for(let [formID,subData] of Object.entries(dataset.subData))
                                    for(let row of subData){
                                        if(row.tmpID == targetTmpID){
                                            return row
                                        }else{
                                            let childResult = getTargetDataset(row)
                                            return childResult
                                        }
                                    }
                                }
                            return false;
                        }
                        getTargetDataset(this.dataset).data[error.fieldCode].vue().setErrors(error.errors)
                    }
                }
            },



            //{
            //    'form' : formDatasets,
            //    'data' : {data},
            //    'subData' : [{data}]
            //}

            // Reference
            writeReferenceData(fromField,data,fields,dataset){
                this.injectOnReferenceWrite(this,fromField,data,fields,dataset)
                if(fields)
                    for(let [field_code,field] of Object.entries(fields)){
                        if(field.target && data[field_code] !== undefined){
                            dataset.data[field.target] = data[field_code]
                            if(dataset['status'] != 'add') this.$set(dataset,'status',"update")
                        }
                    }
            },

            // File
            onFileSelected(dataset,field,file){
                this.tmpFormData.set("file_"+dataset.tmpID+"_"+field.field_id,file)
            },
            getFile(tmpID,fieldID){
                return this.tmpFormData.get("file_"+tmpID+"_"+fieldID)
            },

            // Dataset Utils
            generateEmptyDataset(schema) {
                let tmp = {
                    form_id: schema.form_id,
                    tmpID: tmpID++,

                    data: this.generateEmptyData(schema),

                    parent: null,
                    status: "add",
                    override: {fields:{}},
                    schema:schema,
                    accessFields: [],

                    subData: {}
                };

                Object.values(this.config.schema)
                    .filter(form => form.form_parent == schema.form_id)
                    .forEach(form => {
                        if (!tmp.subData[form.form_id]) tmp.subData[form.form_id] = []
                        this.addEmptyRow(tmp,form.form_id)
                    })
                // if (this.formDatasets[schema.form_id] == undefined) this.$set(this.formDatasets,schema.form_id,tmp)
                return tmp
            },
            generateEmptyData(schema) {
                var tmp = {};
                for (let [key, fieldData] of Object.entries(schema.fields)) {
                    let defaultValue = fieldData.field_default_value
                    if(fieldData.field_type == "checkboxes")
                        tmp[fieldData.field_code] = defaultValue ? defaultValue.split(",") : [];
                    else if(!fieldData.field_options.system_field)
                        tmp[fieldData.field_code] = defaultValue
                }
                return tmp
            },
            addPropertiesToDataset(dataset,parentDataset = null){
                this.$set(dataset,'tmpID',tmpID++)
                this.$set(dataset,'parent',parentDataset)
                this.$set(dataset,'schema',this.config.schema[dataset.form_id])
                this.$set(dataset,'accessFields',[])
                this.$set(dataset,'override',{fields:{}})
                if(dataset.subData)
                    for(let [formID,subDataArray] of Object.entries(dataset.subData)){
                        for(let subData of subDataArray)
                            this.addPropertiesToDataset(subData,dataset)
                    }
            },

            // Row Utils
            addRow(targetSubDataArray,dataset){
                targetSubDataArray.push(dataset)
                this.$nextTick().then(_ => {
                    this.onScroll()
                })
                this.injectOnRowAdd(this,targetSubDataArray,dataset)
            },
            addEmptyRow(parentDataset,targetFormID){
                let toAddData = this.generateEmptyDataset(this.config.schema[targetFormID])
                toAddData.parent = parentDataset
                this.addRow(parentDataset.subData[targetFormID],toAddData)
            },
            deleteRow(parentDataset,targetFormID,targetIndex){
                let targetArray = parentDataset.subData[targetFormID]
                if(targetArray == undefined)return
                let dataset = targetArray[targetIndex]
                let schema = this.config.schema[dataset.form_id]

                this.injectOnRowDelete(this,parentDataset,targetFormID,targetIndex)

                for(let [field_code,field] of Object.entries(dataset.schema.fields)){
                    if(field.field_type == "file"){
                        if(this.tmpFormData.has("file_"+dataset.tmpID+"_"+field.field_id)){
                            this.tmpFormData.delete("file_"+dataset.tmpID+"_"+field.field_id)
                        }
                    }
                }

                if(dataset.data.id)
                    this.deletedData.push({
                        id : dataset.data.id,
                        form_id : dataset.form_id
                    })

                if(dataset.subData)
                    for(let [formID,subDataArray] of Object.entries(dataset.subData)){
                        for(let [rowIndex,rowData] of subDataArray.entries()){
                            this.deleteRow(dataset,formID,0)
                        }
                    }

                targetArray.splice(targetIndex,1)

                if(targetArray.length == 0){
                    this.autoAddOrRemoveRow(parentDataset,targetFormID)
                }

                const showFirstRow  = (row) => {
                    row.selected = true
                    for (let [form_id, subRows] of Object.entries(row.subData)) {
                        console.log(form_id,row,row.data.Str)
                        this.formDatasets[form_id] = row
                        showFirstRow(subRows[0])
                    }
                }

                targetArray[0].selected = true
                showFirstRow(targetArray[0])
                setTimeout(_ => {this.onScroll()},50)
            },

            checkRowIsEmpty(row, schema) {
                let status = true;
                for (let [key, field] of Object.entries(schema.fields)) {
                    if(field.field_type == 'reference_page'){

                    }
                    else if (field.field_default_value == null) {
                        if (!(row.data[key] == null || row.data[key] == "" || row.data[key] == undefined)) status = false
                    } else {
                        if (row.data[key] != field.field_default_value) status = false
                    }
                }
                return status;
            },

            autoAddOrRemoveRow(parentDataset,targetFormID) {
                let currentDataset = parentDataset.subData[targetFormID]
                let schema = this.config.schema[targetFormID]

                let firstRowEmptyFlag = false
                currentDataset.forEach((row, rowIndex) => {
                    if (rowIndex == 0 && this.checkRowIsEmpty(row, schema)) return firstRowEmptyFlag = true // Check first row is empty and dont remove it
                    if (rowIndex == (currentDataset.length - 1) && !firstRowEmptyFlag) return
                    if (this.checkRowIsEmpty(row, schema)) this.deleteRow(parentDataset,targetFormID,rowIndex)
                });

                if(this.config.mode != 'view'){
                    let lastRow = currentDataset[currentDataset.length - 1]
                    if(!lastRow)
                        this.addEmptyRow(parentDataset,targetFormID, schema)
                    else if(!this.checkRowIsEmpty(lastRow, schema))
                        this.addEmptyRow(parentDataset,targetFormID, schema)
                }
            },

            setRowSelected(subDataArray, row) {
                if(subDataArray)
                    for(let row of subDataArray){
                        this.$set(row,'selected',false)
                    }
                if(row)
                    this.$set(row,'selected',true)
            },
            showRowSubData(row) {
                if(row){
                    for (let [form_id, subRows] of Object.entries(row.subData)) {
                        this.formDatasets[form_id] = row
                        this.showRowSubData(subRows[0])
                        this.setRowSelected(subRows,subRows[0])
                        this.autoAddOrRemoveRow(row,form_id)
                    }
                }
                setTimeout(_ => {this.onScroll()},50)
            },

            // Errors utils
            focusError(targetTmpID){
                if(targetTmpID == null || targetTmpID == undefined) return false
                let datasetPath = []
                const showTargetDataset = (dataset) => {
                    if(dataset.tmpID == targetTmpID){
                        datasetPath.unshift(dataset)
                        return true
                    } else if(dataset.subData){
                        for(let [formID,subData] of Object.entries(dataset.subData))
                            for(let row of subData){
                                if(row.tmpID == targetTmpID){
                                    datasetPath.unshift(row)
                                    return true
                                }else{
                                    let childResult = showTargetDataset(row)
                                    if(childResult) datasetPath.unshift(row)
                                    return childResult
                                }
                            }
                        }
                    return false;
                }
                showTargetDataset(this.dataset)
                datasetPath.forEach(row => {
                    if(row.parent != null)
                        this.onBodyFoucs(row.parent,row,row.form_id)
                })
            },

            // Inject functions
            async injectOnInit(that){
                for(let inject of this.config.injectFunctions.injectOnInit){
                    await inject(that,this.pageData)
                }
            },
            async injectOnAdd(that){
                for(let inject of this.config.injectFunctions.injectOnAdd){
                    await inject(that,this.pageData)
                }
            },
            async injectOnView(that,id){
                for(let inject of this.config.injectFunctions.injectOnView){
                    await inject(that,this.pageData)
                }
            },
            async injectOnEdit(that,id){
                for(let inject of this.config.injectFunctions.injectOnEdit){
                    await inject(that,this.pageData,id)
                }
            },
            async injectOnCopy(that,id){
                for(let inject of this.config.injectFunctions.injectOnCopy){
                    await inject(that,this.pageData,id)
                }
            },

            async injectOnReferenceWrite(that,fromField,data,fields,dataset){
                for(let inject of this.config.injectFunctions.injectOnReferenceWrite){
                    await inject(that,this.pageData,fromField,data,fields,dataset)
                }
            },

            async injectOnRowAdd(that,targetSubDataArray,dataset){
                for(let inject of this.config.injectFunctions.injectOnRowAdd){
                    await inject(that,this.pageData,targetSubDataArray,dataset)
                }
            },
            async injectOnRowDelete(that,parentDataset,formID,rowIndex){
                for(let inject of this.config.injectFunctions.injectOnRowDelete){
                    await inject(that,this.pageData,parentDataset,formID,rowIndex)
                }
            },

            async injectOnHeadInput(that,field,dataset){
                for(let inject of this.config.injectFunctions.injectOnHeadInput){
                    await inject(that,this.pageData,field,dataset)
                }
            },
            async injectOnHeadChange(that,field,dataset){
                for(let inject of this.config.injectFunctions.injectOnHeadChange){
                    await inject(that,this.pageData,field,dataset)
                }
            },
            async injectOnHeadClick(that,field,dataset){
                for(let inject of this.config.injectFunctions.injectOnHeadClick){
                    await inject(that,this.pageData,field,dataset)
                }
            },

            async injectOnBodyInput(that,parentDataset,row,field,formID,subDataArray){
                for(let inject of this.config.injectFunctions.injectOnBodyInput){
                    await inject(that,this.pageData,parentDataset,row,field,formID,subDataArray)
                }
            },
            async injectOnBodyChange(that,parentDataset,row,field,formID,subDataArray){
                for(let inject of this.config.injectFunctions.injectOnBodyChange){
                    await inject(that,this.pageData,parentDataset,row,field,formID,subDataArray)
                }
            },
            async injectOnBodyClick(that,parentDataset,row,field,formID,subDataArray){
                for(let inject of this.config.injectFunctions.injectOnBodyClick){
                    await inject(that,this.pageData,parentDataset,row,field,formID,subDataArray)
                }
            }


        }
    })

    let formVueContainer = new Vue({
        el: '#formVueElement',
        data(){
            return {
                pageData : pageData
            }
        }
    })

    let formVue = formVueContainer.$refs.form;
</script>

@yield('form_after_script')

@endsection
