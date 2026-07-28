@extends('layouts.default')
@section('title', $page_data["page"]["translation"])
@section('content')

@if(isset($errors) && ((is_object($errors) && count($errors->all()) > 0 ) || count($errors) > 0))
<div class="ts inverted icon negative message">
    <i class="remove circle icon"></i>
    <div class="header">
        @if(is_object($errors))
        @foreach($errors->all() as $key=>$message) {{ $message }} <br />
        @endforeach
        @else {{ $errors }} @endif
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
<h2 class="txt_center">審核流程</h2>
<form class="ts horizontal form" id="newVerifyForm" name="newVerifyForm" action="{{ route('verifies_save', ['type' => $type,'id' => is_null($verify_data) ? '' : $page_id]) }}" method="POST">
    @csrf
    <div class="ts grid" id="selector">
        <parent-child-selector :main-data="page_modules" :translations="translations" :disabled="type == 'update'" input-id="" parent-key="page_module" item-name-key="page_name" ref="module_form_selector" @change="selectorChange"></parent-child-selector>
        <div style="margin-left:0.85em">
            <button type="button" class="ts button" onclick="addsTr(this)">表身新增一筆</button>
        </div>
    </div>
    <input type="hidden" id="choiceform" name="choiceform">
    <input type="hidden" id="page_id" name="page_id" value="{{old('$page_id',is_null($page_id) ? '' : $page_id)}}">
    <input type="hidden" id="page_template" name="page_template">

    <table id="secable" class="ts single line table" style="">
        <thead>
            <tr>
                <th>No.</th>
                <th>欄位</th>
                <th>條件式</th>
                <th>值</th>
                <th>對象</th>
                <th>對象型態</th>
                <th>人數</th>
                <th>串接</th>
                <th>串接群組</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @if(is_null($verify_data))
            <tr>
                <td name="tid">1</td>
                <td>
                    <select class="ts basic dropdown" name="field_id[]"></select>
                </td>
                <td>
                    <select class="ts basic dropdown" name="verify_operator[]">
                        <option value=">"> ＞ </option>
                        <option value="="> ＝ </option>
                        <option value="<"> ＜ </option>
                        <option value=">="> ≧ </option>
                        <option value="<="> ≦ </option>
                        <option value="LIKE">包含</option>
                        <option value="NOT LIKE">不包含</option>
                    </select>
                </td>
                <td>
                    <div class="ts input">
                        <input type="text" name="verify_value[]" value="">
                    </div>
                </td>
                <td>
                    <div class="ts input">
                        <select class="ts basic dropdown" name="verify_tanget_id[]">
                            @if($ugArr)
                            @foreach($ugArr as $gukey => $guval)
                            <option value="{{$guval['setting_target_id']}}">{{$guval['setting_target']}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </td>
                <td>
                    <select class="ts basic dropdown" name="verify_tanget_type[]">
                        <option value="user"> 個人 </option>
                        <option value="group"> 群組 </option>
                    </select>
                </td>
                <td>
                    <div class="ts input">
                        <input type="text" name="verify_population[]" value="1" readonly>
                    </div>
                </td>
                <td>
                    <select class="ts basic dropdown" name="verify_logic[]">
                        <option value="and"> AND </option>
                        <option value="or"> OR </option>
                    </select>
                </td>
                <td>
                    <div class="ts input">
                        <input type="number" name="verify_logic_group[]" value="0" min="0">
                    </div>
                </td>
                <td>
                    <button type="button" class="ts negative button btnicon deleteRowBtn" style="cursor: pointer;" name="deleteRowBtn2"><i class="large trash icon"></i></button>

                </td>
            </tr>
            @else
            @for($i=0;$i<$verify_count;$i++) <tr>
                <td name="tid">{{$i+1}}</td>
                <td>
                    <input type="hidden" name="test" value="{{$verify_data[$i]->field_id}}">
                    <select class="ts basic dropdown" name="field_id[]"></select>
                </td>
                <td>
                    <select class="ts basic dropdown" name="verify_operator[]">
                        <option value=">" {{old("verify_operator",is_null($verify_data) ? "" : $verify_data[$i]->verify_operator==">"?"selected":"")}}>＞</option>
                        <option value="=" {{old("verify_operator",is_null($verify_data) ? "" : $verify_data[$i]->verify_operator=="="?"selected":"")}}>＝</option>
                        <option value="<" {{old("verify_operator",is_null($verify_data) ? "" : $verify_data[$i]->verify_operator=="<"?"selected":"")}}>＜</option>
                        <option value=">=" {{old("verify_operator",is_null($verify_data) ? "" : $verify_data[$i]->verify_operator==">="?"selected":"")}}>≧</option>
                        <option value="<=" {{old("verify_operator",is_null($verify_data) ? "" : $verify_data[$i]->verify_operator=="<="?"selected":"")}}>≦</option>
                        <option value="LIKE" {{old("verify_operator",is_null($verify_data) ? "" : $verify_data[$i]->verify_operator=="LIKE"?"selected":"")}}>包含</option>
                        <option value="NOT LIKE" {{old("verify_operator",is_null($verify_data) ? "" : $verify_data[$i]->verify_operator=="NOT LIKE"?"selected":"")}}>不包含</option>
                    </select>
                </td>
                <td>
                    <div class="ts input">
                        <input type="text" name="verify_value[]" value="{{old('verify_value',is_null($verify_data) ? '' : $verify_data[$i]->verify_value)}}">
                    </div>
                </td>
                <td>
                    <div class="ts input">
                        <select class="ts basic dropdown" name="verify_tanget_id[]"></select>
                        <input type="hidden" name="textgu" value="{{old('verify_tanget_id',is_null($verify_data) ? '' : $verify_data[$i]->verify_tanget_id)}}">
                    </div>
                </td>
                <td>
                    <select class="ts basic dropdown" name="verify_tanget_type[]">
                        <option value="user" {{old("verify_tanget_type",is_null($verify_data) ? "" : $verify_data[$i]->verify_tanget_type=="user"?"selected":"")}}> 個人 </option>
                        <option value="group" {{old("verify_tanget_type",is_null($verify_data) ? "" : $verify_data[$i]->verify_tanget_type=="group"?"selected":"")}}> 群組 </option>
                    </select>
                </td>
                <td>
                    <div class="ts input">
                        <input type="text" name="verify_population[]" value="{{old('verify_population',is_null($verify_data) ? '' : $verify_data[$i]->verify_population)}}" readonly>
                    </div>
                </td>
                <td>
                    <select class="ts basic dropdown" name="verify_logic[]">
                        <option value="and" {{old("verify_logic",is_null($verify_data) ? "" : $verify_data[$i]->verify_logic=="and"?"selected":"")}}> AND </option>
                        <option value="or" {{old("verify_logic",is_null($verify_data) ? "" : $verify_data[$i]->verify_logic=="or"?"selected":"")}}> OR </option>
                    </select>
                </td>
                <td>
                    <div class="ts input">
                        <input type="number" name="verify_logic_group[]" value="{{old('verify_logic_group',is_null($verify_data) ? '' : $verify_data[$i]->verify_logic_group)}}" min="0">
                    </div>
                </td>
                <td>
                    <button type="button" class="ts negative button btnicon deleteRowBtn" style="cursor: pointer;" name="deleteRowBtn2"><i class="large trash icon"></i></button>

                </td>
                </tr>
                @endfor
                @endif
        </tbody>
    </table>
    <button class="ts primary button" type="submit" name="sendBtn">送出</button>
</form>

@endsection
@section('script')
@parent
<script>
    //模組下拉選單
    const page_modules = @json($pages);
    const translations = @json($translations);
    const type = "{{$type}}";

    let selector = new Vue({
        el: '#selector',
        data: {
            page_modules,
            translations,
            type
        },
        methods: {
            selectorChange: function(event) {
                const el = event.target;
                console.log(event);
                if (type == "insert" && (event != undefined || event != null || event != "")) {
                    $('#page_id').val(event);
                    $.ajax({
                        url: getURL(`verifies/form`),
                        type: 'POST',
                        data: {
                            '_token': getToken(),
                            'pid': event,
                            //'ptemp': ptemp,
                        },
                        success: function(data) {
                            $("select[name='field_id[]']").empty().append(data);

                        }
                    });
                }
            },
        }
    });
    //console.log(selector.$refs.module_form_selector);
    ts('.ts.dropdown:not(.basic)').dropdown();
    if (type == "update") {
        const pageId = document.querySelector("#page_id").value;
        selector.$refs.module_form_selector.inputValue(pageId);
    }

    //表身no.數字計算
    function resetTdNo(id) {
        let num = 1;
        $("#" + id).find("tbody tr").each(function() {
            var tdArr = $(this).children();
            var idtext = tdArr.eq(0).text(num);
            num++;
        })
    }
    //表身新增一筆
    function addsTr(n) {
        var new_line = $("#secable").find('tr:eq(1)').clone(true);
        new_line.find("select[name='verify_tanget_type[]']").val('user');
        new_line.find("select[name='verify_tanget_type[]']").trigger('change');
        new_line.find("select[name='verify_operator[]']").val('>');
        new_line.find("select[name='verify_logic[]']").val('and');
        new_line.find("input[name='verify_value[]']").val('');
        new_line.find("input[name='verify_logic_group[]']").val('');
        //        new_line.find('input[type="checkbox"]').prop("checked", false);
        $("#secable").append(new_line);
        resetTdNo("secable");
    }

    (function($) {
        //表身刪除
        $("button[name='deleteRowBtn2']").click(function() {
            var rowCount = $('#secable tr').length;
            if (rowCount > 2) {
                $(this).closest('tr').remove();
                resetTdNo("secable");
                return false;
            }
        });

        //計算人數
        $("select[name='verify_tanget_id[]']").each(function() {
            $("select[name='verify_tanget_id[]']").change(function() {
                var val = $(this).find(':selected').data('value');
                var thisid = $(this).parent().parent('tr').find("td[name='tid']");
                console.log(val);
                if (val == undefined) {
                    $(this).parent().parent().parent('tr').find("input[name='verify_population[]']").val('1');
                } else {
                    $(this).parent().parent().parent('tr').find("input[name='verify_population[]']").val(val);
                }

            });
        });
        //選擇個人時列出個人名稱，選擇群組時列出群組名稱
        $("select[name='verify_tanget_type[]']").each(function() {
            $("select[name='verify_tanget_type[]']").change(function() {
                var val = $(this).val();
                var thisid = $(this).parent().parent('tr').find("td[name='tid']");
                console.log(val);
                $.ajax({
                    url: getURL(`verifies/getGU`),
                    type: 'POST',
                    data: {
                        '_token': getToken(),
                        'val': val,
                    },
                    success: function(data) {
                        thisid.parent('tr').find("select[name='verify_tanget_id[]']").empty().append(data);
                        thisid.parent('tr').find("input[name='verify_population[]']").val(thisid.parent('tr').find("select[name='verify_tanget_id[]']").length);
                        $("select[name='verify_tanget_id[]']").trigger('change');
                    }
                });
            });
        });

    })(jQuery);

</script>
@endsection
@section('script')
@parent
<script>
    //修改時會用到的js
    (function($) {
        //pageid帶回欄位下拉選單資料
        if ($('#page_id').val()) {
            var pid = $('#page_id').val();
            $.ajax({
                url: getURL(`verifies/form`),
                type: 'POST',
                data: {
                    '_token': getToken(),
                    'pid': pid,
                },
                success: function(data) {
                    $("select[name='field_id[]']").empty().append(data);
                    var v = "";
                    $("input[name='test']").each(function() {
                        v = $(this).val();
                        $(this).parent("td").find($("select")).val(v);
                    })
                }
            });
        }

        $("input[name='textgu']").each(function() {
            if ($(this).val() != "") {
                var valuet = $(this).val()
                var type = $(this).parent().parent().parent('tr').find("select[name='verify_tanget_type[]']").val();
                var thisid = $(this).parent().parent('td').find("select[name='verify_tanget_id[]']");
                $.ajax({
                    url: getURL(`verifies/getGU`),
                    type: 'POST',
                    data: {
                        '_token': getToken(),
                        'val': type,
                    },
                    success: function(data) {
                        thisid.empty().append(data);
                        thisid.val(valuet);
                    }
                });
            }
        });
        $("select[name='verify_tanget_type[]']").each(function() {
            $("select[name='verify_tanget_type[]']").change(function() {
                var val = $(this).val();
                var thisid = $(this).parent().parent('tr').find("td[name='tid']");
                console.log(val);
                $.ajax({
                    url: getURL(`verifies/getGU`),
                    type: 'POST',
                    data: {
                        '_token': getToken(),
                        'val': val,
                    },
                    success: function(data) {
                        thisid.parent('tr').find("select[name='verify_tanget_id[]']").empty().append(data);
                        thisid.parent('tr').find("input[name='textgu']").val("");
                        $("select[name='verify_tanget_id[]']").trigger('change');
                    }
                });
            });
        });

    })(jQuery);

</script>

@endsection
