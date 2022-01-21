@extends('texts.inr')

@section('attributes')
<div
    class="grid grid-flow-row sm:gap-4 gap-y-2 text-sm sm:text-md md:text-lg text-left">
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 sm:grid-cols-5 gap-x-2 border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-3 sm:col-span-2 tracking-tighter sm:tracking-normal">Account Number</div>
        <div class="overflow-x-auto col-span-3 sm:col-span-2 tracking-tighter sm:tracking-normal">
            {{ $attributes['account_number'] }}
        </div>
        <div class="col-span-2 sm:col-span-1" x-data="{'input': '{{ $attributes['account_number'] }}' }">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Account Number is copied!')">
                Copy
            </button>
        </div>
    </div>
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 sm:grid-cols-5 gap-x-2 border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-3 sm:col-span-2 tracking-tighter sm:tracking-normal">Account Name</div>
        <div class="overflow-x-auto col-span-3 sm:col-span-2 tracking-tighter sm:tracking-normal">
            {{ $attributes['account_name'] }}
        </div>
        <div class="col-span-2 sm:col-span-1" x-data="{'input': '{{ $attributes['account_name'] }}' }">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Account Name is copied!')">
                Copy
            </button>
        </div>
    </div>
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 sm:grid-cols-5 gap-x-2 border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-3 sm:col-span-2 tracking-tighter sm:tracking-normal">IFSC Code</div>
        <div class="overflow-x-auto col-span-3 sm:col-span-2 tracking-tighter sm:tracking-normal">
            {{ $attributes['ifsc_code'] }}
        </div>
        <div class="col-span-2 sm:col-span-1" x-data="{'input': '{{ $attributes['ifsc_code'] }}' }">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'IFSC Code is copied!')">
                Copy
            </button>
        </div>
    </div>
    <div
        class="py-4 md:py-6 grid grid-flow-col grid-cols-8 sm:grid-cols-5 gap-x-2 border-b-2 border-gray-400 border-opacity-25 items-center">
        <div class="font-bold col-span-3 sm:col-span-2 tracking-tighter sm:tracking-normal">Amount</div>
        <div class="font-bold text-red-600 col-span-3 sm:col-span-2 tracking-tighter sm:tracking-normal">
            {{ $amount }} {{ $deposit->currency }}
        </div>
        <div class="col-span-2 sm:col-span-1" x-data="{'input': '{{ $amount }}' }">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'Amount is copied!')">
                Copy
            </button>
        </div>
    </div>
</div>
@endsection