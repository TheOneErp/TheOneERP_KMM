@extends('layouts.default')
@section('title', '測試')
@section('content')

<div class="row">
    @if(isset($errors))
        @if(is_object($errors))
            @foreach($errors->all() as $error)
                <span class="btn-danger col-md-12">{{$error}}</span>
            @endforeach
        @else
            <span class="btn-danger col-md-12">{{$errors}}</span>
        @endif
    @endif
<div class="col-md-12">
	<h2 class="txt_center">測試</h2>
	<div data-tab="用戶資料" class="ts active bottom attached tab segment col-md-12">
		<button type="button" class="ts button" name="testEmail"  onclick="location.href='{{ route('SY006_email') }}'">測試Email</button>
		<button type="button" class="ts button" name="testSMS" onclick="location.href='{{ route('SY006_sms') }}'" >測試SMS</button>
	</div>

</div>




</div>

@endsection
