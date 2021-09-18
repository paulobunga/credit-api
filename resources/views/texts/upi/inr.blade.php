@extends('texts.default')
@section('attributes')
<div class="w-full grid grid-flow-row sm:gap-4 gap-y-2 text-sm sm:text-lg">
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
@endsection