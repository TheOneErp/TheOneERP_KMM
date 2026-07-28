@extends('layouts.default')
@section('title', $commonTranslations['translation'])
@section('content')

<!-- Table -->
<div style="width:100%;overflow:hidden">
    <div style="width:100%;overflow:hidden">
        <div id="translationVueElement" style="transition:1s;">
            <div class="ts grid">
                <div class="eight wide column">
                    <h3>{{ $commonTranslations['translation'] }}</h3>
                </div>
                <div class="eight wide column">
                    <button
                        :class="{'ts primary active right floated button' : showAddForm ,'ts primary right floated button' : !showAddForm }"
                        @click="toggleAddForm">{{ $commonTranslations['add'] }}/{{ $commonTranslations['edit'] }}</button>
                </div>
            </div>



            <div class="ts form" v-if="showAddForm">
                <fieldset class="tertiary">
                    <legend>{{ $commonTranslations['add'] }}/{{ $commonTranslations['edit'] }}</legend>
                    <div class="ts fields">
                        <div class="field">
                            <label>{{ $commonTranslations['translation.type'] }} :</label>
                            <select class="ts basic dropdown" v-model="addForm.type">
                                <option value="message">@{{ typeTranslation.message }}</option>
                                <option value="rule">@{{ typeTranslation.rule }}</option>
                                <option value="var">@{{ typeTranslation.var }}</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>{{ $commonTranslations['translation.code'] }}</label>
                            <input type="text" v-model="addForm.code" />
                        </div>
                        <div class="field" v-for="language in languages">
                            <label>@{{language.language_name}}</label>
                            <input type="text" v-model="addForm.data[language.language_id]" />
                        </div>
                        <div class="field">
                            <label>&nbsp;</label>
                            <button class="ts small positive right floated button"
                                @click="add">{{ $commonTranslations['save'] }}</button>
                        </div>
                    </div>
                </fieldset>
                <br>
            </div>

            <div class="ts form">
                <fieldset class="tertiary">
                    <legend>{{ $commonTranslations['query'] }}</legend>
                    <div class="ts labeled action fluid input">
                        <select class="ts basic dropdown" v-model="filter.type">
                            <option value=""></option>
                            <option value="message">@{{ typeTranslation.message }}</option>
                            <option value="rule">@{{ typeTranslation.rule }}</option>
                            <option value="var">@{{ typeTranslation.var }}</option>
                        </select>
                        <input type="text" v-model="filter.code" />
                        <button class="ts primary button"
                            @click.prevent="query">{{ $commonTranslations['query'] }}</button>
                    </div>
                </fieldset>
            </div>

            <table class="ts stackable very compact celled table" v-if="Object.keys(data).length > 0" id="list">
                <thead>
                    <tr>
                        <th>{{ $commonTranslations['translation.type'] }}</th>
                        <th>{{ $commonTranslations['translation.code'] }}</th>
                        <th v-for="language in languages">@{{language.language_name}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="translation in data">
                        <td>@{{ typeTranslation[translation.type] }}</td>
                        <td>@{{ translation.code }}</td>
                        <td v-for="language in languages">
                            <div class="ts fluid input">
                                <input v-model="translation['data'][language.language_id].translation"
                                    @input="translationChanged(translation,language)">
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>{{ $commonTranslations['translation.type'] }}</th>
                        <th>{{ $commonTranslations['translation.code'] }}</th>
                        <th v-for="language in languages">@{{language.language_name}}</th>
                    </tr>
                </tfoot>
            </table>
            <button class="ts primary button" v-if="Object.keys(data).length > 0"
                @click="save">{{ $commonTranslations['save'] }}</button>
        </div>
    </div>
</div>

@endsection

@section('script')
@parent
<script>
    const languages = @json($languages);
    const typeTranslation = {
        message : "{{ $commonTranslations['translation.types.message'] }}",
        rule : "{{ $commonTranslations['translation.types.rule'] }}",
        var: "{{ $commonTranslations['translation.types.var'] }}"
    }
    const translationVueElement = new Vue({
        el: '#translationVueElement',
        data() {
            return {
                showAddForm : false,

                languages,
                typeTranslation,

                filter : {
                    type: null,
                    code: null
                },

                addForm: {
                    type: 'message',
                    code: '',
                    data: (()=>{let tmp = {} ; for(let language of languages){tmp[language.language_id] = ''};return tmp})()
                },

                data : {}
            }
        },
        mounted() {

        },
        computed: {
        },
        methods:{
            async query(){
                fullscreenDimmer.loading()
                let baseURL = new URL("{{route('system.translation.query.get')}}");
                baseURL.searchParams.append('filter',JSON.stringify(this.filter))
                return sendAPIRequest(baseURL,"GET",null).then(response => {
                    this.data = this.combineTranslationData(response)
                    fullscreenDimmer.unloading()
                })
            },
            combineTranslationData(rawData){
                let tmp = {}
                for(translation of rawData){
                    let translationCode = translation.translation_code
                    let translationType = translation.translation_type
                    let translationID = translation.translation_id
                    let translationLanguageID = translation.language_id
                    let translationValue = translation.translation
                    if(!(translationCode in tmp))
                        tmp[translationCode] = {
                            type : translationType,
                            code : translationCode,
                            data: {}
                        }
                    tmp[translationCode]['data'][translationLanguageID] = {
                        id : translationID,
                        translation : translationValue,
                        status : ""
                    }
                }

                for(let translationCode in tmp){
                    for(let language of languages){
                        if(tmp[translationCode]['data'][language.language_id] == undefined){
                            tmp[translationCode]['data'][language.language_id] = {
                                id : null,
                                translation : "",
                                status : "add"
                            }
                        }
                    }
                }

                return(tmp)
            },
            async translationChanged(translation,language){
                let currentStatus = translation['data'][language.language_id]['status'];
                translation['data'][language.language_id]['status'] = currentStatus == 'add' ? 'add' : 'update'
            },
            async save(){
                fullscreenDimmer.loading()

                let sendData = [];
                for(let translationCode in this.data){
                    let translation = this.data[translationCode]
                    for(let languageID in translation.data){
                        let translationData = translation.data[languageID]
                        translationData.translation_code = translationCode
                        translationData.translation_type = translation.type
                        translationData.language_id = languageID
                        if(translationData.status == 'update'){
                            sendData.push(Object.assign({},translationData))
                            translationData.status = ''
                        }else if(translationData.status == 'add'){
                            if(translationData.value != ""){
                                sendData.push(Object.assign({},translationData))
                                translationData.status = ''
                            }
                        }
                    }
                }

                return sendAPIRequest("{{route('system.translation.save.post')}}","POST",sendData).then(response => {
                    this.query()
                })
            },

            toggleAddForm(){
                this.showAddForm = !this.showAddForm
            },
            async add(){
                fullscreenDimmer.loading()

                let sendData = [];

                for(let language of languages ){
                    let languageID = language.language_id
                    if(this.addForm.data[languageID] != "")
                        sendData.push({
                            translation : this.addForm.data[languageID],
                            translation_code : this.addForm.code,
                            translation_type : this.addForm.type,
                            status: "add",
                            language_id : languageID
                        })
                    this.addForm.data[languageID] = ''
                }

                return sendAPIRequest("{{route('system.translation.save.post')}}","POST",sendData).then(response => {
                    this.filter.type = this.addForm.type
                    this.filter.code = this.addForm.code
                    this.query()
                })
            }

        }
    })
</script>
@endsection
