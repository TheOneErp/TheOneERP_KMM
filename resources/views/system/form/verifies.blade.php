@extends('layouts.default')
@section('title', $verify_data["page"]["translation"])
@section('content')

<!-- Notice Messages -->
<div id="loading-dimmer" class="ts active dimmer">
    <div class="ts loader"></div>
</div>

<div id="formVue" v-cloak>
    <h3 class="ts header">@{{verify_data.page.translation}}：@{{translations.edit_page_name}}</h3>
    {{-- Error --}}
    <div class="ts inverted icon negative message" v-if="errors.length > 0">
        <i class="remove circle icon"></i>
        <div class="content">
            <p v-for="error in errors">@{{error.message}} <a v-if="error.link != null && error.link != undefined" class="link" id="#" @click="error.link">Click me</a></p>
        </div>
    </div>
    <div style="margin-bottom: 1.5rem" v-if="config.mode != 'view'">
        <button class="ts positive button" @click="addLevel">
            @{{translations.add}} @{{translations.level}}
        </button>
    </div>
    <form class="ts form" style="margin-bottom: 1.5rem">
        <table
            :id="'level'+levelIndex"
            :key="'level'+(levelIndex)"
            class="ts stackable striped single line compact table"
            v-for="(level,levelIndex) in input.level"
        >
            <thead>
                <tr>
                    <th colspan="5" style="font-weight: bold;">
                        @{{translations.level_number.replace(':number',levelIndex+1)}}
                    </th>
                    <th class="one wide" style="text-align: center;" v-if="config.mode != 'view'">
                        <button
                            class="ts tiny very compact negative basic button"
                            @click.prevent="removeLevel(levelIndex)"
                        >
                            @{{translations.remove}}
                        </button>
                    </th>
                </tr>
                <tr>
                    <th style="text-align: center;">#</th>
                    <th v-for="field in fieldToShow(level_fields)" style="text-align: center;">@{{field.translation}}</th>
                    <th colspan="2" style="text-align: center;"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(target, targetIndex) in level" :key="'target'+targetIndex" >
                    <td style="text-align: center;">@{{targetIndex+1}}</td>
                    <td v-for="field in fieldToShow(level_fields)" style="text-align: center;">
                        <div class="ts fluid field" v-if="field.field_type === 'select'">
                            <select
                                class="ts fluid basic dropdown"
                                v-bind="{
                                    id: field.field_code+'.'+levelIndex+'-'+targetIndex,
                                    disabled: config.mode=='view'
                                }"
                                v-model="target[field.field_code]"
                                {{-- @change="autoAddOrRemoveTarget(levelIndex)" --}}
                                @change="levelOnChange(levelIndex,targetIndex,field,$event)"
                            >
                                <option v-for="opt in field.field_options.options" :value="opt">
                                    @{{optionTranlation(opt)}}
                                </option>
                            </select>
                        </div>
                        <div
                            class="ts field sixteen wide column"
                            :class="{disabled: config.mode==='view'}"
                            v-else-if="field.field_code === 'verify_target_id'"
                        >
                            <universal-reference
                                :field="field"
                                :id="field.field_code+'.'+levelIndex+'-'+targetIndex"
                                :ref="field.field_code+'.'+levelIndex+'-'+targetIndex"
                                :key="field.field_code+target.tmpID"
                                :value="target.verify_target_name"
                                :dataset="levelDataset(target)"
                                @reference="putTarget(levelIndex,targetIndex,$event)"
                                {{-- @change="autoAddOrRemoveTarget(levelIndex)" --}}
                                @change="levelOnChange(levelIndex,targetIndex,field,$event)"
                            >
                        </div>
                        <div
                            v-else-if="field.field_code === 'verify_population'"
                            class="ts field"
                            :class="{
                                disabled: (
                                    config.mode==='view' ||
                                    target.verify_target_type==='user'
                                )
                            }"
                        >
                            <input
                                type="number"
                                step="1"
                                min="1"
                                :max="target.verify_population_max"
                                :id="field.field_code+'.'+levelIndex+'-'+targetIndex"
                                :ref="field.field_code+'.'+levelIndex+'-'+targetIndex"
                                v-model.number="target[field.field_code]"
                                v-number-limit-validate
                                @input="autoAddOrRemoveTarget(levelIndex)"
                                @change="levelOnChange(levelIndex,targetIndex,field,$event)"
                            >
                        </div>
                    </td>
                    <td class="" style="text-align: center;">
                        <button class="ts button" @click.prevent="openCondition(levelIndex,targetIndex)">
                            @{{translations.condition}}
                        </button>
                    </td>
                    <td class="one wide" style="text-align: center;" v-if="config.mode != 'view'">
                        <i
                            class="remove large icon tr-remover"
                            :id="'remove-form'+(levelIndex)+'-target'+targetIndex"
                            @click="removeTarget(levelIndex,targetIndex)"
                        ></i>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
    {{-- Condition Modal --}}
    <div id="condition-modal" class="ts modals dimmer">
        <dialog
            id="conditionModal"
            class="ts modal"
            style="width: 100%"
        >
            <div class="header">
                <h3 class="ts header">@{{conditionModal.title}}</h3>
            </div>
            <div class="content">
                {{-- Error for Condition --}}
                <div class="ts inverted icon negative message" v-if="conditionErrors.length > 0">
                    <i class="remove circle icon"></i>
                    <div class="content">
                        <p v-for="error in conditionErrors">@{{error.message}} <a v-if="error.link != null && error.link != undefined" class="link" id="#" @click="error.link">Click me</a></p>
                    </div>
                </div>
                <form class="ts form">
                    <table
                        :id="`condition.${conditionModal.editing.level}-${conditionModal.editing.target}`"
                        :id="`condition.${conditionModal.editing.level}-${conditionModal.editing.target}`"
                        class="ts stackable striped single line compact table"
                    >
                        <thead>
                            <tr>
                                <th style="text-align: center;">#</th>
                                <th v-for="field in fieldToShow(condition_fields)" style="text-align: center;">@{{field.translation}}</th>
                                <th>
                                    <i
                                        v-if="config.mode!='view'"
                                        class="add large icon add-icon-button"
                                        @click="addCondition()"
                                    ></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(condition, conditionIndex) in conditionModal.data" :key="'condition'+conditionIndex" >
                                <td style="text-align: center;">@{{conditionIndex+1}}</td>
                                <td
                                    v-for="field in fieldToShow(condition_fields)"
                                    style="text-align: center;"
                                    :class="{
                                        'two wide': (
                                            field.field_code === 'verify_condition_group' ||
                                            field.field_code === 'verify_comparison'
                                        ),
                                        'one wide': field.field_code === 'verify_logical'
                                    }"
                                >
                                    <div class="ts fluid field" v-if="field.field_type === 'select'">
                                        <select
                                            class="ts fluid basic dropdown"
                                            :id="field.field_code+'.'+conditionIndex"
                                            v-bind="{
                                                id: field.field_code+'.'+conditionIndex,
                                                disabled: config.mode=='view'
                                            }"
                                            v-model="conditionModal.data[conditionIndex][field.field_code]"
                                            @change="conditionOnChange(conditionIndex,field)"
                                        >
                                            <option v-for="opt in field.field_options.options" :value="opt">
                                                @{{optionTranlation(opt)}}
                                            </option>
                                        </select>
                                    </div>
                                    <div
                                        v-else
                                        class="ts field"
                                        :class="{disabled: config.mode==='view'}"
                                    >
                                        <input
                                            type="text"
                                            :id="field.field_code+'.'+conditionIndex"
                                            v-if="field.field_type === 'string'"
                                            v-model="conditionModal.data[conditionIndex][field.field_code]"
                                            {{-- @input="reorderCondition()" --}}
                                            @change="conditionOnChange(conditionIndex,field)"
                                        >
                                        <input
                                            type="number"
                                            min="0"
                                            :id="field.field_code+'.'+conditionIndex"
                                            v-else-if="field.field_code === 'verify_condition_group'"
                                            v-model.number="conditionModal.data[conditionIndex][field.field_code]"
                                            v-number-limit-validate
                                            {{-- @input="reorderCondition()" --}}
                                            @change="conditionOnChange(conditionIndex,field)"
                                        >
                                    </div>
                                </td>
                                <td class="one wide" style="text-align: center;" v-if="config.mode != 'view'">
                                    <i
                                        class="remove large icon tr-remover"
                                        :id="'remove-condition'+(conditionIndex)+'-condition'+conditionIndex"
                                        @click="removeCondition(conditionIndex)"
                                    ></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="actions">
                <div class="ts fluid separated stackable buttons">
                    <button class="ts positive button" id="condition_confirm">
                        @{{translations.confirm}}
                    </button>
                    <button class="ts deny button" id="condition_cancel">
                        @{{translations.cancel}}
                    </button>
                </div>
            </div>
        </dialog>
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
    const verify_data = @json($verify_data);
    const level_data = @json($level_data);
    const condition_data = @json($condition_data);
    const translations = @json($translations);
    const pageType = "{{$type}}";
    const pageId = @json($pageId);
    const dataId = @json($dataId);
    let tmpID = 0;

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
                pageId,
                status: "loading"
            },
            originData,
            verify_data,
            verify_form_id: verify_data.forms[0].form_id,
            verify_fields: verify_data.forms[0].fields,
            level_data,
            level_form_id: level_data.forms[0].form_id,
            level_fields: level_data.forms[0].fields,
            condition_data,
            condition_fields: condition_data.forms[0].fields,
            translations,
            tmpID,
            referenceTmpID: [],
            input:{
                level: [],
                verify_remarks: null,
            },
            conditionModal: {
                title: null,
                editing: {
                    level: null,
                    target: null,
                },
                data: [],
                dataChanged: false,
                errors:{},
            },
            errors:[],
        },
        mounted() {
            this.addLevel();
            if(this.config.mode !== 'insert'){
                if(this.originData != null){
                    this.input = deepClone(this.originData);
                    if(this.config.mode === 'update'){
                        for(let i in this.input.level){
                            for(let j in this.input.level[i]){
                                this.input.level[i][j]["tmpID"] = this.tmpID++;
                            }
                            this.autoAddOrRemoveTarget(i);
                        }
                    }
                }
            }
            loadingDimmer.toggle();
        },
        computed:{
            initCondition(){
                return deepClone({
                    field_code: null,
                    verify_comparison: null,
                    verify_condition_group: 0,
                    verify_logical: null,
                    verify_value: null,
                });
            },
            initTarget(){
                return deepClone({
                    verify_target_type: null,
                    verify_target_id: null,
                    verify_target_name: null,
                    verify_population: 1,
                    verify_population_max: null,
                    conditions: [this.initCondition]
                });
            },
            initLevel(){
                return deepClone([this.initTarget]);
            },
            conditionErrors(){
                let result = [];
                const conditionData = this.conditionModal.editing;
                const level = conditionData.level;
                const target = conditionData.target;
                // console.log(level,target)
                if(!isAnyEmpty(level, target) && !isEmpty(this.conditionModal.errors[level]) && !isEmpty(this.conditionModal.errors[level][target])){
                    result = this.conditionModal.errors[level][target];
                }
                return result;
            }
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
                    this.conditionModal.errors = [];
                    this.config.sending = true;
                    this.config.status = "processing";
                    this.loadingText();
                    for(let l in this.input.level){
                        this.autoAddOrRemoveTarget(l, false);
                    }
                    await this.delay(1600);

                    let inputClone = deepClone(this.input);
                    let url = getURL(`verifies/save/${this.config.mode}/${this.config.pageId}`);

                    this.config.status = "accessing";
                    sendAPIRequest(url,"post",inputClone).then(result => {
                        if(result.success){
                            this.config.status = "redirecting";
                            document.location.href = getURL('verifies/list');
                        }else{
                            console.log(result);
                            let validationErrors = [];
                            if(result.errors != undefined && result.errors.length > 0){
                                for(let error of result.errors){
                                    let link = function(){formVue.focusError(error.type, error.levelIndex, error.targetIndex, error.id)};
                                    validationErrors.push({
                                        message: error.message,
                                        type: error.type,
                                        levelIndex: error.levelIndex,
                                        targetIndex: error.targetIndex,
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
            saveFailed(errors){
                // fullscreenDimmer.unloading();
                this.config.sending = false;
                let conditionErrors = {};
                for(let e of errors){
                    if(e.type === 'condition'){
                        if(isEmpty(conditionErrors[e.levelIndex])){
                            conditionErrors[e.levelIndex] = {};
                        }
                        if(isEmpty(conditionErrors[e.levelIndex][e.targetIndex])){
                            conditionErrors[e.levelIndex][e.targetIndex] = [];
                        }
                        conditionErrors[e.levelIndex][e.targetIndex].push(e);
                    }else{
                        this.errors.push(e);
                    }
                }
                if(!isEmpty(conditionErrors)){
                    this.$set(this.conditionModal,'errors',conditionErrors);
                }
                for(let l in this.input.level){
                    const targets = this.input.level[l];
                    if(targets.length == 1 && !equalityComparison(targets[0],this.initTarget)){
                        this.addTarget(l);
                    }
                }
                document.querySelector("#content").scrollTop = 0;
            },
            focusError(type, levelIndex, targetIndex, id = null){
                if(!isEmpty(id)){
                    const idSplit = id.split(".");
                    if(idSplit[0] === "verify_target_id"){
                        this.$refs[id][0].openReferenceModal();
                    }else{
                        const el = document.getElementById(id);
                        if(el != undefined){
                            // console.log(el);
                            setTimeout(() => {
                                el.focus();
                            },100);
                        }
                    }
                }
            },
            isFieldReadOnly(field){
                let isReadonly = false;
                if(field.field_readonly){
                    if(field.field_options != null && field.field_options.readonly != undefined){
                        isReadonly = field.field_options.readonly == this.config.mode;
                    }else{
                        isReadonly = true;
                    }
                }else if(false){}
                return isReadonly;
            },
            addLevel(){
                this.input.level.push(deepClone(this.initLevel));
            },
            removeLevel(levelIndex){
                if(this.input.level.length - 1 >= 1){
                    delete this.input.level[levelIndex];
                    this.input.level = this.input.level.filter(() => true);
                }
            },
            autoAddOrRemoveTarget(levelIndex, add = true){
                const targets = deepClone(this.input.level[levelIndex]);
                for(let targetIndex in targets){
                    const target = targets[targetIndex];
                    const toComparison = deepClone(targets[targetIndex]);
                    delete toComparison["tmpID"];
                    if(equalityComparison(toComparison,this.initTarget) && targets.length - 1 > 0){
                        delete targets[targetIndex]
                    };
                }
                this.$set(this.input.level,levelIndex,targets.filter(() => true));
                if(add) this.addTarget(levelIndex);
            },
            addTarget(levelIndex){
                let target = deepClone(this.initTarget);
                target["tmpID"] = this.tmpID++;
                this.input.level[levelIndex].push(deepClone(target));
            },
            putTarget(levelIndex, targetIndex, data){
                const targetData = this.input.level[levelIndex][targetIndex];
                const populationEl = this.$refs[`verify_population.${levelIndex}-${targetIndex}`];
                // console.log(populationEl[0]);
                const f = new Promise((resolve, reject) => {
                    targetData.verify_target_id = data.verify_target_id;
                    targetData.verify_target_name = data.verify_target_name;
                    targetData.verify_population_max = data.verify_population_max;
                    resolve();
                });
                f.then(() => {
                    if(!isEmpty(populationEl)) populationEl[0].dispatchEvent(new Event("input"));
                    console.log(this.input.level[levelIndex][targetIndex]);
                });
            },
            removeTarget(levelIndex, targetIndex){
                const targets = deepClone(this.input.level[levelIndex]);

                if(targetIndex < targets.length - 1){
                    delete targets[targetIndex];
                    this.$set(this.input.level,levelIndex,targets.filter(() => true));
                }
            },
            autoRemoveCondition(){
                const conditions = this.conditionModal.data;
                for(let conditionIndex in conditions){
                    const condition = conditions[conditionIndex];
                    if(equalityComparison(condition,this.initCondition) && conditions.length - 1 > 0){
                        delete conditions[conditionIndex];
                    }
                }
                this.$set(this.conditionModal,'data',conditions.filter(() => true));
                // this.addCondition();
            },
            addCondition(){
                this.conditionModal.data.push(deepClone(this.initCondition));
                this.conditionModal.dataChanged = true;
                this.reorderCondition();
            },
            removeCondition(conditionIndex){
                const conditions = this.conditionModal.data;

                if(conditions.length - 1 > 0){
                    delete conditions[conditionIndex];
                    this.$set(this.conditionModal,'data',conditions.filter(() => true));
                    this.conditionModal.dataChanged = true;
                }
                this.reorderCondition()
            },
            reorderCondition(){
                this.conditionModal.data.sort(function(a,b){
                    return a.verify_condition_group - b.verify_condition_group;
                });
            },
            openCondition(levelIndex,targetIndex){
                const target = this.input.level[levelIndex][targetIndex];
                const that = this;
                this.initConditionModal(levelIndex,targetIndex);
                ts('#conditionModal').modal({
                    onApprove: function() {
                        that.autoRemoveCondition();
                        that.$set(target,'conditions',deepClone(that.conditionModal.data));
                        that.closeCondition();
                    },
                    onDeny: function() {
                        let toClose = true;
                        if(that.conditionModal.dataChanged){
                            toClose = confirm(translations.unsave_confirm);
                        }
                        if(toClose){
                            // this.conditionModal.onShow = false;
                            that.closeCondition();
                        }
                        return toClose;
                    }
                }).modal("show");
                document.querySelector("#conditionModal").scrollTop = 0;
            },
            initConditionModal(levelIndex,targetIndex){
                const conditions = deepClone(this.input.level[levelIndex][targetIndex].conditions);
                const titleLevel = this.translations.level_number.replace(":number",levelIndex+1);
                const editing = {level: levelIndex, target: targetIndex};
                this.conditionModal.title = unionString(titleLevel, this.input.level[levelIndex][targetIndex].verify_target_name, " / ");

                this.$set(this.conditionModal,'data',conditions);
                this.$set(this.conditionModal,'editing',editing);
            },
            closeCondition(){
                this.autoRemoveCondition();
                this.$set(this.conditionModal,'data',[]);
                this.conditionModal.dataChanged = false;
                this.conditionModal.title = null;
                for(let i in this.conditionModal.editing){
                    this.conditionModal.editing[i] = null;
                }
            },
            levelOnChange(levelIndex,targetIndex,field,event){
                this.autoAddOrRemoveTarget(levelIndex);
                const target = this.input.level[levelIndex][targetIndex];
                /* if(field.field_code === "verify_target_type"){
                    target.verify_target_id = null;
                    target.verify_target_name = null;
                    target.verify_population_max = null;
                    if(target.verify_target_type === 'user'){
                        target.verify_population = 1;
                    }
                }else  */if(field.field_code === "verify_target_id"){
                    if(isEmpty(event)){
                        target.verify_target_id = null;
                        target.verify_target_name = null;
                        target.verify_population_max = null;
                    }
                }
            },
            conditionOnChange(conditionIndex,field){
                // this.autoAddOrRemoveCondition();
                this.conditionModal.dataChanged = true;

                if(field.field_code === "verify_condition_group"){
                    this.reorderCondition();
                }
            },
            fieldToShow: function(fields){
                let result = [];
                for(let field_code in fields){
                    const field = fields[field_code];
                    if(field.field_show_on_form) result.push(field);
                }
                return result;
            },
            verifyDataset(){
                return {
                    form_id: this.verify_form_id,
                    data: {page_id: this.config.pageId},
                    parent: null,
                    schema: this.verify_data.forms[0]
                };
            },
            levelDataset(target){
                return {
                    form_id: this.level_form_id,
                    data: deepClone(target),
                    parent: this.verifyDataset(),
                    schema: this.level_data.forms[0]
                };
            },
            optionTranlation(value){
                value = value.toString();
                let translation = this.translations["field.options"][value];
                if(value === "<>") translation = this.translations["field.options"]["!="];
                if(isEmpty(translation)){
                    translation = this.translations["field.options"][value.toLowerCase()];
                }
                if(isEmpty(translation)){
                    translation = this.translations[value];
                }
                if(isEmpty(translation)){
                    translation = this.translations[value.toLowerCase()];
                }
                if(isEmpty(translation)){
                    translation = value;
                }
                return translation;
            },
            findFieldRule(field, rule){
                let result = false;
                const toFind = field.field_rule.find(x => x.includes(rule));
                if(!isEmpty(toFind)){
                    if(toFind.includes(":")) result = toFind.split(":")[1];
                }
                return result;
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
            isEmpty(...value){isEmpty(...value)},
            getURL: function(URL){return getURL(URL)},
        },
    });

    document.querySelector("#content").style['overflow-y'] = 'auto';
</script>
@endsection
