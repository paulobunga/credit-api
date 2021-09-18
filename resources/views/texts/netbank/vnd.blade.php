@extends('texts.default')
@section('attributes')
<div class="w-full grid grid-flow-row sm:gap-4 gap-y-2 text-xs tracking-tighter sm:tracking-normal sm:text-md md:text-lg">
    <div
        class="w-full py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-left border-b-2 border-gray-400 border-opacity-25">
        <div class="font-bold col-span-3">Account Number</div>
        <div class="overflow-x-auto col-span-4">
            {{ $attributes['account_number'] }}
        </div>
        <div class="col-span-1" x-data="{'input': '{{ $attributes['account_number'] }}' }">
            <button class="px-3 sm:px-6 bg-yellow-200 text-yellow-800 rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Account Number is copied!')">
                <i class="far fa-copy"></i>
            </button>
        </div>
    </div>
    <div
        class="w-full py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-left border-b-2 border-gray-400 border-opacity-25">
        <div class="font-bold col-span-3">Account Name</div>
        <div class="overflow-x-auto col-span-4">
            {{ $attributes['account_name'] }}
        </div>
        <div class="col-span-1" x-data="{'input': '{{ $attributes['account_name'] }}' }">
            <button class="px-3 sm:px-6 bg-yellow-200 text-yellow-800 rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Account Name is copied!')">
                <i class="far fa-copy"></i>
            </button>
        </div>
    </div>
    <div
        class="w-full py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-left border-b-2 border-gray-400 border-opacity-25">
        <div class="font-bold col-span-3">Bank Name</div>
        <div class="overflow-x-auto col-span-4">
            {{ $attributes['bank_name'] }}
        </div>
        <div class="col-span-1" x-data="{'input': '{{ $attributes['bank_name'] }}' }">
            <button class="px-3 sm:px-6 bg-yellow-200 text-yellow-800 rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Bank Name is copied!')">
                <i class="far fa-copy"></i>
            </button>
        </div>
    </div>
</div>
@endsection