window.injects = {
    injectOnInit: [],
    injectOnAdd: [],
    injectOnView: [],
    injectOnCopy: [],
    injectOnEdit: [],
    injectOnReferenceWrite: [],
    injectOnRowAdd: [],
    injectOnRowDelete: [],
    injectOnHeadInput: [],
    injectOnHeadChange: [],
    injectOnHeadClick: [],
    injectOnBodyInput: [],
    injectOnBodyChange: [],
    injectOnBodyClick: []
}

window.cache = {
    reference: {

    }
}

window.defaultSelectOptions = {
    conditionOptions: [{
            value: 'and',
            text: window.commonTranslations['filter.condition.and']
        },
        {
            value: 'or',
            text: window.commonTranslations['filter.condition.or']
        }
    ],
    operatorOptions: [{
            value: '=',
            text: window.commonTranslations['filter.operator.=']
        },
        {
            value: '!=',
            text: window.commonTranslations['filter.operator.!=']
        },
        {
            value: '>',
            text: window.commonTranslations['filter.operator.>']
        },
        {
            value: '>=',
            text: window.commonTranslations['filter.operator.>=']
        },
        {
            value: '<',
            text: window.commonTranslations['filter.operator.<']
        },
        {
            value: '<=',
            text: window.commonTranslations['filter.operator.<=']
        },
        {
            value: 'like',
            text: window.commonTranslations['filter.operator.like']
        },
        {
            value: 'not like',
            text: window.commonTranslations['filter.operator.not like']
        }
    ]
}

events = {
    Change: new Event("change")
};

function createInvisibleDiv() {
    let el = document.createElement('div');
    el.style.width = 'auto';
    el.style.display = 'inline-block';
    el.style.visibility = 'hidden';
    el.style.position = 'fixed';
    el.style.overflow = 'auto';
    return el;
}

// Fullscreen dimmer
fullscreenDimmer = {
    loading() {
        document.getElementById("fullscreenDimmer").className = 'ts active dimmer';
    },
    unloading() {
        document.getElementById("fullscreenDimmer").className = 'ts dimmer';
    },
    text(value) {
        const loader = document.getElementById("fullscreenDimmer").querySelector("div.loader");
        if (value === "") {
            loader.className = 'ts loader';
        } else {
            loader.className = 'ts text loader';
        }
        loader.innerText = value;
    },
    untext() {
        this.text('');
    },
}

// Utils
function sendAPIRequest(url, method, data) {
    let headers = {
        'X-CSRF-TOKEN': window.csrfToken
    };
    if (data != undefined && data != null) {
        if (data.constructor == Object || data.constructor == Array) {
            headers['Content-Type'] = "application/json";
            data = JSON.stringify(data);
        }
    }
    return fetch(url, {
            method: method,
            headers,
            redirect: 'follow',
            body: data == null ? null : data
        })
        .then(_ => {
            return _.json()
        })
}

