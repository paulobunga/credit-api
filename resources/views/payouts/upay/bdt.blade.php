@extends('layout')

@section('head')
<title>{{ env('APP_NAME') }} - PayOut</title>
@endsection

@section('style')
<style>
</style>
@endsection

@section('content')
<x-stepper :steps="$steps" />
<div class="py-4 md:py-6 text-center flex justify-center">
    <x-timer :dateTime='$withdrawal->expired_at' />
</div>

<div class="grid grid-flow-row sm:gap-y-4 gap-y-2 text-sm sm:text-lg">
    <div class="py-4 md:py-6 text-center flex justify-center">
        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($attributes['wallet_number']) !!}
    </div>
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-center border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-2">Wallet Number</div>
        <div class="overflow-x-auto col-span-4">
            {{ $attributes['wallet_number'] }}
        </div>
        <div class="col-span-2" x-data="{'input': '{{ $attributes['wallet_number'] }}' }">
            <button class="px-3 py-1 bg-blue-600 text-white rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Wallet Number is copied!')">
                Copy
            </button>
        </div>
    </div>
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-center border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-2">Amount</div>
        <div class="font-bold text-red-600 col-span-4">
            {{ $amount }} {{ $withdrawal->currency }}
        </div>
        <div class="col-span-2" x-data="{'input': '{{ $amount }}' }">
            <button class="px-3 py-1 bg-blue-600 text-white rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Amount is copied!')">
                Copy
            </button>
        </div>
    </div>
</div>
@endsection