@extends('layouts.default')
@section('title', 'INSERT指令產生器')
@section('content')
<div id="el">
    <form class="ts form">
        <div class="ts labeled input" style="margin-bottom: .4em;">
            <div
                class="ts label"
                style="padding-left: 0px; background: none; border-top: none; border-bottom: none; border-left: none;"
            >
                <div class="ts radio checkbox">
                    <input
                        id="A"
                        type="radio"
                        name="mode"
                        v-model="mode"
                        value="page_id"
                    >
                    <label for="A">頁面ID</label>
                </div>
            </div>
            <input
                type="text"
                class="ts fluid input"
                :class="{disabled: mode!='page_id'}"
                v-model="page_id"
            >
        </div>
        <div class="ts labeled input" style="margin-bottom: .4em;">
            <div
                class="ts label"
                style="padding-left: 0px; background: none; border-top: none; border-bottom: none; border-left: none;"
            >
                <div class="ts radio checkbox">
                    <input
                        id="B"
                        type="radio"
                        name="mode"
                        v-model="mode"
                        value="table_name"
                    >
                    <label for="B">資料表</label>
                </div>
            </div>
            <input
                type="text"
                class="ts fluid input"
                :class="{disabled: mode!='table_name'}"
                v-model="table_name"
            >
        </div>
        <div class="ts labeled input" style="margin-bottom: .4em;">
            <div
                class="ts label"
                style="padding-left: 0px; background: none; border-top: none; border-bottom: none; border-left: none;"
            ><label>忽略欄位</label></div>
            <input type="text" v-model="ignore">
        </div>
        <fieldset class="tertiary" style="margin-bottom: 0em;">
            <legend>Where</legend>
            <div
                class="fields"
                v-for="(expression,index) in where"
            >
                <div class="ts field">
                    <label>條件群組</label>
                    <input
                        type="number"
                        min="0"
                        @change="resortWhere"
                        v-model.number="expression.group"
                        v-number-limit-validate
                    >
                </div>
                <div class="ts field" :class="{disabled: index === 0}">
                    <label>邏輯運算子</label>
                    <select v-model="expression.logical_operator">
                        <option value="AND"> AND </option>
                        <option value="OR"> OR </option>
                    </select>
                </div>
                <div class="ts field">
                    <label>欄位代碼</label>
                    <input type="text" v-model="expression.field_code">
                </div>
                <div class="ts field">
                    <label>比較運算子</label>
                    <select v-model="expression.comparison_operator">
                        <option value="="> = </option>
                        <option value=">"> > </option>
                        <option value="<"> < </option>
                        <option value=">="> >= </option>
                        <option value="<="> <= </option>
                        <option value="<>"> <> </option>
                        <option value="LIKE"> LIKE </option>
                    </select>
                </div>
                <div class="ts field">
                    <label>值</label>
                    <input type="text" v-model="expression.value">
                </div>
                <i
                    class="remove large icon tr-remover"
                    @click="remove(index)"
                ></i>
            </div>
        </fieldset>
        <button class="ts fluid positive bottom attached button" @click="add">增加</button>
        <button class="ts fluid primary bottom attached button" @click="search">產生</button>
    </form>
    <div class="ts inverted segment" style="margin-top: 2.5rem" v-if="commands.length > 0">
        <div v-for="sql in commands"><kbd>@{{sql}}</kbd></div>
    </div>
    <div class="ts inverted negative segment" style="margin-top: 2.5rem" v-if="errors.length > 0">
        <div v-for="e in errors">@{{e}}</div>
    </div>
</div>
@endsection

@section('script')
@parent
<script>
const vueel = new Vue({
    el: "#el",
    data:{
        page_id: null,
        table_name: null,
        mode: 'page_id',
        ignore: null,
        where:[],
        commands:[],
        errors:[],
    },
    mounted(){
        this.where.push(this.initExpression());
    },
    computed: {

    },
    methods:{
        initExpression: function(){
            const result = {
                group: 0,
                logical_operator: "AND",
                field_code: null,
                comparison_operator: "=",
                value: null
            }
            return result;
        },
        add: function(event){
            if(event != undefined) event.preventDefault();
            this.where.push(this.initExpression());
            this.resortWhere();
        },
        remove: function(index){
            delete this.where[index];
            this.where = this.where.filter(() => true);
            if(this.where.length <= 0) this.add();
            this.resortWhere();
        },
        resortWhere: function(){
            this.where.sort(function (a, b){
                return a.group - b.group;
            });
        },
        search: function(event){
            event.preventDefault();
            // fullscreenDimmer.loading();

            const url = getURL('insert_sql');
            sendAPIRequest(url, "POST", {
                page_id: this.page_id,
                table_name: this.table_name,
                ignore: this.ignore,
                mode: this.mode,
                where: this.where
            }).then(result => {
                console.log(result);
                if(result.status){
                    this.commands = result.commands;
                    this.errors = [];
                }else{
                    this.commands = [];
                    this.errors = result.errors;
                }
                fullscreenDimmer.unloading();
            });

            /* for(let i = 0; i <= 10; i++){
                this.result.push('INSERT INTO a (1,2,3,4) VALUES (5,6,7,8)');
            } */
        },
    },
});
</script>
@endsection
