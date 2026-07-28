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
<h2 class="txt_center">新增群組</h2>
<form class="ts horizontal form" id="newVerifyForm" name="newVerifyForm" action="{{ route('groups_save', ['type' => $type,'id' => is_null($group_data) ? '' : $group_data->group_id]) }}" method="POST">
    @csrf
    <div class="ts grid">
        <div class="one wide column">
            <h5 class="ts left aligned header">群組名稱</h5>
        </div>
        <div class="four wide column">
            <div class="ts input">
                <input type="text" id="group_name" name="group_name" value="{{ old('group_name',is_null($group_data) ? '' : $group_data->group_name) }}" {{$type=="update" ?"readonly":""}}>
            </div>
        </div>
        <div class="four wide column">
            <button type="button" id="adduser" name="adduser"><i class="large add user icon"></i></button>
        </div>
    </div>


    <table id="secable" class="ts single line table" style="">
        <thead>
            <tr>
                <th>No.</th>
                <th>帳號</th>
                <th>名稱</th>
            </tr>
        </thead>
        <tbody>
            @if($type=="update")
            @foreach($ugArr as $gukey => $guval)
            @if($guval['check'] == 1)
            <tr>
                <td>{{$gukey+1}}<input type="hidden" name="tid[]" value="{{$guval['target_id']}}"></td>
                <td>{{$guval['target_username']}}</td>
                <td>{{$guval['target_name']}}</td>
            </tr>
            @endif
            @endforeach
            @endif
        </tbody>
    </table>
    <button class="ts primary button" type="submit" name="sendBtn">送出</button>

    <div class="ts modals dimmer">
        <dialog style="overflow-y: auto;" id="optionModal" class="ts tiny modal">
            <div class="header">
                選擇用戶
            </div>
            <div class="content">
                <table class="ts compact celled definition table">
                    <thead>
                        <tr>
                            <td>
                                <button type="button" id="check_all" name="check_all" value="">全選</button>
                            </td>
                            <th>帳號</th>
                            <th>姓名</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($ugArr && $type=='insert')
                        @foreach($ugArr as $gukey => $guval)
                        <tr>
                            <td class="collapsing">
                                <div class="ts toggle checkbox">
                                    <input type="checkbox" id="toggle{{$guval['target_id']}}" name="uid">
                                    <label for="toggle{{$guval['target_id']}}"></label>
                                    <input type="hidden" name="target_id" value="{{$guval['target_id']}}">
                                </div>
                            </td>
                            <td name="username">{{$guval['target_username']}}</td>
                            <td name="uname">{{$guval['target_name']}}</td>
                        </tr>
                        @endforeach
                        @elseif($ugArr && $type=='update')
                        @foreach($ugArr as $gukey => $guval)
                        @if($guval['check'] == 1)
                        <tr>
                            <td class="collapsing">
                                <div class="ts toggle checkbox">
                                    <input type="checkbox" id="toggle{{$guval['target_id']}}" name="uid" checked>
                                    <label for="toggle{{$guval['target_id']}}"></label>
                                    <input type="text" name="target_id" value="{{$guval['target_id']}}">
                                </div>
                            </td>
                            <td name="username">{{$guval['target_username']}}</td>
                            <td name="uname">{{$guval['target_name']}}</td>
                        </tr>
                        @else
                        <tr>
                            <td class="collapsing">
                                <div class="ts toggle checkbox">
                                    <input type="checkbox" id="toggle{{$guval['target_id']}}" name="uid">
                                    <label for="toggle{{$guval['target_id']}}"></label>
                                    <input type="text" name="target_id" value="{{$guval['target_id']}}">
                                </div>
                            </td>
                            <td name="username">{{$guval['target_username']}}</td>
                            <td name="uname">{{$guval['target_name']}}</td>
                        </tr>
                        @endif
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="actions">
                <button type="button" class="ts positive button">
                    確定
                </button>
            </div>
        </dialog>
</form>




@endsection
@section('script')
@parent
<script>
    (function($) {
        $("#adduser").click(function() {
            let userarr = [];
            ts('#optionModal').modal({
                approve: '.positive, .approve, .ok',
                onApprove: function() {
                    $('#secable tbody').empty();
                    let uid = $('input[name="uid"]:checked');
                    let username = $('input[name="username"]');
                    let uname = $('input[name="uname"]');

                    for (let i = 0; i < uid.length; i++) {
                        let target_id = $(uid[i]).parent('div').find('input[name="target_id"]').val();
                        let username = $(uid[i]).parent().parent().parent('tr').find('td[name="username"]').text();
                        let uname = $(uid[i]).parent().parent().parent('tr').find('td[name="uname"]').text();
                        userarr.push(uid[i], username, uname);
                        $('#secable tbody').append('<tr><td>' + (i + 1) + '<input type="hidden" name="tid[]" value="' + target_id + '"></td><td>' + username + '</td><td>' + uname + '</td></tr>');
                    }
                }
            }).modal("show")
        });


        $("button[name='check_all']").click(function() {
            if ($(this).val() == "checked") {
                $('input:checkbox').not(this).prop('checked', false);
                $("button[name='check_all']").val("");
            } else {
                $('input:checkbox').not(this).prop('checked', true);
                $("button[name='check_all']").val("checked");
            }
        });
    })(jQuery);

</script>

@endsection
