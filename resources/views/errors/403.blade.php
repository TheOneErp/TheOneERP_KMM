@extends('layouts.default')

@section('title', $commonTranslations["access_dined"])

@section('content')
<div class="ts secondary inverted negative message">
    <div class="header">{{ $commonTranslations["access_dined"] }}</div>
    <p>{{ $commonTranslations["error.check_permission"] }}</p>
</div>
@endsection
