@extends('layouts.default')
@section('title', $pageData["page"]["translation"])
@section('content')
{{-- <div id="reportVue">

</div> --}}
<div id="formVueElement">
    <universal-form :page-data="pageData" ref='form' />
</div>

<script type="text/x-template" id="universal-form">
    <div v-if="status.renderComponent" v-cloak>
        <h2>{{$pageData["page"]["translation"]}}</h2>
        <div v-if="status.loading">
            <div class="ts active centered inline massive text loader">
                {{ $commonTranslations['loading'] }}
                <div class="ts text loader"></div>
            </div>
        </div>

        <div v-if="!status.loading">
            <!-- Title -->
            <h3>{{$commonTranslations['filter']}} : </h3>
            <div class="ts message" v-if="messages.length > 0">
                <div class="content">
                    <div class="ts relaxed divided list">
                        <div class="item" v-for="message in messages">@{{message}}</div>
                    </div>
                </div>
            </div>

            <div class="ts inverted icon negative message" v-if="errors.length > 0">
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
                                    :rootDataset="dataset"
                                    @reference="(data,fields) => writeReferenceData(field,data,fields,formDatasets[config.headForm.form_id])"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="sixteen wide column" style="margin-top: 2.5em">
                        <div class="ts input">
                            <div class="inline field">
                                <label>{{ $commonTranslations['output_format'] }} : </label>
                                <select v-model="fileType">
                                    <option value="pdf">PDF</option>
                                    <option value="csv">CSV</option>
                                    <option value="docx">Word</option>
                                    <option value="xlsx">Excel</option>
                                    <option value="pptx">PowerPoint</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <br />
            <button @click.prevent="output" class="ts positive button" :disabled="status.sending">
                {{ $commonTranslations['output'] }}
            </button>
        </div>
    </div>
</script>
@endsection

@section('script')
@parent
<script>
    const pageData = @json($pageData);
    const pageOptions = @json($pageOptions);
    const permission = @json($permission);

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
                    renderComponent: true,
                    text: "",
                },

                config: {
                    mode: "add",
                    modeText: "",

                    fieldOptions: {},
                    formOptions: {},

                    pageData: this.pageData,

                    headForm: this.pageData.forms.find(form => form.form_type == "head"),
                    schema: convertArrayToObject(this.pageData.forms, 'form_id'),

                    injectFunctions: window.injects
                },

                injectData : {},

                errors : [],
                messages: [],
                deletedData : [],

                formDatasets: {},

                dataset : null,
                fileType : 'pdf',
                tmpFormData : new FormData(),

                parentDataset: null,
                parentVue : null
            }
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
                this.tmpFormData = new FormData()

                if (this.dataset == null) { // If dataset is null , then generate empty dataset
                    Vue.set(this,'dataset',this.generateEmptyDataset(this.config.schema[this.config.headForm.form_id]))
                }

                // Set dataset to display dataset
                this.formDatasets[this.dataset.form_id] = this.dataset
                // Set and show all sub dataset
                // this.showRowSubData(this.dataset)
                // Remove empty row , and leave one empty row
                /* for(let [formID , form] of Object.entries(this.formDatasets))
                    if(form.subData[formID])
                        this.autoAddOrRemoveRow(form,formID) */

                this.status.sending = false
                this.status.loading = false;

                this.dataset.vue = this.returnVue

                this.addPropertiesToDataset(this.dataset)
                this.reRender()
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
                });
            },
            getFieldClass(field, form) {
                if (field.field_options != null && field.field_options.wide != null) {
                    return  field.field_options.wide + " wide column"
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
                        tmp.data[fieldKey] = fieldValue.vue().serializeDataset(fieldValue)
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
            async output(){
                this.$emit("input",this.dataset)

                if(this.config.mode.includes("reference")){
                    return this.closeForm()
                    this.status.sending = false;
                }

                this.status.sending = true;
                this.status.text = "processing";
                this.loadingText();

                let url = "{{ route($routes['report'],['id' => $pageData['page']['page_id']]) }}"
                // if(this.dataset.id) url = url + "/" + this.dataset.id

                let data = this.serializeDataset();
                data.deletedData = this.deletedData;

                this.tmpFormData.append('filters',JSON.stringify(data));
                this.tmpFormData.append('format',this.fileType);

                await this.delay(500);
                const that = this;
                return sendAPIRequest(url,"POST",this.tmpFormData).then(async function (result){
                    that.status.text = "outputing";
                    await that.delay(1600);
                    that.status.sending = false;
                    if(result.status){
                        that.errors = [];
                        window.open(result.file);
                    }else{
                        for(let i in result.messages){
                            result.messages[i] = {
                                text: result.messages[i]
                            };
                        }
                        that.setErrors(result.messages)
                    }
                }).catch(e => {
                    that.status.sending = false;
                    that.setErrors([
                        {
                            text: "{{$commonTranslations['error.unknown'].$commonTranslations['contact_maintenance']}}",
                        }
                    ]);
                    console.error(e);
                });
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

            // Reference
            writeReferenceData(fromField,data,fields,dataset){
                // this.injectOnReferenceWrite(this,fromField,data,fields,dataset)
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
                this.tmpFormData.append("file_"+dataset.tmpID+"_"+field.field_id,file)
            },

            // Dataset Utils
            generateEmptyDataset(schema) {
                let tmp = {
                    form_id: schema.form_id,
                    tmpID: tmpID++,

                    data: this.generateEmptyData(schema),

                    parent: null,
                    status: "add",
                    schema:schema,

                    subData: {}
                };

                Object.values(this.config.schema)
                    .filter(form => form.form_parent == schema.form_id)
                    .forEach(form => {
                        if (!tmp.subData[form.form_id]) tmp.subData[form.form_id] = []
                        // this.addEmptyRow(tmp,form.form_id)
                    })
                if (this.formDatasets[schema.form_id] == undefined) this.$set(this.formDatasets,schema.form_id,tmp)
                return tmp
            },
            generateEmptyData(schema) {
                var tmp = {};
                for (let [key, fieldData] of Object.entries(schema.fields)) {
                    if(fieldData.field_options.system_field !== true){
                        let defaultValue = fieldData.field_default_value
                        if(fieldData.field_type == "checkboxes")
                            tmp[fieldData.field_code] = defaultValue ? defaultValue.split(",") : [];
                        else
                            tmp[fieldData.field_code] = fieldData.field_default_value
                    }
                }
                return tmp
            },
            addPropertiesToDataset(dataset,parentDataset = null){
                this.$set(dataset,'tmpID',tmpID++)
                this.$set(dataset,'parent',parentDataset)
                this.$set(dataset,'schema',this.config.schema[dataset.form_id])
                if(dataset.subData)
                    for(let [formID,subDataArray] of Object.entries(dataset.subData)){
                        for(let subData of subDataArray)
                            this.addPropertiesToDataset(subData,dataset)
                    }
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

            async loadingText(){
                let i = -1;
                fullscreenDimmer.loading();
                while(this.status.sending){
                    let temp = '';
                    const text = window.commonTranslations[this.status.text];
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
        mounted() {
            this.addData();
        }
    })

    /* let pdfModal = new Vue({
        el: "#pdfModalElement",
        data(){
            return {
                file: getURL("reports/pdf/5d688e2264967.pdf"),
            };
        },

    }); */

    let formVueContainer = new Vue({
        el: '#formVueElement',
        data(){
            return {
                pageData : pageData
            }
        }
    });

    let formVue = formVueContainer.$refs.form;
</script>
@endsection
