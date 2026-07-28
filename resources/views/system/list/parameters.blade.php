@extends('layouts.default')
@section('title', '參數設定')
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
                <button class="ts primary very compact labeled icon small button" name="newParameter" onclick="location.href='{{ route('parameters_form', ['type' => 'insert']) }}'">
                    <i class="add icon"></i>{{$languages["new"]}}
                </button>
            </th>
            @foreach ($fields as $item)
            <th>{{$languages[$item->field_code]}}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($datas as $key=>$value)
        <tr>
            <td class="glm list actions">
                <div class="ts buttons">
                    <button class="ts icon very compact small button"><i class="eye icon"></i></button>
                    <button class="ts icon info very compact small button" name="editParameter" onclick="location.href='{{ route('parameters_form', ['type' => 'update','id' => $value->parameter_id]) }}'"><i class="pencil icon"></i></button>
                    <button class="ts icon positive very compact small button"><i class="copy icon"></i></button>
                    <form class="deleteform" action="{{ route('parameters_delete', ['id' => $value->parameter_id]) }}" method="POST">
                        @csrf
                        <button style="cursor: pointer;" class="ts icon negative very compact small button" name="delParameter" onclick="return confirm('{{$commonTranslations['delete.confirm']}}');" {{$value->parameter_deletable=="0"?"disabled='disabled'":""}}><i class="delete icon"></i></button>
                    </form>
                </div>
            </td>
            @foreach ($fields as $item)
            <td>
                @if ($item->field_type == "boolean")
                {{ ($value->{$item->field_code})==1 ? $commonTranslations["yes"] : $commonTranslations["no"]}}
                @else
                {{ ($value->{$item->field_code}) }}
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            @foreach ($fields as $item)
            <th>{{$languages[$item->field_code]}}</th>
            @endforeach
        </tr>
    </tfoot>
</table>
<div class="pagindiv">
    {{$datas->links()}}
</div>

@endsection
