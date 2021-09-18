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

<div class="w-full mt-3 py-2 md:py-3 text-center flex justify-center">
    <x-timer :dateTime='$deposit->expired_at' />
</div>
<div class="w-full mt-3 flex-1 text-center font-bold text-2xl">
    <div class="h-6 text-gray-800 text-lg leading-8 uppercase">Amount</div>
    <div class="text-red-700">
        {{ number_format($deposit->amount, 2, '.',  '') }} {{ $deposit->currency }}
    </div>
</div>
{{-- @if($deposit->status == 0)
<form id="form" method="post"
action="{{ app('api.url')->version(env('API_VERSION'))->route('api.deposits.update', $deposit->merchant_order_id) }}">
<input type="hidden" name="_method" value="put" />
<input type="hidden" name="merchant_id" value="{{ $deposit->merchant_id }}" />
@endif --}}
@section('attirbutes')
<div class="w-full grid grid-flow-row sm:gap-4 gap-y-2 text-sm sm:text-lg">
    <div class="w-full py-2 md:py-3 text-center flex justify-center">
        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($attributes['upi_id']) !!}
    </div>
    <div
        class="w-full py-2 md:py-3 grid grid-flow-col gap-x-2 text-center border-b-2 border-gray-400 border-opacity-25">
        <div class="uppercase font-bold">UPI ID</div>
        <div class="overflow-x-auto">
            {{ $attributes['upi_id'] }}
        </div>
        <div x-data="{'input': '{{ $attributes['upi_id'] }}' }">
            <button class="px-3 sm:px-6 bg-yellow-200 text-yellow-800 rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'UPI ID is copied!')">
                <i class="far fa-copy"></i>
            </button>
        </div>
    </div>
</div>
@show
{{-- @if($deposit->status == 0)
<button type="submit" class="btn btn-primary">Submit</button>
</form>
@endif --}}

@endsection