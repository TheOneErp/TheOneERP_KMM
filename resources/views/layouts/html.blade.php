<!DOCTYPE html>
<html lang="en">
<meta name="csrf-token" content="{{ csrf_token() }}">
<head>
    <!-- Title -->
    <title>{{ env('APP_NAME') }} - @yield('title')</title>

    <!-- Metadata -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="_token" content="{{ csrf_token() }}" />
    <script src="{{ asset('js/jquery.min.js') }}"></script>
  <!-- Moment.js v2.20.0 -->
  <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
  <!-- FullCalendar v3.8.1 -->
  <link href="{{ asset('css/fullcalendar.min.css') }}" rel="stylesheet"  />
  <link href="{{ asset('css/fullcalendar.print.css') }}" rel="stylesheet" media="print"></script>
     <link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet"  />
  <script src="{{ asset('js/fullcalendar.min.js') }}"></script>
    <!-- CSS -->
  
    <link rel="stylesheet" href="{{ asset('css/tocas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/v_chrats_style.min.css') }}">

  <link rel="stylesheet" href="{{ asset('css/semantic.min.css') }}">
    <!-- Scripts -->
    <script src="{{ asset('js/sweetalert2.js')}}"></script>

    @yield('head')
</head>

<body>

    @yield('header')

    @yield('body')

    <div class="ts dimmer" id="fullscreenDimmer">
        <div class="ts loader"></div>
    </div>

    <!-- JavaScript -->
  
        <script src="{{ asset('js/tocas.js') }}"></script>
    <script src="{{ asset('js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/zh-tw.js') }}"></script>
<script src="{{ asset('js/semantic.min.js ') }}"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @if("APP_DEBUG")
    <script src="{{ asset('js/vue.common.dev.js') }}"></script>
    @else
    <script src="{{ asset('js/vue.min.js') }}" ></script>
    @endif

    <script>
        @include("assets.js.main")
    </script>

    <script src="{{ asset('js/main.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/common.js') }}?v={{ time() }}"></script>

    @yield('footer')

</body>

</html>
