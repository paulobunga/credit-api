@extends('qrcodes.default')
@section('attributes')
<div class="w-full grid grid-flow-row sm:gap-4 gap-y-2 text-sm sm:text-lg">
    <div class="w-full py-4 md:py-6 text-center flex justify-center">
        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($attributes['qrcode']) !!}
    </div>
    <div
        class="w-full py-4 md:py-6 grid grid-flow-col gap-x-2 text-center border-b-2 border-gray-400 border-opacity-25">
        <div class="font-bold">QRCODE</div>
        <div class="overflow-x-auto">
            {{ $attributes['qrcode'] }}
        </div>
        <div x-data="{'input': '{{ $attributes['qrcode'] }}' }">
            <button class="px-3 sm:px-6 bg-yellow-200 text-yellow-800 rounded-full"
                x-on:click="$clipboard(input); $store.$alert.show('success', 'QRCODE is copied!')">
                <i class="far fa-copy"></i>
            </button>
        </div>
    </div>
</div>
@endsection