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
<h2 class="txt_center">新增排程</h2>
<form class="ts horizontal form" id="newSchedulesForm" name="newSchedulesForm" method="POST" action="{{ route('schedules_save', ['type' => $type,'id' => is_null($schedules_data) ? '' : $schedules_data->schedule_id]) }}">
    @csrf
    <div class="ts field" v-for="language in languages">
        <label><span style="color:red">*</span>名稱</label>
        <input type="text" id="schedule_name" name="schedule_name" value="{{old('schedule_name',is_null($schedules_data) ? '' : $schedules_data->schedule_name)}}" {{$type=='update' ?'readonly':''}}>
    </div>
    <div class="ts field" v-for="language in languages">
        <label><span style="color:red">*</span>函數</label>
        <input type="text" id="schedule_fun" name="schedule_fun" value="{{old('schedule_fun',is_null($schedules_data) ? '' : $schedules_data->schedule_fun)}}">
    </div>
    <div class="ts field" v-for="language in languages">
        <label><span style="color:red">*</span>備註</label>
        <input type="text" id="schedule_remarks" name="schedule_remarks" value="{{old('schedule_remarks',is_null($schedules_data) ? '' : $schedules_data->schedule_remarks)}}">
    </div>
    <input type="hidden" id="schedule_active" name="schedule_active" value="{{old('schedule_active',is_null($schedules_data) ? '0' : $schedules_data->schedule_active)}}">
    <button class="ts primary button" type="submit" name="sendBtn">送出</button>
</form>

</div>
</div>
@endsection
