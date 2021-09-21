@extends('layout')

@section('head')
<title>{{ env('APP_NAME') }} - PayIn</title>
@endsection

@section('style')
<style>
</style>
@endsection

@section('content')
<x-stepper :steps="$steps" />
<div class="py-4 md:py-6 mb-10 text-center flex justify-center">
    <x-timer :dateTime='$deposit->expired_at' />
</div>

@yield('attributes')

@endsection