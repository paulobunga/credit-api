@extends('layout')

@section('head')
<title>{{ env('APP_NAME') }} - PayIn</title>
@endsection

@section('style')
<style>
</style>
@endsection

@section('content')
<div class="py-4 md:py-6">
    <x-stepper :steps="$steps" />
</div>
<div class="w-full py-4 md:py-6  text-center flex justify-center">
    <x-timer :dateTime='$deposit->expired_at' />
</div>
<div class="w-full py-4 flex-1 text-center font-bold text-2xl">
    <div class="h-6 text-gray-800 text-lg leading-8 uppercase">Amount</div>
    <div class="text-red-700">
        {{ number_format($deposit->amount, 2, '.',  '') }} {{ $deposit->currency }}
    </div>
</div>

@yield('attributes')

@endsection