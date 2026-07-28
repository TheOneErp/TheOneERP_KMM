@extends('layouts.html')

@section('body')

<header>
    @include('layouts.default.header')
</header>

<div class="ts stackable grid" id="index">
    <div class="four wide column" id="menu">
        @include('layouts.default.menu')
    </div>
    <div class="twelve wide column" id="content">
        @yield('content')
    </div>
</div>

@endsection

@section('header')
@yield('style')
@endsection

@section('footer')
@yield('script')
@endsection
