@extends('layouts.default')
@section('title', '權限設定')
@section('content')
<div class="ts stackable grid">

    <div class="sixteen wide column">
        <h2 class="txt_center">管理權限</h2>
        <div class="ts stackable grid">
            <div class="sixteen wide column">
                <div class="ts input">
                    <div class="inline field"><label>用戶帳號</label> <span style="width: inherit;"><input type="text"
                                style="width: inherit;" name="user_account" value='{{is_null($username)?'':$username}}'
                                readonly></span></div>
                </div>
            </div>
            <div class="sixteen wide column">
                <div class="ts input">
                    <div class="inline field"><label>用戶名稱</label> <span style="width: inherit;"><input type="text"
                                style="width: inherit;" name="user_name" value='{{is_null($name)?'':$name}}'
                                readonly></span></div>
                </div>
            </div>
            <div class="sixteen wide column">
                <div class="ts input">
                    <div class="inline field"><label>用戶型態</label> <span style="width: inherit;"><input type="text"
                                style="width: inherit;" name="user_type" value="{{is_null($user_type)?'':$user_type}}"
                                readonly></span></div>
                </div>
            </div>
        </div>

        <div class="ts horizontal">
           {{--頁面表格--}}
            <table class="ts selectable stackable celled table" id="list" style="text-align: center;">
                {{-- TODO: 兩個Dropdown寫成迴圈 --}}

                <div class="ts grid" id="selector">
                    <parent-child-selector :main-data="page_modules" :translations="translations"
                        :ignore-type="['page']" input-id="" parent-key="page_module" item-name-key="page_name"
                        ref="module_form_selector" @any-change="selectorChange"></parent-child-selector>
                </div>
                <button class="ts  button" id="copyPermission">權限複製</button>


                <thead>
                    <tr>
                        <th>表單</th>
                        <th>
                            <label for="check_new">新增</label><input type="checkbox" id="check_new"
                                data-check="permission_insert" class="checkAll checkall"></th>
                        <th>
                            <label for="check_new">修改</label><input type="checkbox" id="check_edit"
                                data-check="permission_update" class="checkAll checkall"></th>
                        <th>
                            <label for="check_new">刪除</label><input type="checkbox" id="check_del"
                                data-check="permission_delete" class="checkAll checkall"></th>
                        <th>
                            <label for="check_new">查詢</label><input type="checkbox" id="check_search"
                                data-check="permission_read" class="checkAll checkall"></th>
                        <th>
                            <label for="check_new">全選</label><input type="checkbox" id="check_all"
                                data-check="check_all[]" class="checkAll"></th>
                        <th>
                            <label for="check_new">僅限個人</label><input type="checkbox" id="check_personal"
                                data-check="permission_allow_rw_all" class="checkAll"></th>
                    </tr>
                </thead>
                <tbody id="listItems">
                    @foreach($pages as $key=>$value)
                    <tr>
                        <input class="module{{$value['page_module']}}" type="hidden" id="{{$key}}" name="page_id">
                        <td onclick="getFields({{$key}},this)">{{$value["page_name"]}}</td>
                        <td>
                            <input type="checkbox" class="ts checkbox checkAllitem checkall" name="permission_insert"
                                data-check="check_new" value="1"
                                {{array_key_exists("permission_insert",$value)?($value['permission_insert'] == 1 ? "checked" : ""):""}}>
                        </td>
                        <td><input type="checkbox" class="ts checkbox checkAllitem checkall" name="permission_update"
                                data-check="check_edit" value="1"
                                {{array_key_exists("permission_update",$value)?($value['permission_update'] == 1 ? "checked" : ""):""}}>
                        </td>
                        <td><input type="checkbox" class="ts checkbox checkAllitem checkall" name="permission_delete"
                                data-check="check_del" value="1"
                                {{array_key_exists("permission_delete",$value)?($value['permission_delete'] == 1 ? "checked" : ""):""}}>
                        </td>
                        <td><input type="checkbox" class="ts checkbox checkAllitem checkall" name="permission_read"
                                data-check="check_search" value="1"
                                {{array_key_exists("permission_read",$value)?($value['permission_read'] == 1 ? "checked" : ""):""}}>
                        </td>
                        <td><input type="checkbox" class="ts checkbox checkAllitem rowCheck" name="check_all[]"
                                data-check="check_all" value="1" @if(old('check_all[]')=="1" ) checked @endif></td>
                        <td><input type="checkbox" class="ts checkbox checkAllitem" name="permission_allow_rw_all"
                                data-check="check_personal" value="1"
                                {{array_key_exists("permission_allow_rw_all",$value)?($value['permission_allow_rw_all'] == 1 ? "checked" : ""):""}}>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <thead>

                </thead>
            </table>
            {{--<button type="button" class="ts button" onclick="checkThing();">條件複製</button>--}}
            <input type="hidden" id="chosenPage" value="">
            {{--欄位表格--}}
            <div id="tableDiv">
                @foreach($pages as $key=>$value)
                @if( array_key_exists("column",$value) && count($value['column']) != 0 )
                <table id="table{{$key}}" class="ts single line table show" style="display: none;">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>欄位</th>
                            <th>欄位位置</th>
                            <th>內容屬性</th>
                            <th>邏輯</th>
                            <th>內容</th>
                            <th>關聯</th>
                            <th>備註</th>
                            <th>刪除</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($value['column'] as $ckey=>$column)
                        <tr>
                            <td>{{$ckey+1}}</td>
                            <td>

                                <select class="ts basic dropdown fieldClass" name="field_name{{$key}}"
                                    onchange="addsTr(this);">
                                    @if( count($value['field']) != 0 )
                                    @foreach($value['field'] as $fkey=>$field)
                                    <option value="{{$field['field_id']}}"
                                        {{$column['field_id'] == $field['field_id']?'selected':'' }}>
                                        {{$field['translation']}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </td>
                            <td></td>
                            <td><select class="ts basic dropdown" name="field_attribute{{$key}}"
                                    onchange="addsTr(this);">
                                    <option value="content"
                                        {{$column['permission_column_attribute'] == 'content'?'selected':'' }}>內容
                                    </option>
                                    <option value="num"
                                        {{$column['permission_column_attribute'] == 'num'?'selected':'' }}>筆數</option>
                                </select></td>
                            <td><select class="ts basic dropdown" name="field_logic{{$key}}" onchange="addsTr(this);">
                                    <option value=">" {{$column['permission_column_logic'] == '>'?'selected':'' }}>&gt;
                                    </option>
                                    <option value="=" {{$column['permission_column_logic'] == '='?'selected':'' }}>=
                                    </option>
                                    <option value="<" {{$column['permission_column_logic'] == '<'?'selected':'' }}>&lt;
                                    </option>
                                    <option value=">=" {{$column['permission_column_logic'] == '>='?'selected':'' }}>
                                        &gt;=</option>
                                    <option value="<=" {{$column['permission_column_logic'] == '<='?'selected':'' }}>
                                        &lt;=</option>
                                </select></td>
                            <td><input type="text" onchange="addsTr(this);" name="field_content{{$key}}"
                                    value="{{$column['permission_column_content']}}"></td>
                            <td><select class="ts basic dropdown" name="field_related{{$key}}" onchange="addsTr(this);">
                                    <option value="and" {{$column['permission_column_relative'] == '>'?'selected':'' }}>
                                        And</option>
                                    <option value="or" {{$column['permission_column_relative'] == '>'?'selected':'' }}>
                                        Or</option>
                                </select></td>
                            <td><input type="text" onchange="addsTr(this);" name="field_remark{{$key}}"
                                    value="{{$column['permission_column_remarks']}}"></td>
                            <td><button type="button" class="ts negative button btnicon deleteRowBtn"
                                    style="cursor: pointer;" name="deleteRowBtn" onclick="deleteRow(this);"><i
                                        class="large trash icon"></i></button></td>
                        </tr>
                        @endforeach



                    </tbody>
                </table>
                @endif
                @endforeach
            </div>



        </div>
        <button type="button" name="submitBtn" class="ts button" value="" onclick="submit()" ;>送出</button>
    </div>
    {{-- 複製小視窗 --}}
    <div class="ts modals dimmer">
        <dialog id="optionModal" class="ts closable tiny modal" style="overflow-y: auto;">

            <div class="header">
                選擇用戶
            </div>
            <div class="content">
                <div class="ts  grid">
                    <div class="four wide column">
                        <select name="guSelect" class="ts basic small dropdown ">
                            <option value="none" selected disabled hidden>選擇 用戶型態</option>
                            <option value="group">群組</option>
                            <option value="user">個人</option>
                        </select>
                    </div>
                    <div class="four wide column">
                        <button type="button" class="ts  button" id="cancelled">取消</button>
                    </div>
                </div>
                <table class="ts compact celled definition table">
                    <thead>
                        <tr>
                            <td>選取</td>
                            <th>帳號</th>
                            <th>姓名</th>
                            <th>用戶型態</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($ugArr)
                        @foreach($ugArr as $gukey => $guval)
                        <tr class="radioTr  {{$guval['target_type']}}">
                            <td class="collapsing">
                                <div class="ts radio checkbox">
                                    <input type="radio" id="{{$guval['target_type']}}_{{$guval['target_id']}}"
                                        name="uid" value="{{$guval['target_id']}}">
                                    <label for="{{$guval['target_type']}}_{{$guval['target_id']}}"></label>
                                </div>
                            </td>
                            <td>{{$guval['target_username']}}</td>
                            <td>{{$guval['target_name']}}</td>
                            <td>{{$guval['target_type']}}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                    <tfoot class="full-width">

                    </tfoot>
                </table>
            </div>
            <div class="actions">
                <button class="ts positive button submitCopy">
                    確定
                </button>
            </div>
        </dialog>
    </div>
</div>
@endsection
@section('script')
@parent
<script>
    //模組下拉選單
const page_modules = @json($page);
const translations = @json($translations);
function checkList(){

    let checkArr = {
        check_new : "permission_insert",
        check_edit :"permission_update",
        check_del :"permission_delete",
        check_search :"permission_read",
        check_all :"check_all[]",
        check_personal :"permission_allow_rw_all"
    };

    for(var key in checkArr) {
        let name = checkArr[key];
        let checkboxes = $('input[name="'+name+'"]');
        let num = 0;
        for(var val of checkboxes) {
            if($(val).closest("tr").is(":visible") && $(val).is(':checked') ){
                num++;
            }
        }

        if (num == $("#list tr:visible" ).length - 1) {
            $('#' + key).prop('checked', true);
        }else{
            $('#' + key).prop('checked', false);
        }
    }
}
let selector = new Vue({
    el:'#selector',
    data:{
        page_modules,
        translations
    },
    methods:{
        selectorChange: function(type, id){
            const module_selector = this.$refs.module_form_selector;
            const main = module_selector.allValues.module;
            const sub = module_selector.allValues.submodule;
            let page_id = [];
            if( sub == -1 && main == -1){
               for(let thisTr of document.querySelectorAll("#listItems tr")){
                    thisTr.hidden = false;

                }
            }else{
               if(sub != undefined && sub != -1){
                    page_id.push(sub);
                }else{
                    const items = module_selector.$refs.submodule[0].items;
                    page_id.push(main);
                    for(let item in items){
                        if(Number(items[item].page_module) == main)
                        page_id.push(Number(item));
                    }
                }
                // console.log(page_id)
                for(let thisTr of document.querySelectorAll("#listItems tr")){
                    thisTr.hidden = true;
                    for(let i of page_id){
                        if(thisTr.querySelector("[name=page_id]").classList.contains(`module${i}`)){
                            thisTr.hidden = false;
                            continue;
                        }
                    }
                }
            }

            checkList();
        },
    }
});
ts('.ts.dropdown:not(.basic)').dropdown();

</script>
<script>
    document.querySelector("#content").style['overflow-y'] = 'auto';
    ftArr = [];
    pageCondition = {};

    let thText = ["No.","欄位","欄位位置","內容屬性","邏輯","內容","關聯","備註","刪除"];
    let logicArr = {
        ">" : ">",
        "=" : "=",
        "<" : "<",
        ">=" : ">=",
        "<=" : "<="
    }
    let attributeArr={ "content" : "內容" , "num" : "筆數" }
    let relatedArr={ "and" : "And" , "or" : "Or" }

    //取出下拉的值
    function getSelectValue(name){
        let tmpArr = [];
        let elem = $('select[name='+name+']');
        for(var val of elem) {
            if( val.options.length != 0){
               tmpArr.push(val.options[val.selectedIndex].value);
            }
        }
        // $('select option[value="1"]').attr("selected",true);
        return tmpArr;
    }
    //取出input的值
    function getInputValue(name){
        let tmpArr = [];
        let elem = $('input[name='+name+']');
        for(var val of elem) {
            tmpArr.push(val.value);
        }
        return tmpArr;
    }

    /* function setSelectValue(name){
        let tmpArr = [];
        let elem = $('select[name='+name+']');
        for(var val of elem) {
            tmpArr.push(val.options[val.selectedIndex].value);
            $("#myselect").val("value");
        }
        return tmpArr;
    } */

    function storeValue(chosenP){
        let nameElem= getSelectValue("field_name" + chosenP);
        let attributeElem= getSelectValue("field_attribute" + chosenP);
        let logicElem= getSelectValue("field_logic" + chosenP);
        let contentElem= getInputValue("field_content" + chosenP);
        let relatedElem= getSelectValue("field_related" + chosenP);
        let remarkElem= getInputValue("field_remark" + chosenP);

        let fieldsArr = [];
        let pageArr = [];
        for(var key in nameElem) {
            fieldsArr = {
                "field_name" : nameElem[key],
                "field_attribute" : attributeElem[key],
                "field_logic" : logicElem[key],
                "field_content" : contentElem[key],
                "field_related" : relatedElem[key],
                "field_remark" : remarkElem[key],
            }
            pageArr.push(fieldsArr);
        }


        pageCondition[chosenP] = {
            'column':pageArr,
            'page':{}
        };
        // pageCondition = JSON.stringify(pageCondition);
        console.log(pageCondition);
    }

    function resetTdNo(id){
        let num = 1;
        $("#"+id).find("tbody tr").each(function(){
            var tdArr = $(this).children();
            var idtext = tdArr.eq(0).text(num);
            num++;
        })
    }

    function addsTr(n){
        if($(n).closest("tr").is(":last-child")){
            let tableId = $(n).closest("table").attr("id");
            var new_line = $("#" + tableId ).find('tr:eq(1)').clone(true);
            // new_line.find('input[type="checkbox"]').prop("selected", false);
            new_line.find('input[type="text"]').val("");
            new_line.find('td:eq(2)').empty();
            $("#" + tableId ).append(new_line);
            resetTdNo(tableId );

        }

        if( $( n ).hasClass( "fieldClass" ) ){
            $(n).parent().next('td').text(ftArr[$(n).val()]);
        }
    }

    function createSelect(arrName,name){
        let elemSelect = document.createElement("select");
        for(var key in arrName) {
           let option = document.createElement("option");
           option.value = key;
           option.text = arrName[key];
           elemSelect.appendChild(option);
        }
        elemSelect.setAttribute("class", "ts basic dropdown");
        elemSelect.setAttribute("name", name);
        elemSelect.setAttribute("onchange", "addsTr(this);");
        return elemSelect;
    }

    function creatFieldSelect(pid){

        $.ajax({
           type:'POST',
           url:"{{ route('permission_getFields') }}",
           data:{'_token':getToken(),pid:pid},

           success:function(res){
                let data = res;
                // let data = res;
                let newTable  = document.createElement("table");
                newTable.id = "table" + pid;
                let tblHead = document.createElement("thead");
                let tblBody = document.createElement("tbody");

                let fieldSelect = document.createElement("select");
                let defaultTtype = "";

                // let pageInput = document.createElement("input");
                let contenInput = document.createElement("input");
                let remarkInput = document.createElement("input");

                let deleteBtn = document.createElement("button");
                let btnI = document.createElement("i");
                // pageInput.setAttribute("type", "hidden");
                // pageInput.setAttribute("name","field_page");

                contenInput.setAttribute("type", "text");
                contenInput.setAttribute("onchange", "addsTr(this);");
                contenInput.setAttribute("name","field_content" + pid);
                remarkInput.setAttribute("type", "text");
                remarkInput.setAttribute("onchange", "addsTr(this);");
                remarkInput.setAttribute("name","field_remark" + pid);
                deleteBtn.setAttribute("type", "button");
                deleteBtn.setAttribute("class", "ts negative button btnicon deleteRowBtn");
                deleteBtn.setAttribute("style", "cursor: pointer;");
                deleteBtn.setAttribute("name", "deleteRowBtn");
                deleteBtn.setAttribute("onclick", "deleteRow(this);");
                /* deleteBtn.addEventListener("click", function () {
                     var rowCount = $('#table'+pid+' tr').length;
                    if( rowCount > 2){
                         $(this).closest('tr').remove();
                         resetTdNo('table'+pid);
                         return false;
                    }
                });*/
                btnI.setAttribute("class", "large trash icon");

                deleteBtn.append(btnI);

                //生成table欄位
                for (var i = 0; i < 2; i++) {
                    let row = document.createElement("tr");
                    for (let j = 0; j<= 8; j++) {
                        let cell = "";
                        if( i == 0 ){
                            cell=document.createElement("th");
                            let cellText = document.createTextNode(thText[j]);
                            cell.appendChild(cellText);
                            row.appendChild(cell);
                        }else{
                            cell=document.createElement("td");
                            row.appendChild(cell);
                        }
                    }
                    if( i == 0 ){
                        tblHead.appendChild(row);
                    }else{
                        tblBody.appendChild(row);
                    }
                }
                newTable.appendChild(tblHead);
                newTable.appendChild(tblBody);
                newTable.setAttribute("class", "ts single line table show");
                document.querySelector("#tableDiv").append(newTable);

                data.forEach(function(item, index, arr) {
                    //生成下拉選單
                    // pageInput.setAttribute("value",item['page_id']);
                    let option = document.createElement("option");
                    option.value = item['fields']['field_id'];
                    option.text = item['fields']['translation'];
                    fieldSelect.appendChild(option);
                    ftArr[item['fields']['field_id']] = item['form_type'];
                    if( index == 0 ){
                        defaultTtype = item['fields']['field_id'];
                    }
                });
                // newTable.append(pageInput);
                let logicSelect = createSelect(logicArr,"field_logic" + pid);
                let attributeSelect = createSelect(attributeArr,"field_attribute" + pid);
                let relatedSelect = createSelect(relatedArr,"field_related" + pid);

                fieldSelect.setAttribute("class", "ts basic dropdown fieldClass");
                fieldSelect.setAttribute("name", "field_name" + pid);
                fieldSelect.setAttribute("onchange", "addsTr(this);");
                $("#table" + pid ).find("tbody tr:eq(0)").each(function(){
                    let tdArr = $(this).children();
                    tdArr.eq(0).text(1);
                    tdArr.eq(1).append(fieldSelect);
                    tdArr.eq(2).append(ftArr[defaultTtype]);
                    tdArr.eq(3).append(attributeSelect);
                    tdArr.eq(4).append(logicSelect);
                    tdArr.eq(5).append(contenInput);
                    tdArr.eq(6).append(relatedSelect);
                    tdArr.eq(7).append(remarkInput);
                    tdArr.eq(8).append(deleteBtn);
                });
           }
        });
    }

    function getFields(pid,n){ //permission_read
        let checkRead = $(n).parent().find($("input[name='permission_read']"));
        if( checkRead.is(':checked') ){
            lastId = document.getElementById("chosenPage").value;
            let lastTable = document.querySelector("#table" + lastId);
            if( typeof(lastTable) != "undefined" && lastTable != null ){
                document.getElementById("table" + lastId).style.display = "none";
            }
            let pageTable = document.querySelector("#table" + pid);
            if( typeof(pageTable) != "undefined" && pageTable != null ){
                console.log('table is exist!');
                pageTable.style.display="inline-table";
            }else{
               console.log('no!');
               creatFieldSelect(pid);
            }
            document.getElementById("chosenPage").value = pid;
        }
    }

    //刪除tr
    function deleteRow(n){
        let tableId = $(n).closest("table").attr("id");
        var rowCount = $('#'+ tableId +' tr').length;
        if( rowCount > 2){
            $(n).closest('tr').remove();
            resetTdNo("specialTable");
            return false;
        }
    }

    function submit(){
        const pageListNum = 3;
        let table = document.querySelectorAll("#list tbody tr");
        for(let ele of table ){
            let pageId = ele.querySelector("input[name='page_id']").id
            let arr1 = [];

            //欄位資料
            let pageTable = document.querySelector("#table" + pageId);
            if( typeof(pageTable) != "undefined" && pageTable != null ){
                let nameElem= getSelectValue("field_name" + pageId);
                let attributeElem= getSelectValue("field_attribute" + pageId);
                let logicElem= getSelectValue("field_logic" + pageId);
                let contentElem= getInputValue("field_content" + pageId);
                let relatedElem= getSelectValue("field_related" + pageId);
                let remarkElem= getInputValue("field_remark" + pageId);

                let fieldsArr = [];
                let pageArr = [];
                for(var key in nameElem) {
                    fieldsArr = {
                        "field_name" : nameElem[key],
                        "field_attribute" : attributeElem[key],
                        "field_logic" : logicElem[key],
                        "field_content" : contentElem[key],
                        "field_related" : relatedElem[key],
                        "field_remark" : remarkElem[key],
                    }
                    pageArr.push(fieldsArr);
                }
                pageCondition[pageId] = {
                    'column':pageArr,
                    'page':{
                        'permission_insert' : ele.querySelector("input[name='permission_insert']").checked?1:0,
                        'permission_update' : ele.querySelector("input[name='permission_update']").checked?1:0,
                        'permission_delete' : ele.querySelector("input[name='permission_delete']").checked?1:0,
                        'permission_read' : ele.querySelector("input[name='permission_read']").checked?1:0,
                        'permission_allow_rw_all' : ele.querySelector("input[name='permission_allow_rw_all']").checked?1:0,
                    }
                };
            }else{
                pageCondition[pageId] = {
                    'column':[],
                    'page':{
                        'permission_insert' : ele.querySelector("input[name='permission_insert']").checked?1:0,
                        'permission_update' : ele.querySelector("input[name='permission_update']").checked?1:0,
                        'permission_delete' : ele.querySelector("input[name='permission_delete']").checked?1:0,
                        'permission_read' : ele.querySelector("input[name='permission_read']").checked?1:0,
                        'permission_allow_rw_all' : ele.querySelector("input[name='permission_allow_rw_all']").checked?1:0,
                    }
                };
            }
        }
        let url = "{{ route('permission_save') }}";
        fetch(url, {
            method: 'POST', // or 'PUT'
            body: JSON.stringify({
                '_token':getToken(),
                user_account : document.querySelector("input[name='user_account']").value,
                user_type : document.querySelector("input[name='user_type']").value,
                permission : pageCondition

            }), // data can be `string` or {object}!
            headers: new Headers({
            'Content-Type': 'application/json'
            })
        }).then(
            function (response) {
                if (response.status !== 200) {
                    console.log('Looks like there was a problem.Status Code: ' +  response.status);
                    return;
                }
                response.json().then(function (data) {
                    alert(data);
                    location.href = "{{ route('users_list') }}";
                });
            }
        )
        .catch(error => console.error('Error:', error))
        .then(response => console.log('Success:', response));
    }

    (function($) {
        $('.radioTr').click(function() {
            let chosenRadio = $(this).find('input[type="radio"]');
            chosenRadio.prop('checked', true);
         });

        $('.submitCopy').click(function() {
            let radio = $('input[name="uid"]:checked');
            let target_type = radio.parent().parent().parent().find("td:eq(3)").text().trim();
            $.ajax({
               type:'POST',
               url:"{{ route('permission_copy') }}",
               data:{
                   '_token':getToken(),
                   'user_account' : $("input[name='user_account']").val(),
                   'user_type' : $("input[name='user_type']").val(),
                   'uid':radio.val(),
                   'type':target_type
               },

               success:function(data){
                   if( data = "success" ){
                      location.reload();
                    }
               }
            });
         });

        //點選全選
        $('.checkAll').change(function() {
            let name = $(this).data("check");
            var checkboxes = $('input[name="'+name+'"]');
            if($(this).is(':checked')) {
                for(var val of checkboxes) {
                    if($(val).closest("tr").is(":visible") ){
                        if( $(val).attr("name") == "check_all[]" ){
                           $(val).prop('checked', true);
                           $(val).parent().parent().find('input:checkbox.checkall').prop('checked', true);
                        }else{
                            $(val).prop('checked', true);
                        }
                    }
                }
                if( $(this).attr("id") == "check_all" ){
                    $(this).parent().parent().find('input:checkbox.checkall').prop('checked', true);
                }
            } else {
                for(var val of checkboxes) {
                    if($(val).closest("tr").is(":visible") ){
                        if( $(val).attr("name") == "check_all[]" ){
                            $(val).prop('checked', false);
                           $(val).parent().parent().find('input:checkbox.checkall').prop('checked', false);
                        }else{
                            $(val).prop('checked', false);
                        }
                    }
                }
            }

            if( $(this).attr("id") == "check_all"){
                if( $(this).is(':checked') ){
                    $(this).parent().parent().find('input:checkbox.checkall').prop('checked', true);
                }else{
                    // $(this).parent().parent().find('input:checkbox.checkall').prop('checked', 'false');
                    $(this).parent().parent().find('input:checkbox.checkall').prop("checked",false);
                }
            }
        });

        $('.checkAllitem').change(function() {
            let checkAll = $(this).data("check");
            let name = $(this).attr("name");
            let pid = $(this).parent().parent().find($("input[name='page_id']")).attr("id");
            if( name == "permission_read" && !$(this).is(':checked') ){
                let lastTable = $("#table" + pid);
                if( typeof(lastTable) != "undefined" && lastTable != null ){
                    lastTable.remove();
                }
            }

            let checkboxes = $('input[name="'+name+'"]');
            let showNum = $('input[name="'+name+'"]').closest("tr:visible").length;
            let num = 0;
            for(var val of checkboxes) {
                if($(val).closest("tr").is(":visible") && $(val).is(':checked') ){
                    num++;
                }
            }
            let rowCheckNum = $(this).parent().parent().find('.checkall').length;
            let rowCheckedNum = $(this).parent().parent().find('.checkall:checked').length;
            if( $(this).hasClass("checkall") && rowCheckNum !=  rowCheckedNum){
               $(this).parent().parent().find('.rowCheck').prop("checked", false);
            }else if($(this).hasClass("checkall") && rowCheckNum ==  rowCheckedNum){
                $(this).parent().parent().find('.rowCheck').prop("checked", true);
            }
            if( num == showNum ){
                $('#'+checkAll).prop("checked", true);
            }else{
                $('#'+checkAll).prop('checked', false);
            }

        });

        $('.rowCheck').change(function() {
            if( $(this).is(':checked') ){
                $(this).parent().parent().find('input:checkbox.checkall').prop('checked', true);
            }else{
                $(this).parent().parent().find('input:checkbox.checkall').prop("checked",false);
            }
        });

        $("#copyPermission").click(function() {
            ts('#optionModal').modal({
                approve: '.positive, .approve, .ok',
                onApprove: function() {
                    let uid = $('input[name="uid"]').val();
                }
            }).modal("show")
        });

        $('select[name=guSelect]').change(function() {
            if($(this).val() == 'user' ){
                $(".user").show();
                $(".group").hide();
            }else{
                $(".user").hide();
                $(".group").show();
            }
            // checkAll();
        });

        $("#cancelled").click(function() {
            $(".user").show();
            $(".group").show();
            $('select[name=guSelect]').val("none");
            // checkAll();
        });
    })(jQuery);
</script>
@endsection