function timeout(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * @param obj something to clone
 *
 * @return the clone of obj
 */
function deepClone(obj) {
    try {
        return JSON.parse(JSON.stringify(obj));
    } catch (error) {
        if (typeof obj === "object") {
            let result;
            if (obj.constructor === Array) {
                result = [];
            } else {
                result = {};
            }
            for (let key in obj) {
                result[key] = deepClone(obj[key]);
            }
            return result;
        } else {
            return obj;
        }
    }
}

/**
 * @param a something to be compared
 * @param b another to be compared
 * @param strict want to strict comparison
 *
 * @return comparison result
 */
function equalityComparison(a, b, strict = false) {
    let result = true;
    try {
        if (strict) {
            result = JSON.stringify(a) === JSON.stringify(b);
        } else {
            result = JSON.stringify(a) == JSON.stringify(b);
        }
    } catch (error) {
        if (typeof a === "object" && typeof b === "object") {
            for (let key in a) {
                if (b[key] !== undefined) {
                    equalityComparison(a[key], b[key], strict);
                } else {
                    result = false;
                    break;
                }
            }
        } else if (typeof a === typeof b) {
            if (strict) {
                result = a === b;
            } else {
                result = a == b;
            }
        } else {
            result = false;
        }
    }
    return result;
}

function convertArrayToObject(array, key) {
    const initialValue = {};
    return array.reduce((obj, item) => {
        return {
            ...obj,
            [item[key]]: item,
        };
    }, initialValue);
}

function compareToSort(a, b, order) {
    if (a > b) {
        if (order === "ASC") {
            return 1;
        } else {
            return -1;
        }
    } else if (a == b) {
        return 0;
    } else {
        if (order === "ASC") {
            return -1;
        } else {
            return 1;
        }
    }
}

function sortArrayByCustomKey(array, key = [], order = "ASC") {
    if (array.constructor === Array) {
        if (typeof key === "string" || (typeof key === "number" && Number.isInteger(key))) {
            key = [key];
        }

        if (key.constructor !== Array) {
            console.error("The second parameter must be an array, string or integer.");
        } else {
            array.sort((a, b) => {
                let compared = 0;
                if ((a.constructor === Array || a.constructor === Object) && (b.constructor === Array || b.constructor === Object)) {
                    for (let k of key) {
                        compared = compareToSort(a[k], b[k], order);
                        if (compared === 0) {
                            continue;
                        } else {
                            break;
                        }
                    }
                } else {
                    console.error("The item which want to be compared must be array or object.");
                }
                return compared;
            });
        }
    } else {
        console.error("The first parameter must be an array.");
    }
}

function sortDatasetByCustomKey(dataset, key = [], order = "ASC") {
    if (typeof key === "string" || (typeof key === "number" && Number.isInteger(key))) {
        key = [key];
    }

    dataset.sort((a, b) => {
        let compared = 0;

        for (let k of key) {
            compared = compareToSort(a.data[k], b.data[k], order);
            if (compared === 0) {
                continue;
            } else {
                break;
            }
        }

        return compared;
    });
}

function isEmpty(...values) {
    let result = [];
    for (let value of values) {
        let determination = false;
        if (value === undefined) {
            determination = true;
        } else if (value === null) {
            determination = true;
        } else if (typeof value === "string" && (value === "" || value.trim() === "")) {
            determination = true;
        } else if (typeof value === "object") {
            if (value.constructor === Array) {
                determination = equalityComparison(value, [], true);
            } else if (value.constructor === Object) {
                determination = equalityComparison(value, {}, true);
            }
        }
        result.push(determination);
    }

    return result.find(x => x == false) === undefined;
}

function isAnyEmpty(...values) {
    for (let value of values) {
        if (isEmpty(value)) return true;
    }
    return false;
}

function unionString(string, union, delimiter = ",") {
    string = string.toString();
    if (typeof union === "object") {
        for (let i in union) {
            string = unionString(string, union[i], delimiter);
        }
    } else {
        if (!isEmpty(string)) {
            string += delimiter;
        }
        string += union;
    }

    return string;
}

function getURL(URL) {
    return window.baseURL + URL;
}

function getToken() {
    return window.csrfToken;
}


// Date picker
flatpickr.localize(flatpickr.l10ns.zh_tw);

// Vue

const testTextWidthEl = createInvisibleDiv()
document.body.appendChild(testTextWidthEl)

const inputAutoWidthFixed = {
    'date': 'YYYY-MM-DD',
    'time': '00:00:00',
    'datetime': 'YYYY-MM-DD 00:00:00'
}

Vue.directive('input-auto-width', {
    bind: function(bindEl, binding, vnode) {
        vnode.context.$nextTick(() => {
            const setWidth = (el, text) => {
                testTextWidthEl.innerText = text
                el.style.setProperty('width', `${(testTextWidthEl.clientWidth < 30 ? 30 : testTextWidthEl.clientWidth) + 50}px`, 'important')
            }

            const getParentsClassName = (currentElement, currentClassName) => {
                const parentElement = currentElement.parentNode
                if (parentElement == undefined || parentElement == null || parentElement.className.includes("grid") || parentElement.className.includes("ts form")) {
                    return currentClassName
                }
                return getParentsClassName(parentElement, currentClassName + " " + parentElement.className)
            }

            const className = getParentsClassName(bindEl, "")
            if (className.includes("wide column")) {
                bindEl.parentNode.style.width = 'inherit';
                bindEl.style.width = 'inherit';
                return
            }

            // Fixed width
            if (binding.value && binding.value.type in inputAutoWidthFixed) {
                setWidth(bindEl, inputAutoWidthFixed[binding.value.type])
            } else {
                bindEl.handler = (el = null, value = null) => {
                    if (el == null || !(el.tagName)) el = bindEl
                    if (value == null) value = el.value
                    setWidth(el, value)
                }

                bindEl.addEventListener('input', bindEl.handler);
                bindEl.addEventListener('change', bindEl.handler);
                bindEl.handler();
            }
        });
    },
    update: function(bindEl, binding, vnode) {
        if (bindEl.handler)
            vnode.context.$nextTick(() => {
                bindEl.handler(bindEl, bindEl.value)
            })
    },
    unbind: function(bindEl, binding, vnode) {
        if (bindEl.handler)
            bindEl.removeEventListener('input', bindEl.handler);
    }
});

Vue.directive('number-limit-validate', {
    bind: function(el, binding, vnode) {
        this.numberLimitValidate = () => {
            const value = isEmpty(el.value) ? "" : Number(el.value);
            const max = isEmpty(el.max) ? "" : Number(el.max);
            const min = isEmpty(el.min) ? "" : Number(el.min);
            let toPut = value;
            if (!isEmpty(value)) {
                if (!isEmpty(max) && value > max) {
                    el.value = max;
                } else if (!isEmpty(min) && value < min) {
                    el.value = min;
                }
                if (Number(el.value) !== value) {
                    el.dispatchEvent(new Event("input"));
                }
            }
        }
        this.numberNullValidate = () => {
            const value = isEmpty(el.value) ? "" : Number(el.value);
            const min = isEmpty(el.min) ? 0 : Number(el.min);
            if (isEmpty(value)) {
                el.value = min;
                el.dispatchEvent(new Event("input"));
            }
        }
        const modifiers = binding.modifiers;
        el.addEventListener('input', numberLimitValidate);
        if (modifiers.nullable == undefined || !modifiers.nullable) {
            el.addEventListener('change', numberNullValidate);
        }
        numberLimitValidate();
        numberNullValidate();
    },
    unbind: function(el, binding, vnode) {
        el.removeEventListener('input', this.numberLimitValidate);
        el.removeEventListener('change', this.numberNullValidate);
    }
});

Vue.directive('max-min-validate', {
    bind: function(el, binding, vnode) {
        this.maxMinValidate = () => {
            const field = el.parentElement;
            const fields = field.parentElement;
            const max = fields.querySelector(".max");
            const min = fields.querySelector(".min");
            if (!isAnyEmpty(max, min)) {
                const maxValue = max.value !== "" ? Number(max.value) : "";
                const minValue = min.value !== "" ? Number(min.value) : "";
                if (!isAnyEmpty(maxValue, minValue) && minValue > maxValue) {
                    el.value = el == max ? minValue + 1 : maxValue - 1;
                    el.dispatchEvent(new Event("input"));
                    errorEl = detailError.cloneNode(true);
                    errorEl.querySelector("p").innerText = translations.min_bigger;
                    fields.parentElement.appendChild(errorEl);
                    setTimeout(() => {
                        fields.parentElement.removeChild(errorEl);
                    }, 2000);
                }
            }
        }
        el.addEventListener('change', maxMinValidate);
    },
    unbind: function(el, binding, vnode) {
        el.removeEventListener('change', this.maxMinValidate);
    }
});

Vue.component('universal-reference', {
    template: `
        <div>
            <div v-if="field.field_options.reference.type == 'select'" style="display:flex;align-items:center;" class="glm webkit fill width">
                <select
                    class="glm webkit fill width"
                    :style="field.field_style"
                    :value="value"
                    :disabled="field.field_readonly"
                    ref='select'
                    @change='handleSelectChange'
                >
                    <option
                        v-for="data in referenceData.data.datas"
                        :value="data[field.field_options.reference.select_field]"
                    >{{ data[field.field_options.reference.select_field] }}</option>
                </select><button v-if="!field.field_readonly" :disabled="field.field_disabled" type="button"  class="ts primary icon button" @click.prevent="refreshdata"><i class="refresh icon"></i></button>
                <span v-if="referenceData.loading" class="ts active inline tiny loader" style="margin-left:15px;"></span>
            </div>
            <div v-else>
                <div class="ts action input" @click="$emit('click')">
                    <input v-if="field.field_options.reference.type != 'readonly'" :style="field.field_style" v-input-auto-width type="text" v-bind:value="value" :readonly="field.field_readonly" @input="handleInput" @focusin="handleFocusIn" @focusout="handleFocusOut">
                    <button v-if="!field.field_readonly" :disabled="field.field_disabled" type="button"  class="ts primary icon button" @click.prevent="openReferenceModal"><i class="search icon"></i></button>
                </div>
                <div class="ts modals dimmer">
                    <dialog :id="randomID" class="ts closable tiny modal" data-v-glm-css-filled-modal>
                        <div class="ts active dimmer" v-if='referenceData.loading'>
                            <div class="ts loader"></div>
                        </div>

                        <div class="content" v-if='!referenceData.loading && referenceData.data != null'>

                            <div class="ts inverted icon negative message" v-if="message != null || referenceErrors.length != 0">
                                <i class="remove circle icon"></i>
                                <div class="content">
                                    <p v-if="message != null">{{message.title}} : {{message.text}}</p>
                                    <p v-for="referenceError in referenceErrors">{{referenceError.message}}</p>
                                </div>
                            </div>

                            <button type="button" class="ts primary button" :class="{'active':showFilterForm}"
                                @click.prevent="() => {showFilterForm = !showFilterForm}">{{ commonTranslations.filter }}</button>
                            &nbsp;
               
                    
                            <button type="button" class="ts primary button" @click.prevent="refreshdata">更新資料</button>
                             <div v-if="showFilterForm">
                                <br>

                              <div class="ts form" @keydown.enter.prevent="addFilterAndQueryData()">
                                    <fieldset class="tertiary">
                                        <legend>{{ commonTranslations.filter }}</legend>
                                        <div class="ts fields">
                                            <div class="field">
                                                <label>{{ commonTranslations['filter.group'] }} :</label>
                                                <input type="number" v-number-limit-validate v-model="filterForm.group" min=0 />
                                            </div>
                                            <div class="field">
                                                <label>{{ commonTranslations['filter.condition'] }} :</label>
                                                <select class="ts basic dropdown" v-model="filterForm.condition">
                                                    <option v-for="option in filterForm.conditionOptions" :value="option.value">
                                                    {{ option.text }}</option>
                                                </select>
                                            </div>
                                            <div class="field">
                                                <label>{{ commonTranslations['field'] }} :</label>
                                                <select class="ts basic dropdown" v-model="filterForm.field">
                                                    <option v-for="option in filterForm.fieldOptions" :value="option.value">
                                                        {{ option.text }}</option>
                                                </select>
                                            </div>
                                            <div class="field">
                                                <label>{{ commonTranslations['filter.operator'] }} :</label>
                                                <select class="ts basic dropdown" v-model="filterForm.operator">
                                                    <option v-for="option in filterForm.operatorOptions" :value="option.value">
                                                        {{ option.text }}</option>
                                                </select>
                                            </div>
                                            <div class="field">
                                                <label>{{ commonTranslations['content'] }} :</label>
                                                <div >
                                           <universal-field 
                                                v-model="filterForm.value"
                                                :field="field.field_options.reference.fields"
                                                :options="{ mode: 'search' }"
                                                >
                                        </universal-field>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label>&nbsp;</label>
                                                  <div>
                                                    <button class="ts small primary right floated button" @click.prevent="addFilterAndQueryData()">{{ commonTranslations['add'] + commonTranslations['filter'] }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                            <br>
                        </div>

                        <!-- Filters -->
                        <div v-if="referenceData.filters.filter(filter => !filter.hidden).length > 0" class="ts stackable grid">
                            <div class="inline four wide column"
                                v-for="filter in referenceData.filters"
                                v-if=!filter.hidden>
                                <div class="ts primary card">
                                    <div class="content">
                                        <div class="description">
                                            <button class="ts close button" @click.prevent='deleteFilterAndQueryData(filter)'></button>
                                            <pre>{{filter.text}}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>
                        <table class="ts sortable selectable stackable celled striped padded table" id="list" style="padding:1em;">
                            <thead>
                                <tr>
                                    <th v-if="field.field_options.reference.type == 'multiple'">
                                        <input type="checkbox" name="checkAll" v-show="false">
                                    </th>
                                 <th 
                                    v-for="[field_code, field] of Object.entries(referenceData.fields)" 
                                    :key="field_code" 
                                    v-if="field.show" 
                                    @click.prevent="handleSortClick(field)">
                                        {{ field.translation }}
                                         <i v-if="sortBy.findIndex(sortBy => sortBy.field == field.field_code) != -1"
                                        :class="{'chevron circle down icon' : sortBy.find(sortBy => sortBy.field == field.field_code).order == 'asc' , 'chevron circle up icon' : sortBy.find(sortBy => sortBy.field == field.field_code).order == 'desc'}">
                                        {{ sortBy.findIndex(sortBy => sortBy.field == field.field_code) + 1 }}
                                    </i>     
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data,i) in referenceData.data.datas"
                                    @click.prevent="emitReferenceClicked(data)"

                                    >
                                    <td v-if="field.field_options.reference.type == 'multiple'">
                                        <input type="checkbox" v-model="data.isChecked" @click.prevent="referenceCheckbox(data)">
                                    </td>
                                    <td v-for="[field_code,field] of Object.entries(referenceData.fields)" v-if="field.show">
                                        {{ data[field_code] }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br>

                        <template v-if="field.field_options.reference.type == 'multiple'">
                            <div class="ts inverted segment" style="background:none;border-style:none;">
                                <button class="ts info button" @click.prevent="referenceChoose">挑選</button>
                            </div>
                        </template>
                    </dialog>
                </div>
            </div>
        </div>
    `,
    props: ['field', 'value', 'dataset'],
    async mounted() {

        this.initReference()
        if (this.field.field_options.reference.front_field.enabled) {
            await this.addFrontFieldFilter()
            for (filter of this.referenceData.filters) {
                this.$watch(filter.path, (n, o) => {
                    this.initReference()
                })
            }
        }
        this.refreshdata();
    },
    data() {
        let fieldOptions = [{
            value: '*',
            text: window.commonTranslations['filter.all_field']
        }];
        return {
            filters : [],
            sortBy: [],
            filterForm: {
                group: 0,
                field: '*',
                condition: 'or',
                operator: 'like',
                value: '',
                fieldOptions,
                conditionOptions: window.defaultSelectOptions.conditionOptions,
                operatorOptions: window.defaultSelectOptions.operatorOptions
            },

            commonTranslations: window.commonTranslations,
            message: null,
            referenceErrors: [],
            options: [],
            frontFieldTranslation: "",
            focusValue: "",

            sending: false,

            editable: true,
            showFilterForm: false,
            referenceData: {
                loading: false,
                fields: convertArrayToObject(this.field.field_options.reference.fields, 'field_code'),
                data: {
                    datas: []
                },
                filters: []
            },
            randomID: "universal_" + Math.random().toString().slice(3, 12) + Math.random().toString().slice(3, 12)
        }
    },
    methods: {
        async initReference() {
            if (this.field.field_options.reference.type == 'select') {
                let frontFieldEnabled = this.field.field_options.reference.front_field.enabled
                let oldValue = this.value
                let selectField = this.field.field_options.reference.select_field

                if (!frontFieldEnabled && (this.sending || this.referenceData.data.datas.length > 0)) return true

                if (frontFieldEnabled) {
                    let frontFieldResult = this.addFrontFieldFilter()
                    if (!frontFieldResult)
                        return false
                }

                let originalFilterValues = this.getFilterValues(this.referenceData.filters);
                let preventMultiRequest = await new Promise((resolve, reject) => {
                    setTimeout(() => {
                        let newFilterValues = this.getFilterValues(this.referenceData.filters);
                        resolve(originalFilterValues == newFilterValues)
                    }, 500)
                });
                if (!preventMultiRequest) return false

                this.sending = true
                await this.getReferenceData();
                this.sending = false

                let responseArray = this.referenceData.data.datas
                let newOptions = responseArray.map(el => el[selectField]);

                let newData = (!newOptions.includes(oldValue) && oldValue != "") ? "" : responseArray.find(item => oldValue == item[selectField])
                if (this.$refs.select)
                    this.$refs.select.value = oldValue

            } else if (this.field.field_options.reference.type == 'list' && this.value) {
                let originalFilterValues = this.getFilterValues(this.referenceData.filters);
                let preventMultiRequest = await new Promise((resolve, reject) => {
                    setTimeout(() => {
                        let frontFieldResult = this.addFrontFieldFilter()
                        let newFilterValues = this.getFilterValues(this.referenceData.filters);
                        resolve(originalFilterValues != newFilterValues && originalFilterValues != "")
                    }, 500)
                });
                if (!preventMultiRequest) return false
                this.$emit("reference", this.getEmptyData(), this.referenceData.fields)
            }
        },

        openModal() {
            ts("#" + this.randomID).modal({
                approve: '.不可能存在的按鈕',
                deny: '.不可能存在的按鈕'
            }).modal("show")
        },
        closeModal() {
            ts("#" + this.randomID).modal("hide")
        },
        handleSortClick(field) {
            const fieldCode = field.field_code;
            const sortByIndex = this.sortBy.findIndex(sortBy => sortBy.field === fieldCode);
        
            if (sortByIndex === -1) {
                // 如果字段不在 sortBy 中，添加新的排序条件，默认升序
                this.sortBy.push({ field: fieldCode, order: "asc" });
            } else {
                const currentSort = this.sortBy[sortByIndex];
                if (currentSort.order === "asc") {
                    // 切换为降序
                    currentSort.order = "desc";
                } else if (currentSort.order === "desc") {
                    // 第三次点击，从 sortBy 中移除
                    this.sortBy.splice(sortByIndex, 1);
                }
            }
        
            // 根据 sortBy 数组对数据进行排序
            this.referenceData.data.datas.sort((a, b) => {
                for (let sortCondition of this.sortBy) {
                    const field = sortCondition.field;
                    const order = sortCondition.order;
        
                    let aValue = a[field];
                    let bValue = b[field];
        
                    // 针对特定字段（如 id）进行数字转换
                    if (field === "id") {
                        aValue = Number(aValue) || 0;
                        bValue = Number(bValue) || 0;
                    }
                    
                    if ((aValue < bValue) || (aValue == null && bValue != null)) return order === "asc" ? -1 : 1;
                    if ((aValue > bValue) || (bValue == null && aValue != null)) return order === "asc" ? 1 : -1;
                }
                return 0; // 如果所有排序条件都相等
            });
        
           // console.log("Updated sortBy array:", this.sortBy);
         //   console.log("Sorted data:", this.referenceData.data.datas);
        },
        referenceCheckbox(data){
            //因為前端部分tr和checkbox會同時點2次 延遲0.1秒在做判斷在切換
            var e = event;
            setTimeout(()=>{
                if($(e.path).find("tr.info input[type=checkbox]").prop("checked") == true){
                    $(e.path).find("tr.info input[type=checkbox]").prop("checked", false);
                    data.isChecked = false;
                } else {
                    $(e.path).find("tr.info input[type=checkbox]").prop("checked", true);
                    data.isChecked = true;
                }
            },1);
        },
        referenceChoose(){
            var data = null,fields = null;

            //copy相同的欄位
            data = Object.assign({},this.referenceData.data.datas[0]);
            delete data.isChecked;
            fields = this.referenceData.fields;

            //各欄位設空值
            for(var i in data){
                data[i] = '';
            }

            //檢查是否有複選回傳該筆資料
            var datas = this.referenceData.data.datas.filter((item, i)=>{
                if(item.isChecked){
                    for(let [field_code,field] of Object.entries(fields)){
                        //比對對象有目標 將資料存取
                        if(field.target && item[field_code] !== undefined){
                            if(!(item[field_code] == '' || item[field_code] == null)){
                                data[field_code] += item[field_code]+';';
                            }
                        }
                    }
                }

                return item.isChecked;
            });

            //將資料帶回該筆欄位
            this.$emit("reference", data, this.referenceData.fields);

            this.closeModal();
        },
        // Reference
        getFilterValues(filters) {
            let tmp = ""
            for (let filter of filters)
                if (filter.value != undefined)
                    tmp = tmp + filter.value
            return tmp
        },
        openReferenceModal() {

            if (this.field.field_options.reference.front_field.enabled) {
                let frontFieldResult = this.addFrontFieldFilter()
                if (!frontFieldResult) {
                    alert(commonTranslations['reference.error.required_front_field'].replace(":field", this.frontFieldTranslation))
                    return
                }
            }

            this.openModal()

            this.getReferenceData()
        },
        addFrontFieldFilter() {
            let result = true

            this.frontFieldTranslation = ""
            let originalFilters = []
            let originalFilterValues = this.getFilterValues(this.referenceData.filters)

            // Remove old front filters
            let lastFilterIndex = this.referenceData.filters.filter(filter => filter.group == null)
            if (lastFilterIndex.length > 0) {
                originalFilters = this.referenceData.filters.filter(filter => filter.group != null)
                this.referenceData.filters = originalFilters
            }

            let frontFieldFilters = this.field.field_options.reference.front_field.fields;
            // Old reference workaround
            if (frontFieldFilters == undefined) frontFieldFilters = [this.field.field_options.reference.front_field]

            for (let frontFieldOptions of frontFieldFilters) {
                let frontFormID = frontFieldOptions.form_id
                let frontFieldCode = frontFieldOptions.field_code
                let targetFilterKey = frontFieldOptions.target ? frontFieldOptions.target : frontFieldCode

                let addFilter = (dataset, path = 'dataset') => {
                    if (dataset.form_id == frontFormID) {
                        if (dataset.data[frontFieldCode] !== undefined && dataset.data[frontFieldCode] !== null) {
                            this.referenceData.filters.push({
                                id: Math.random().toString(),
                                group: null,
                                condition: 'and',
                                field: targetFilterKey,
                                operator: "=",
                                value: dataset.data[frontFieldCode],
                                hidden: true,
                                path: path + '.data.' + frontFieldCode
                            })
                            return true
                        } else {
                            this.referenceData.filters.push({
                                id: Math.random().toString(),
                                group: null,
                                hidden: true,
                                path: path + '.data.' + frontFieldCode
                            })
                            this.frontFieldTranslation = dataset.schema.fields[frontFieldCode].translation + " " + this.frontFieldTranslation
                            return false
                        }
                    } else if (dataset.parent) {
                        return addFilter(dataset.parent, path + '.parent')
                    } else {
                        return false
                    }
                }
                result = (result == false) ? result : addFilter(this.dataset)
            }


            if (this.field.field_options.reference.type == 'select') {
                let newFilterValues = this.getFilterValues(this.referenceData.filters)
                return (newFilterValues != originalFilterValues) ? result : false
            } else {
                return result
            }
        },
        async getReferenceData() {
            this.referenceData.loading = true
            let url = window.urls.getReferenceData
            url = url.replace("-field_id-", this.field.field_id)
            url = new URL(url);
            url.searchParams.append('filters', JSON.stringify(this.referenceData.filters))
            url.searchParams.append('sortby',JSON.stringify(this.sortBy))
            // console.log(url);
            if (window.cache.reference[url.toString()] != undefined)
                var data = await window.cache.reference[url.toString()];
            else {
                var data = sendAPIRequest(url, "GET", null).catch(error => {
                    delete window.cache.reference[url.toString()]
                });
                window.cache.reference[url.toString()] = data;
                data = await data;
                if (this.field.field_options.reference.type == 'select') {
                    if (data.datas)
                        data.datas.unshift(this.getEmptyData())
                }
            }

            //如果跳窗是複選模式-增加checkbox欄位
            if(this.field.field_options.reference.type == 'multiple'){
                data.datas.forEach((item, i)=>{
                    item.isChecked = false;
                });
            }

            this.referenceData.loading = false
            this.referenceErrors = [];
            if (data.status) {
                this.referenceData.data = data
                if (this.filterForm.fieldOptions.length == 1) {
                    for (let [field_code, field] of Object.entries(data.fields)) {
                        this.referenceData.fields[field_code].translation = field.translation
                        if (field.show)
                            this.filterForm.fieldOptions.push({
                                value: field_code,
                                text: field.translation
                            })
                    }
                }
            } else {
                this.referenceErrors = data.errors;
            }


            return
        },
        async refreshdata() {
            this.referenceData.loading = true
            let url = window.urls.getReferenceData
            url = url.replace("-field_id-", this.field.field_id)
            url = new URL(url);
            url.searchParams.append('filters', JSON.stringify(this.referenceData.filters))

                var data = sendAPIRequest(url, "GET", null).catch(error => {
                    delete window.cache.reference[url.toString()]
                });
                window.cache.reference[url.toString()] = data;
                data = await data;
                if (this.field.field_options.reference.type == 'select') {
                    if (data.datas)
                        data.datas.unshift(this.getEmptyData())
                }


            //如果跳窗是複選模式-增加checkbox欄位
            if(this.field.field_options.reference.type == 'multiple'){
                data.datas.forEach((item, i)=>{
                    item.isChecked = false;
                });
            }

            this.referenceData.loading = false
            this.referenceErrors = [];
            if (data.status) {
                this.referenceData.data = data
                if (this.filterForm.fieldOptions.length == 1) {
                    for (let [field_code, field] of Object.entries(data.fields)) {
                        this.referenceData.fields[field_code].translation = field.translation
                        if (field.show)
                            this.filterForm.fieldOptions.push({
                                value: field_code,
                                text: field.translation
                            })
                    }
                }
            } else {
                this.referenceErrors = data.errors;
            }


            // console.log("referenceData.fields:", this.referenceData.fields);
            return
        },
        emitReferenceClicked(data) {
            if(this.field.field_options.reference.type == 'multiple') {

                if($(event.target)[0].localName == "input"){
                    $(event.target)[0].localName;
                    let a = data.isChecked;
                    setTimeout(() => {
                        if(a == true){
                            data.isChecked = false;
                        } else {
                            data.isChecked = true;
                        }
                      }, "1");

                }else if($(event.target)[0].localName == "td"){
                    console.log("hi");
                    if(data.isChecked == true){
                        data.isChecked = false;
                    } else {
                        data.isChecked = true;
                    }
                }

            } else if (this.field.field_options.reference.type != 'readonly') {
                this.$emit("reference", data, this.referenceData.fields)
                this.closeModal();
            }
        },

        //Filters
        addFilter() {
            const getOptionText = (options, value) => {
                return (options.find(option => option.value == value).text)
            }
            if (this.filterForm.condition != '' && this.filterForm.field != '' && this.filterForm.operator != '' && this.filterForm.value != '' && this.filterForm.group !== '') {
                let tmp = {
                    id: Math.random().toString(),
                    group: this.filterForm.group,
                    condition: this.filterForm.condition,
                    field: this.filterForm.field,
                    operator: this.filterForm.operator,
                    value: this.filterForm.value,
                    text: `${commonTranslations['filter.group']} : ${this.filterForm.group}\n${getOptionText(this.filterForm.conditionOptions,this.filterForm.condition)} ${getOptionText(this.filterForm.fieldOptions,this.filterForm.field)} ${getOptionText(this.filterForm.operatorOptions,this.filterForm.operator)} ${this.filterForm.value}`
                }

                if (this.filterForm.operator == "like" || this.filterForm.operator == "not like") {
                    tmp.value = `%${this.filterForm.value}%`;
                }
                this.referenceData.filters.push(tmp)
            } else {
                this.showMessage('error', commonTranslations['warning'], commonTranslations['messages.fillOrSelectAll'])
            }
        },
        deleteFilter(filter) {
            delete this.referenceData.filters[this.referenceData.filters.findIndex(el => el.id == filter.id)]
            this.referenceData.filters = this.referenceData.filters.filter(() => true)
        },
            addFilterAndQueryData() {
                this.addFilter();
                this.$nextTick(() => {
                    this.getReferenceData();
                });
            },
                 deleteFilterAndQueryData(filter){
                delete this.referenceData.filters[this.referenceData.filters.findIndex(el => el.id == filter.id)]
                this.referenceData.filters = this.referenceData.filters.filter(() => true)
                this.$nextTick(() => {
                    this.getReferenceData();
                });
            },
        showMessage(type, title, text) {
            this.message = {
                type,
                title,
                text
            }
            setTimeout(_ => {
                this.message = null
            }, 3000)
        },

        // Events
        handleFocusIn() {
            this.focusValue = this.value
        },
        handleFocusOut() {
            if (this.focusValue != this.value) {
                this.$emit('change', this.value)
                this.handleChange()
            }
        },
        handleInput(event) {
            this.$emit("input", event.target.value)
        },
        async handleChange(event) {
            this.$emit("change", this.value)
            let fromField = Object.values(this.referenceData.fields).find(field => field.target == this.field.field_code)
            if (this.value != "" && fromField) {
                this.referenceData.filters = [{
                    id: Math.random().toString(),
                    group: 0,
                    condition: 'and',
                    field: fromField.field_code,
                    operator: "=",
                    value: this.value,
                    hidden: true
                }]
                await this.getReferenceData();
                this.referenceData.filters = []
                if (this.referenceData.data.datas.length != 0) {
                    return this.$emit("reference", this.referenceData.data.datas[0], this.referenceData.fields);
                }
            }
            this.$emit("reference", this.getEmptyData(), this.referenceData.fields);
        },
        getEmptyData() {
            let emptyData = {}
            Object.keys(this.referenceData.fields).forEach(field => emptyData[field] = "")
            return emptyData
        },
        handleSelectChange(event) {
            let selectField = this.field.field_options.reference.select_field
            let data = this.referenceData.data.datas.find(data => data[selectField] == this.$refs.select.value)
            if (data)
                this.$emit("reference", data, this.referenceData.fields);
            this.closeModal();
        },

   
    }
})

Vue.component('universal-reference-page', {
    template: `
    <div v-if="field">
        <button class="ts primary icon button" :style="field.field_style" @click.prevent="openModal() && $emit('click') "><i class="edit icon"></i></button>
        <div class="ts modals dimmer">
            <dialog :id="randomID" class="ts closable tiny modal" data-v-glm-css-filled-modal>
                <div class="content">
                    <universal-form ref='form' :pageData="field.pageData" @input="handleInput" @close="closeModal()"></universal-form>
                </div>
            </dialog>
        </div>
    </div>
    `,
    props: ['field', 'value', 'dataset', 'mode', 'rootDataset'],
    data() {
        return {
            randomID: "universal_" + Math.random().toString().slice(3, 12) + Math.random().toString().slice(3, 12)
        }
    },
    watch: {
        mode() {
            this.init();
        }
    },
    mounted() {
        this.init()
    },
    methods: {
        async init() {
            if (this.$refs.form) {
                this.$refs.form.openReferencePageData(this.value, {
                    mode: this.mode ? this.mode : "edit",
                    parentDataset: this.rootDataset,
                    parentVue: this.rootDataset ? this.rootDataset.vue : null
                })
            }
        },
        openModal() {
            ts("#" + this.randomID).modal({
                approve: '.不可能存在的按鈕',
                deny: '.不可能存在的按鈕'
            }).modal("show")
        },
        closeModal() {
            ts("#" + this.randomID).modal("hide")
        },

        // Events
        handleInput(dataset) {
            this.$emit("input", dataset)
        },
      
    }
})

Vue.component('universal-field', {

    template: `
        <span v-if="options.mode == 'view'" >
            <span v-if=" fieldData.field_type == 'boolean'" @click="$emit('click')" :style="fieldData.field_style">
                {{ value == 1 ? "是" : "否" }}
            </span>
            <span v-else-if=" fieldData.field_type == 'checkboxes'" @click="$emit('click')" :style="fieldData.field_style">
                {{ value.join(',') }}
            </span>
            <span v-else-if=" fieldData.field_type == 'file' && value != null && value != undefined" @click="$emit('click')" >
                {{ value }} &nbsp;
                <button
                    :style="fieldData.field_style"
                    class="ts primary icon button"
                    @click.prevent="handleDownloadFile">
                    <i class="cloud download icon" />
                </button>
            </span>
            <universal-reference
                v-else-if=" fieldData.field_type == 'reference' &&  fieldData.field_options.reference.type == 'readonly'"

                :field="fieldData"
                :dataset='dataset'
                :readonly="fieldData.field_readonly"

                :value="value"
                @input="$emit('input', $event);"
                @change="$emit('change', $event)"
                @reference="emitReferenceClicked"
                @click="$emit('click')"
            />
            <universal-reference-page
                v-else-if=" fieldData.field_type == 'reference_page'"
                ref='referencePage'

                :field="fieldData"
                :dataset='dataset'
                mode="view"

                :value="value"
            />
            <span v-else @click="$emit('click')">
                {{ value }}
            </span>
        </span>
        <span v-else-if='field' class="glm webkit fill width">
            <input
                v-if=" fieldData.field_type == 'string'"

                v-input-auto-width
                type="text"
                :readonly="fieldData.field_readonly"
                :style="fieldData.field_style"

                :value="value"
                @input="$emit('input', $event.target.value)"
                @focusin="handleFocusIn"
                @focusout="handleFocusOut"
                @click="$emit('click')"
            />

            <div
                v-else-if=" fieldData.field_type == 'textarea' && options.mode != 'search'"
                class="ts resizable input"
                @click="$emit('click')"
            >
                <textarea
                    v-input-auto-width

                    type="text"
                    :readonly="fieldData.field_readonly"
                    :style="fieldData.field_style"

                    :value="value"
                    @input="$emit('input', $event.target.value)"
                    @focusin="handleFocusIn"
                    @focusout="handleFocusOut"
                />
            </div>

            <input
                v-else-if=" fieldData.field_type == 'integer'"

                v-input-auto-width
                type="text"
                step='1'
                :readonly="fieldData.field_readonly"
                :style="fieldData.field_style"

                v-model="number.tmpValue"
                @input="handleNumberInput"
                @focusin="handleFocusIn"
                @focusout="handleFocusOut"
                @click="$emit('click')"
            />

            <input
                v-else-if=" fieldData.field_type == 'decimal'"

                v-input-auto-width
                type="text"
                :readonly="fieldData.field_readonly"
                :style="fieldData.field_style"

                v-model="number.tmpValue"
                @input="handleNumberInput"
                @focusin="handleFocusIn"
                @focusout="handleFocusOut"
                @click="$emit('click')"
            />

            <div
                v-else-if=" fieldData.field_type == 'boolean'"
                class="ts toggle checkbox"
                :style="fieldData.field_style"
            >
                <input
                    type="checkbox"

                    :id="randomID"
                    :disabled="fieldData.field_readonly"
                    :checked="value == '1'"

                    ref="switch"
                    @click="handleBooleanChange()"
                />
                <label
                    :for="randomID"
                />
            </div>

            <select
                v-else-if="( fieldData.field_type == 'select') && options.mode != 'search'"

                :value="value"
                :disabled="fieldData.field_readonly"
                :style="fieldData.field_style"

                @input="$emit('input', $event.target.value)"
                @focusin="handleFocusIn"
                @focusout="handleFocusOut"
                @click="$emit('click')"
            >
                <option
                    v-for="option in  fieldData.field_options.options"
                    :value="option"
                >{{ option }}</option>
            </select>

            <div
                v-else-if="( fieldData.field_type == 'checkboxes' ||  fieldData.field_type == 'radio') && options.mode != 'search'"
                class="ts compact horizontal checkboxes"
                @click="$emit('click')"
            >
                <div
                    v-if=" fieldData.field_type == 'checkboxes'"
                    v-for="option in  fieldData.field_options.options"
                    class="ts checkbox"
                    :style="fieldData.field_style"
                >
                    <input
                        type="checkbox"

                        :id="randomID+option"
                        :name="randomID"
                        :checked="value.includes(option)"
                        :disabled="fieldData.field_readonly"

                        @click="handleCheckboxesChange(option)"
                    >
                    <label
                        :for="randomID+option"
                    >{{ option }}</label>
                </div>
                <div
                    v-if=" fieldData.field_type == 'radio'"
                    v-for="option in  fieldData.field_options.options"
                    class="ts radio checkbox"
                    :style="fieldData.field_style"
                >
                    <input
                        type="radio"

                        :id="randomID+option"
                        :name="randomID"
                        :checked="value == option"
                        :value="option"
                        :disabled="fieldData.field_readonly"

                        @input="$emit('input', $event.target.value)"
                        @change="$emit('change', $event.target.value)"
                    >
                    <label
                        :for="randomID+option"
                    >{{ option }}</label>
                </div>
            </div>

            <input
                v-else-if=" fieldData.field_type == 'date'"

                v-input-auto-width="{type:'date'}"
                :id="randomID"
                type="text"
                :readonly="fieldData.field_readonly"
                :style="fieldData.field_style"

                :value="value"
                @input="$emit('input', $event.target.value)"
                @focusin="handleFocusIn"
                @focusout="handleFocusOut"
                @click="$emit('click')"
            />

            <input
                v-else-if=" fieldData.field_type == 'time'"

                v-input-auto-width="{type:'time'}"
                :id="randomID"
                type="text"
                :readonly="fieldData.field_readonly"
                :style="fieldData.field_style"

                :value="value"
                @input="$emit('input', $event.target.value)"
                @focusin="handleFocusIn"
                @focusout="handleFocusOut"
                @click="$emit('click')"
            />

            <input
                v-else-if=" fieldData.field_type == 'datetime'"

                v-input-auto-width="{type:'datetime'}"
                :id="randomID"
                type="text"
                :readonly="fieldData.field_readonly"
                :style="fieldData.field_style"

                :value="value"
                @input="$emit('input', $event.target.value)"
                @focusin="handleFocusIn"
                @focusout="handleFocusOut"
                @click="$emit('click')"
            />

            <div class="ts action input"
                v-else-if=" fieldData.field_type == 'file' && options.mode != 'search'"
            >
                <input type="text" v-input-auto-width readonly :value="value" :style="fieldData.field_style">
                <input type="file" style="display : none" ref="fileInput" @change="handleFileChanged">
                <button
                type="button"
                    class="ts primary icon button"
                    :id="randomID"
                    :disabled="fieldData.field_readonly"
                    @click.prevent="handleSelectFile">
                    <i class="file outline icon" />
                </button>
            </div>


            <universal-reference
                v-else-if=" fieldData.field_type == 'reference' && options.mode != 'search'"
                ref="reference"

                :field="fieldData"
                :dataset='dataset'
                :readonly="fieldData.field_readonly"


                :value="value"
                @input="$emit('input', $event);"
                @change="$emit('change', $event)"
                @reference="emitReferenceClicked"
                @click="$emit('click')"
            />

            <universal-reference-page
                v-else-if=" fieldData.field_type == 'reference_page' && options.mode != 'search' "
                ref='referencePage'

                :field="fieldData"
                :dataset='dataset'
                :rootDataset='rootDataset'
                :mode="options.mode == 'view' ? 'view' : fieldData.field_readonly ? 'view' : 'edit'"

                :value="value"
                @input="$emit('input', $event)"
                @click="$emit('click')"
            />

            <button
                v-else-if="( fieldData.field_type == 'button') && options.mode != 'search'"
                type="button"
                class="ts primary button"
                :disabled="fieldData.field_readonly"
                :style="fieldData.field_style"

                @click.prevent="$emit('click')"
            >
                {{  fieldData.translation }}
            </button>

            <input
                v-else

                v-input-auto-width
                type="text"
                :readonly="fieldData.field_readonly"
                :style="fieldData.field_style"

                :value="value"
                @input="$emit('input', $event.target.value)"
                @focusin="handleFocusIn"
                @focusout="handleFocusOut"
                @click="$emit('click')"
            />
        </span>
        <span v-else @click="$emit('click')">
            <input
                v-input-auto-width
                type="text"
                :value="value"
                :style="fieldData.field_style"
                @input="$emit('input', $event.target.value)"
                @change="$emit('change', $event.target.value)"
            />
        </span>
    `,
    props: ['field', 'value', 'dataset', 'options', 'override', 'rootDataset'],
    mounted() {
       // console.log(this.field);
        this.initField();

    },
    data() {
        return {
            randomID: "universal_" + Math.random().toString().slice(3, 12) + Math.random().toString().slice(3, 12),
            fieldData: this.field,
            focusValue: null,

            number: {
                tmpValue: ''
            },

            file: {
                filename: null
            },

            datetime: {
                inited: false,
                instance: null
            }
        }
    },
    watch: {
        value: function(newVal, oldVal) {
            if (newVal != oldVal) {
                let el = this.$el.querySelector('input,textarea,select');
                if (el)
                    el.dispatchEvent(events.Change)
            }
        },
        field: function(newVal, oldVal) {
            this.initField()
        },
        override: {
            handler: function(newVal, oldVal) {
                this.initField()
            },
            deep: true
        }
    },
    methods: {
        initField() {
            this.getFieldData()
           //console.log(this.$el);
            if (this.fieldData != undefined && this.options.mode != 'view') {
                let el = this.$el.querySelector('input,textarea,select');
                let that = this;

                if (['decimal', 'integer'].includes(this.fieldData.field_type)) {
                    this.setNumberWithThousandPoint()
                    this.$watch('value', (o, n) => {
                        if (o != n) {
                            this.setNumberWithThousandPoint()
                        }
                    })
                }

                if (['date', 'time', 'datetime'].includes(this.fieldData.field_type)) {
                    if (this.datetime.inited && this.fieldData.field_readonly) {
                        this.datetime.instance.close();
                        this.datetime.instance.destroy();
                        this.datetime.inited = false
                    }
                    if (!this.fieldData.field_readonly && !this.datetime.inited) {
                        let value = el ? el.value : "";
                        let pickerOptions = {
                            onOpen(selectedDates, dateStr, instance) {
                                if (that.fieldData.field_readonly) {
                                    instance.destroy()
                                    that.datetime.inited = false
                                    return
                                }
                                if (that.value) {
                                    instance.setDate(that.value)
                                }
                            },
                            onReady() {
                                el.readOnly = that.fieldData.field_readonly
                            }
                        }
                        if (this.field.field_type == "date") {
                            pickerOptions["dateFormat"] = "Y-m-d"
                        } else if (this.field.field_type == "time") {
                            pickerOptions["dateFormat"] = "H:i:00"
                            pickerOptions["enableTime"] = true
                            pickerOptions["noCalendar"] = true
                        } else if (this.field.field_type == "datetime") {
                            pickerOptions["dateFormat"] = "Y-m-d H:i:00"
                            pickerOptions["enableTime"] = true
                        }
                        this.datetime.instance = flatpickr(el, pickerOptions)
                        el.value = value;
                        this.datetime.inited = true
                    }
                }
            } else if (this.fieldData != undefined && this.options.mode == 'view') {
                //console.log('a')
                if (['decimal', 'integer'].includes(this.field.field_type)) {
                    this.setNumberWithThousandPoint()
                    this.$nextTick().then(_ => this.$emit("input", this.number.tmpValue))
                }
            }
            if(this.dataset && this.dataset.accessFields){
                this.dataset.accessFields[this.fieldData.field_code] = () => {
                    return this
                }
            }
        },

        getFieldData() {
            let originalField = this.field;
            let options = this.options == undefined ? {} : this.options;
            if (this.override || Object.keys(options).length != 0 || (this.dataset.status != 'add' && this.field.field_options.editable === false)) {
                let overrideField = this.override ? this.override : {};
                let newField = deepClone(this.field);
                newField = {
                    ...newField,
                    ...overrideField,
                    ...options
                }
                if ((this.dataset && this.dataset.status !== undefined) && this.dataset.status != 'add' && this.field.field_options.editable === false) {
                    newField.field_readonly = true;
                }
                this.fieldData = newField
            } else {
                this.fieldData = originalField
            }
        },

        // Numbers
        getDecimalStep(decimal) {

        },
        handleNumberInput() {
            let tmpValue = this.number.tmpValue
            if (tmpValue.charAt(tmpValue.length - 1) == '.') {
                if (tmpValue.charAt(tmpValue.length - 2) == '.') {
                    tmpValue = tmpValue.substr(0, tmpValue.length - 1);
                }
                return;
            }

            let numberWithoutPoints = this.number.tmpValue.match(/(^-\d+|\d+|\.{1})/g, '')
            numberWithoutPoints = numberWithoutPoints ? numberWithoutPoints.join('') : ''

            this.$emit('input', numberWithoutPoints);
            this.$nextTick().then(_ => this.setNumberWithThousandPoint());
        },
        setNumberWithThousandPoint() {
            if (this.value === undefined || this.value === null) {
                this.number.tmpValue = ''
                return;
            }

            let splitedNumbers = this.value.toString().split('.')
            let intValue = splitedNumbers[0]
            let floatValue = splitedNumbers[1] ? splitedNumbers[1] : ''

            intValue = intValue.replace(/\d(?=(\d{3})+$)/g, '$&,');
            this.number.tmpValue = floatValue ? `${intValue}.${floatValue}` : `${intValue}`;
        },

        handleFocusIn() {
            this.focusValue = this.value
        },
        handleFocusOut() {
            if (this.focusValue != this.value)
                this.$emit('change', this.value)
        },
        handleCheckboxesChange(option) {
            let tmp = [...this.value]
            let dataIndex = tmp.findIndex(item => item == option)
            if (dataIndex != -1) {
                delete tmp[dataIndex]
                tmp = tmp.filter(() => true)
            } else {
                tmp.push(option)
            }
            this.$emit('input', tmp)
            this.$emit('change', tmp)
        },
        handleBooleanChange(that) {
            const checked = this.$refs.switch.checked
            const value = checked ? '1' : '0'

            this.$emit('input', value)
            this.$emit('change', value)
            this.$emit('click')
        },
        handleSelectFile() {
            this.$refs.fileInput.click();
            this.$emit('click')
        },
        handleDownloadFile() {
            window.open(window.urls.download.replace("-fieldID-", this.field.field_id).replace("-filename-", this.value).replace("-id-", this.dataset.data.id), '_blank');
        },
        handleFileChanged(e) {
            if (this.$refs.fileInput.files[0]) {
                this.$emit('input', this.$refs.fileInput.files[0].name)
                this.$emit('change', this.$refs.fileInput.files[0].name)
                this.$emit('file', this.$refs.fileInput.files[0])
            } else {
                this.$emit('input', "")
                this.$emit('change', "")
            }
        },

        // Reference
        emitReferenceClicked(data, fields) {
            this.$emit("reference", data, fields)
        }
    }
});

Vue.component('parent-child-selector', {
    data: function() {
        return {
            allValues: {},
        };
    },
    mounted() {
        ts('.ts.dropdown:not(.basic)').dropdown();
        this.cancelClick();
        if (this.value != 0) {
            this.inputValue(this.value);
        }
    },
    computed: {
        showDropdown: function() {
            let result = {};
            for (let type in this.mainData) {
                if (!this.ignoreType.includes(type)) {
                    result[type] = this.mainData[type];
                }
            }
            return result;
        },
        parentAndChild: function() {
            let result = [];
            for (item in this.showDropdown) {
                result.push(item);
            }
            return result;
        },
        lastChild: function() {
            return this.parentAndChild[this.parentAndChild.length - 1];
        },
        anySelected: function() {
            let result = false;
            for (let type in this.showDropdown) {
                if (!isEmpty(this.$refs[type])) {
                    const dropdown = this.$refs[type][0];
                    if (dropdown.itemSelected !== -1) {
                        result = true;
                        break;
                    }
                }
            }
            return result;
        },
    },
    model: {
        prop: 'value',
        event: 'change'
    },
    props: {
        'main-data': Object,
        'item-name-key': String,
        'parent-key': String,
        'input-id': {
            type: String,
            default: function() {
                return 'parent-child-selector';
            }
        },
        'value': {
            type: [String, Number],
            default: function() {
                return 0;
            }
        },
        'ignore-type': {
            type: Array,
            default: function() {
                return [];
            }
        },
        'ignore-item': {
            type: Array,
            default: function() {
                return [];
            }
        },
        'translations': {
            type: Object,
            default: function() {
                return {};
            }
        },
        'disabled': {
            type: Boolean,
            default: function() {
                return false;
            }
        },
        'cancel-hidden': {
            type: Boolean,
            default: function() {
                return false;
            }
        }
    },
    methods: {
        cancelClick: function(event) {
            if (event != undefined) {
                event.preventDefault();
            }
            // this.$refs.module[0].itemSelected = -1;
            for (let dropdown in this.showDropdown) {
                this.$refs[dropdown][0].itemSelected = -1;
                this.$refs[dropdown][0].showItems = [];
                this.$refs[dropdown][0].showAll = true;
                this.allValues[dropdown] = -1;
                this.$emit('any-change', dropdown, -1);
            }
            this.$emit('change', -1);
            this.$emit('cancel');
        },
        dropdownChange: function(dropdownType, changeChild = true, lastNode = null, parents = []) {
            const parentAndChild = this.parentAndChild;
            const parent = this.parentAndChild.indexOf(dropdownType) - 1;
            const child = this.parentAndChild.indexOf(dropdownType) + 1;
            const thisDropdown = lastNode === null ? this.$refs[dropdownType][0] : lastNode;
            const selected = thisDropdown.itemSelected;
            if (parent > -1) {
                const parentDropdown = this.$refs[parentAndChild[parent]][0];
                const parentSelected = parentDropdown.itemSelected;
                if (selected !== -1) {
                    const parentBeSelected = Number(thisDropdown.items[selected][this.parentKey]);
                    // console.log(thisDropdown, parentDropdown.items[parentBeSelected]);
                    if (parentDropdown.items[parentBeSelected] === undefined) {
                        parentDropdown.itemSelected = -1;
                        this.dropdownChange(parentAndChild[parent], false, thisDropdown);
                    } else if (parentSelected === -1) {
                        parentDropdown.itemSelected = parentBeSelected;
                        this.dropdownChange(parentAndChild[parent], false);
                    }
                }
            }
            if (child < this.parentAndChild.length) {
                const childDropdown = this.$refs[parentAndChild[child]][0];
                const childItems = childDropdown.items;
                let showItems = [];
                let toNextParents = deepClone(parents);
                if (selected !== -1) {
                    toNextParents.push(selected);
                    for (let item in childItems) {
                        if (Number(childItems[item][this.parentKey]) === selected) {
                            showItems.push(Number(item));
                        }
                    }
                    childDropdown.showItems = showItems;
                    childDropdown.showAll = false;
                    if (changeChild) {
                        childDropdown.itemSelected = -1;
                        this.dropdownChange(parentAndChild[child], true, null, toNextParents);
                    }
                } else {
                    const thisShowItems = thisDropdown.showItems;
                    toNextParents = toNextParents.concat(thisShowItems);
                    for (let item in childItems) {
                        if (toNextParents.includes(Number(childItems[item][this.parentKey]))) {
                            showItems.push(Number(item));
                        }
                    }
                    childDropdown.showItems = showItems;
                    childDropdown.showAll = false;
                    if (changeChild) {
                        childDropdown.itemSelected = -1;
                    }
                    this.dropdownChange(parentAndChild[child], changeChild, null, toNextParents);
                }

            } else {
                if (selected !== -1) {
                    this.$emit('change', selected);
                }
            }
            this.allValues[dropdownType] = selected;
            if (changeChild && lastNode === null && equalityComparison(parents, [])) {
                this.$emit('any-change', dropdownType, selected);
            }
        },
        inputValue: function(value) {
            const valueToNumber = Number(value);
            if (isNaN(valueToNumber)) {
                console.error("Value can't be NaN!");
                return false;
            } else if (valueToNumber === 0 && typeof value == "string") {
                console.error("Value can't be String or Empty string!");
                return false;
            } else if (valueToNumber === 0) {
                console.error("Value can't be 0!");
                return false;
            } else if (valueToNumber < 0) {
                console.error("Value can't be smaller than 0!");
                return false;
            } else {
                const lastChildType = this.lastChild;
                const lastChild = this.$refs[lastChildType][0];
                let found = false;
                let i = this.parentAndChild.length - 1;
                while (!found || i >= 0) {
                    const dropType = this.parentAndChild[i];
                    const dropdown = this.$refs[dropType][0];
                    if (!this.ignoreType.includes(dropType)) {
                        if (dropdown.items[valueToNumber] != undefined) {
                            dropdown.itemSelected = valueToNumber;
                            this.dropdownChange(dropType);
                            found = true;
                        }
                    }
                    i--;
                }
                if (!found) {
                    console.error("Can not find this value in items!");
                }
                return found;
                /* if (lastChild.items[valueToNumber] != undefined) {
                    lastChild.itemSelected = valueToNumber;
                    this.dropdownChange(lastChildType);
                } else {

                } */
            }
        },
    },
    template: `
        <span style="margin-top: 0px; margin-bottom: 1.5rem;">
            <parent-child-dropdown
                v-for="(items, key) in showDropdown"
                :items="items"
                :key="key"
                :ref="key"
                :disabled="disabled"
                :dropdown-type="key"
                :item-name-key="itemNameKey"
                :ignore-item="ignoreItem"
                :value="value"
                :last-child="key == lastChild"
            ></parent-child-dropdown>
            <button
                class="ts floating button"
                v-bind="{hidden: disabled||cancelHidden||!anySelected}"
                id="cancel"
                @click="cancelClick"
            >{{translations.clear}}</button>
            <input
                type="hidden"
                :id="inputId"
                :value="value"
                :ref="inputId"
            >
        </span>
    `,
});

Vue.component('parent-child-dropdown', {
    data: function() {
        return {
            translations,
            dropdownStyle: {
                'margin-top': '0px',
                'margin-right': '0.85em'
            },
            itemSelected: -1,
            showItems: [],
            showAll: true,
            // parent: -1,
        }
    },
    methods: {
        itemClick(thisNode) {
            this.itemSelected = Number(thisNode);
            // this.parent = Number(parentNode);
            this.$parent.dropdownChange(this.dropdownType);
            // console.log(this);
        },
    },
    props: {
        items: Object,
        'dropdown-type': String,
        'item-name-key': String,
        disabled: Boolean,
        'ignore-item': {
            type: Array,
            default: function() {
                return [];
            }
        },
    },
    mounted() {

    },
    computed: {
        selectedText: function() {
            let result = `${translations.selecting} ${translations[this.dropdownType]}`;
            if (this.itemSelected !== -1) {
                result = this.items[this.itemSelected].page_name;
            }
            return result;
        },
        toShowItems: function() {
            let result = {};
            for (let i of this.ignoreItem) {
                if (this.items[i] !== undefined) {
                    delete this.items[i];
                }
            }
            if (this.showAll) {
                result = this.items;
            } else {
                for (let show of this.showItems) {
                    result[show] = this.items[show];
                }
            }
            return result;
        },
    },
    template: `
        <div
            class="ts floating dropdown labeled icon button"
            :class="{disabled}"
            :id="'parent-child-dropdown-'+dropdownType"
            :style="dropdownStyle"
        >
            <i class="dropdown icon"></i>
            <span class="text">{{selectedText}}</span>
            <div class="menu">
                <parent-child-item
                    v-for="(item, id) in toShowItems"
                    :item="item"
                    :item-id="id"
                    :key="id"
                    :item-name-key="itemNameKey"
                    @item-click="itemClick"
                ></parent-child-item>
            </div>
        </div>
    `,
});

Vue.component('parent-child-item', {
    props: {
        item: Object,
        'item-id': String,
        'item-name-key': String,
    },
    template: `
        <div
            class="item"
            :id="'parent-child-item'+itemId"
            @click="$emit('item-click', itemId)"
        >{{item[itemNameKey] == undefined ? '': item[itemNameKey]}}</div>
    `,
});
