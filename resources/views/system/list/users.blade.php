@extends('layouts.default')
@section('title', $languages["page_name"])
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
				<button class="ts primary very compact labeled icon small button" style="cursor: pointer;" name="newUser" onclick="location.href='{{ route('users_form', ['type' => 'insert','id' => '']) }}'"><i class="add icon"></i>{{$languages["new"]}}</button>
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
                    <button
                        class="ts icon very compact small button"
                        onclick="location.href='{{ route('users_form', ['type' => 'view','id' => $value->user_id]) }}'"
                    ><i class="eye icon"></i></button>
					<button type="button"
						class="ts icon info very compact small button"
						style="cursor: pointer;"
						name="editBtn"
						onclick="location.href='{{ route('users_form', ['type' => 'update','id' => $value->user_id]) }}'">
						<i class="pencil icon"></i>
					</button>

					<button type="button" class="ts positive icon very compact small button" name="permissionBtn" onclick="location.href='{{ route('permission_form', ['type' => 'update','id' =>  $value->user_id,'user_type' => 'user']) }}'">
                        <i class="lock icon"></i>
                    </button>
					<form class="deleteform" action="{{ route('users_delete',  ['id' => $value->user_id]) }}" method="POST">
						@csrf
						<button class="ts icon negative very compact small button"
								style="cursor: pointer;"
								name="deleteBtn"
								onclick="return confirm('{{$commonTranslations['delete.confirm']}}');">
							<i class="delete icon"></i>
						</button>
					</form>
				</div>


			</td>
			@foreach ($fields as $item)
				<td>
					@if ($item->field_type == "boolean")
						{{ ($value->{$item->field_code}) ? $languages["yes"] : $languages["no"]}}
					@else
						{{ ($value->{$item->field_code}) }}
					@endif
				</td>
			@endforeach
			{{-- <td>{{$value->username}}</td>
			<td>{{$value->name}}</td>
			<td>{{$value->usr_email}}</td>
			<td>{{$value->usr_tel}}</td>
			<td>{{$value->user_disabled==1?"是":"否"}}</td>
			<td>{{$value->user_remarks}}</td> --}}
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
