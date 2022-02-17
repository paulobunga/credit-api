@extends('layout')

@section('head')
<title>{{ env('APP_NAME') }} - PayIn</title>
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
        <section class="text-gray-600 body-font flex flex-wrap shadow-lg" x-data="deposit">
            <div
                class="w-full md:w-2/5 mb-4 md:mb-0 overflow-hidden bg-gradient-to-b flex flex-col justify-center items-center py-5 text-white gradient-color">
                <div class="relative w-40 h-40 bg-white rounded-full overflow-hidden flex align-center justify-center">
                    @yield('logo')
                </div>
                <div class="my-6">
                    <x-timer :dateTime='$deposit->expired_at'>
                        <x-slot name="custom">
                            <div class="font-mono leading-none text-4xl minutes" x-text="minutes">00</div>
                            :
                            <div class="font-mono leading-none text-4xl seconds" x-text="seconds">00</div>
                        </x-slot>
                    </x-timer>
                </div>
                <template x-if="status == 1 && hasMobileNumber">
                    <p class="mt-4">Waiting Approval</p>
                </template>
                <template x-if="status == 1 && !hasMobileNumber">
                    <p class="mt-4">Time Remaing</p>
                </template>
                <template x-if="status == 2 || status == 4">
                    <p class="mt-4">Success</p>
                </template>
                <template x-if="status == 3 || status == 5">
                    <p class="mt-4">Failed</p>
                </template>
                <template x-if="status == 6">
                    <p class="mt-4">Expired</p>
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
                <form @submit.prevent="submit">
                    <div class="py-4 grid grid-flow-col grid-cols-3 gap-x-2 items-center">
                        <div class="col-span-1">Cash Out From</div>
                        <div class="col-span-2">
                            <input type="text" name="sender_mobile_number" class="rounded w-full py-2" x-bind:class="{
                                    'border-2 outline-pink-600 px-3': !hasMobileNumber,
                                    'border-0 outline-0': hasMobileNumber 
                                }" x-bind:readonly="hasMobileNumber" x-model="data.sender_mobile_number"
                                placeholder="Enter cash-out Mobile Number" />
                        </div>
                    </div>
                    <div class="py-4 flex w-full items-center justify-center" x-show="status==1 && !hasMobileNumber">
                        <button type="submit" class="px-6 py-3 text-white rounded-full w-full md:w-1/2 btn">
                            Submit
                        </button>
                    </div>
                </form>
            </div>

        </section>
    </div>
</div>
@endsection

@push('js')
<script>
    function deposit() {
      return {
            hasMobileNumber: "{{ !empty($deposit->extra['sender_mobile_number'] ?? '')? 'true': 'false' }}" === 'true',
            status: "{{ $deposit->status }}",
            data:{
                uuid: "{{ request()->uuid }}",
                time: "{{ request()->time }}",
                sign: "{{ request()->sign }}",
                merchant_order_id: "{{ $deposit->merchant_order_id }}",
                currency: "{{ $deposit->currency }}",
                sender_mobile_number: "{{ $deposit->extra['sender_mobile_number'] ?? '' }}",
            },
            async submit() {
                const response = await (await fetch('{{ apiRoute('api.deposits.update', $deposit->merchant_order_id) }}', {
                    method: 'PUT',
                    body: JSON.stringify(this.data),
                    headers: {
                        'Content-type': 'application/json; charset=UTF-8',
                    },
                })).json();
                Alpine.store('$alert').show(response.code == 200?'success':'error', response.message);
                if(response.code == 200){
                    this.hasMobileNumber = true;
                }
            },
        };
    }
</script>
@endpush