@extends('layout')

@section('head')
<title>{{ env('APP_NAME') }} - PayIn</title>
@endsection

@section('style')
<style>
</style>
@endsection

@section('content')
<div class="bg-white">
    <x-alert />
    <div class="sm:max-w-2xl md:container lg:max-w-4xl mx-auto px-4 sm:px-6 py-2">
        <x-stepper :steps="$steps" />
        <div class="py-4 md:py-6 mb-10 text-center flex justify-center">
            <x-timer :dateTime='$deposit->expired_at' />
        </div>
        @yield('attributes')
    </div>
</div>
@endsection