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
<h2 class="txt_center">新增參數</h2>
<form class="ts horizontal form" id="newParameterForm" name="newParameterForm" method="POST" action="{{ route('parameters_save', ['type' => $type,'id' => is_null($parameter_data) ? '' : $parameter_data->parameter_id]) }}">
    @csrf
    <div class="ts field" v-for="language in languages">
        <label><span style="color:red">*</span>名稱</label>
        <input type="text" id="parameter_code" name="parameter_code" value="{{old('parameter_code',is_null($parameter_data) ? '' : $parameter_data->parameter_code)}}" {{$type=='update' ?'readonly':''}}>
    </div>
    <div class="ts field" v-for="language in languages">
        <label><span style="color:red">*</span>值</label>
        <input type="text" id="parameter_value" name="parameter_value" value="{{old('parameter_value',is_null($parameter_data) ? '' : $parameter_data->parameter_value)}}">
    </div>
    <div class="ts field" v-for="language in languages">
        <label><span style="color:red">*</span>是否能被刪除</label>
        <div class="ts toggle checkbox">
            <input type="checkbox" id="parameter_deletable" name="parameter_deletable" value="1" @if($type=='insert' ) {{old('parameter_deletable')=='1' ?'checked':''}} @elseif($parameter_data->parameter_deletable==1 && is_null(old('parameter_deletable')) && count($errors) <= 0) {{'checked'}} @elseif(count($errors)>0)
            {{old('parameter_deletable')=='1'?'checked':''}}
            @endif
            >
            <label for="parameter_deletable"></label>
        </div>
    </div>
    <div class="ts field" v-for="language in languages">
        <label><span style="color:red">*</span>備註</label>
        <input type="text" id="parameter_remarks" name="parameter_remarks" value="{{old('parameter_remarks',is_null($parameter_data) ? '' : $parameter_data->parameter_remarks)}}">
    </div>

    <button class="ts primary button" type="submit" name="sendBtn">送出</button>
</form>
@endsection
