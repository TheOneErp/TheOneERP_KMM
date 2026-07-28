@extends('layouts.default')
@section('title', '排程管理')
@section('content')
@if(isset($errors) && ((is_object($errors) && count($errors->all()) > 0 ) || count($errors) > 0))
<div class="ts inverted icon negative message">
    <i class="remove circle icon"></i>
    <div class="header">
        @if(is_object($errors))
        @foreach($errors->all() as $key=>$message)
        {{$message}}<br>
        @endforeach
        @else
        {{$errors}}
        @endif
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
<h3 class="ts header">{{$languages["page_name"]}}</h3>

<table class="ts selectable stackable celled table">
    <thead>
        <tr>
            <th>
                <button class="ts primary very compact labeled icon small button" name="newSchedule" onclick="location.href='{{ route('schedules_form', ['type' => 'insert']) }}'"><i class="add icon"></i>{{$languages["new"]}}</button>
            </th>
            @foreach ($fields as $item)
            <th>{{$languages[$item->field_code]}}</th>
            @endforeach
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($datas as $key=>$val)
        <tr>
            <td class="glm list actions">
                <div class="ts buttons">
                    <button class="ts icon very compact small button"><i class="eye icon"></i></button>
                    <button class="ts icon info very compact small button" name="editSchedules" onclick="location.href='{{ route('schedules_form', ['type' => 'update','id' => $val->schedule_id]) }}'" {{$val->schedule_active=="1"?"disabled='disabled'":""}}><i class="pencil icon"></i></button>
                    <button class="ts icon positive very compact small button"><i class="copy icon"></i></button>
                    <form class="deleteform" action="{{ route('schedules_del', ['id' => $val->schedule_id]) }}" method="POST">
                        @csrf
                        <button class="ts icon negative very compact small  button" name="delSchedule" onclick="return confirm('{{$commonTranslations['delete.confirm']}}');" {{$val->schedule_active=="1"?"disabled='disabled'":""}}><i class="delete icon"></i></button>
                    </form>
                </div>
            </td>
            @foreach ($fields as $item)
            <td>
                @if ($item->field_type == "boolean")
                {{ ($val->{$item->field_code})==1 ? $commonTranslations["yes"] : $commonTranslations["no"]}}
                @else
                {{ ($val->{$item->field_code}) }}
                @endif
            </td>
            @endforeach
            <td>
                <form class="runform" action="{{ route('schedules_run', ['id' => $val->schedule_id]) }}" method="POST">
                    @csrf
                    @if($val->schedule_active == '0')
                    <button style="cursor: pointer;" class="ts  button btnicon" name="runSchedules" onclick="return confirm('確定執行此排程?');"><i class="large video play icon"></i></button>
                    @else
                    <button style="cursor: pointer;" class="ts  button btnicon" name="stopSchedules" onclick="return confirm('確定暫停此排程?');"><i class="large pause icon"></i></button>
                    @endif

                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            @foreach ($fields as $item)
            <th>{{$languages[$item->field_code]}}</th>
            @endforeach
            <th></th>
        </tr>
    </tfoot>
</table>
<div class="pagindiv">
    {{$datas->links()}}
</div>

@endsection
