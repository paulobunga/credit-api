@extends('texts.default')
@section('attributes')
<div
    class="grid grid-flow-row sm:gap-4 gap-y-2 text-sm sm:text-md md:text-lg">
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-center border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-2">Account Number</div>
        <div class="overflow-x-auto col-span-4">
            {{ $attributes['account_number'] }}
        </div>
        <div class="col-span-2" x-data="{'input': '{{ $attributes['account_number'] }}' }">
            <button class="px-3 py-1 bg-blue-600 text-white rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Account Number is copied!')">
                Copy
            </button>
        </div>
    </div>
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-center border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-2">Account Name</div>
        <div class="overflow-x-auto col-span-4">
            {{ $attributes['account_name'] }}
        </div>
        <div class="col-span-2" x-data="{'input': '{{ $attributes['account_name'] }}' }">
            <button class="px-3 py-1 bg-blue-600 text-white rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Account Name is copied!')">
                Copy
            </button>
        </div>
    </div>
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-center border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-2">Bank Name</div>
        <div class="overflow-x-auto col-span-4">
            {{ $attributes['bank_name'] }}
        </div>
        <div class="col-span-2" x-data="{'input': '{{ $attributes['bank_name'] }}' }">
            <button class="px-3 py-1 bg-blue-600 text-white rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Bank Name is copied!')">
                Copy
            </button>
        </div>
    </div>
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 gap-x-2 text-center border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-2">Amount</div>
        <div class="font-bold text-red-600 col-span-4">
            {{ $amount }} {{ $deposit->currency }}
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