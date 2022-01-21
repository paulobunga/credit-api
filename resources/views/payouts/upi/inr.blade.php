@extends('layout')

@section('head')
<title>{{ env('APP_NAME') }} - PayOut</title>
@endsection

@section('content')
<div class="bg-white">
    <x-alert />
    <div class="sm:max-w-2xl md:container lg:max-w-4xl mx-auto px-4 sm:px-6 py-2">
        <x-stepper :steps="$steps" />
        <div class="py-4 md:py-6 text-center flex justify-center">
            <x-timer :dateTime='$withdrawal->expired_at' />
        </div>

        <div class="grid grid-flow-row sm:gap-y-4 gap-y-2 text-sm sm:text-lg">
            <div class="py-4 md:py-6 text-center flex justify-center">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($attributes['upi_id']) !!}
            </div>
            <div
                class="py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-center border-b-2 border-gray-400 border-opacity-25 items-center">
                <div class="font-bold col-span-2">UPI ID</div>
                <div class="overflow-x-auto col-span-4">
                    {{ $attributes['upi_id'] }}
                </div>
                <div class="col-span-2" x-data="{'input': '{{ $attributes['upi_id'] }}' }">
                    <button class="px-3 py-1 bg-blue-600 text-white rounded-full"
                        x-on:click="$clipboard(input); $store.$alert.show('success', 'UPI ID is copied!')">
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
    </div>
</div>
@endsection