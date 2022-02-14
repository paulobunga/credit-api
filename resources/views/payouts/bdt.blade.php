@extends('layout')

@section('head')
<title>{{ env('APP_NAME') }} - PayOut</title>
@endsection

@push('style')
<style>
    .timer-container {
        grid-template-columns: none !important;
    }
</style>
@endpush

@section('content')
<div class="bg-white">
    <x-alert />
    <div
        class="md:container lg:max-w-4xl w-full mx-auto md:absolute md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 ">
        <section class="text-gray-600 body-font flex flex-wrap shadow-lg" x-data="withdrawal">
            <div
                class="w-full md:w-2/5 mb-4 md:mb-0 overflow-hidden bg-gradient-to-b flex flex-col justify-center items-center py-5 text-white gradient-color">
                <div class="relative w-40 h-40 bg-white rounded-full overflow-hidden flex align-center justify-center">
                    @yield('logo')
                </div>
                <div class="my-6">
                    <x-timer :dateTime='$withdrawal->expired_at'>
                        <x-slot name="custom">
                            <div class="font-mono leading-none text-4xl minutes" x-text="minutes">00</div>
                            :
                            <div class="font-mono leading-none text-4xl seconds" x-text="seconds">00</div>
                        </x-slot>
                    </x-timer>
                </div>
                <template x-if="status == 1">
                    <p class="mt-4">Time Remaing</p>
                </template>
                <template x-if="status == 2 || status == 4">
                    <p class="mt-4">Success</p>
                </template>
                <template x-if="status == 3 || status == 5">
                    <p class="mt-4">Failed</p>
                </template>
            </div>
            <div class="w-full md:w-3/5 flex flex-col flex-wrap text-center md:text-left p-6">
                <p class="py-6 text-center">
                    Cash Out to the account below and fill in the required information.<br />
                    নীচের অ্যাকাউন্টে ক্যাশ আউট করুন এবং প্রয়োজনীয় তথ্য পূরণ করুন!
                </p>
                <div class="w-full divider"></div>
                <div class="py-4 grid grid-flow-col grid-cols-3 gap-x-2 items-center">
                    <div class="col-span-1">Amount</div>
                    <div class="font-bold col-span-1">
                        ৳ {{ $amount }}
                    </div>
                    <div class="col-span-1 text-center" x-data="{'input': '{{ $amount }}' }">
                        <button class="px-3 py-1 text-white rounded-full btn"
                            x-on:click="$clipboard(input); $store.$alert.show('success', '{{ $amount }} is copied!')">
                            Copy
                        </button>
                    </div>
                </div>
                <div class="py-4 grid grid-flow-col grid-cols-3 gap-x-2 items-center">
                    <div class="col-span-1">{{ $channel->name }} Agent</div>
                    <div class="font-bold col-span-1">
                        {{ $attributes['wallet_number'] }}
                    </div>
                    <div class="col-span-1 text-center" x-data="{'input': '{{ $attributes['wallet_number']  }}' }">
                        <button class="px-3 py-1 text-white rounded-full btn"
                            x-on:click="$clipboard(input); $store.$alert.show('success', '{{ $attributes['wallet_number']  }} is copied!')">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('js')
<script>
    function withdrawal() {
      return {
            status: "{{ $withdrawal->status }}",
        };
    }
</script>
@endpush