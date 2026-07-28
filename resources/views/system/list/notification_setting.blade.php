@extends('layouts.default')
@section('title', $languages["page_name"])
@section('content')
<!--<div class="row">-->
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

<!--    <div class="col-md-12">-->
        <h2 class="txt_center">{{$languages["page_name"]}}</h2>

        <table  class="ts selectable stackable celled table" >
            <thead>
                <tr>
                    <th>
                    	<button class="ts primary very compact labeled icon small button" style="cursor: pointer;"  name="notification_setting_new" onclick="location.href='{{ route('notification_setting_form', ['type' => 'insert','id' => '']) }}'"><i class="add icon"></i>{{$languages["new"]}}</button>
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
                			<button type="button" class="ts icon info very compact small button" style="cursor: pointer;" name="editBtn" onclick="location.href='{{ route('notification_setting_form', ['type' => 'update','id' => $value->notification_setting_id]) }}'">
                				<i class="pencil icon"></i>
                			</button>
                			<form class="deleteform" action="{{ route('notification_setting_delete',  ['id' => $value->notification_setting_id]) }}" method="POST">
                				@csrf
                				<button class="ts icon negative very compact small button" style="cursor: pointer;" name="deleteBtn" onclick="return confirm('{{$commonTranslations['delete.confirm']}}');">
                					<i class="delete icon"></i>
                				</button>
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
<!--    </div>-->
<!--</div>-->


@endsection
