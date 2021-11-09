@extends('layout')
@section('head')
<title>{{ env('APP_NAME') }} - Try PayOut</title>
@endsection
@section('content')
<h2 class="font-bold text-lg my-4">Demo PayOut</h2>
<form class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" x-data="channels" method="post" action="{{ $action }}">
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="currency">
            Currency
        </label>
        <select name="currency" x-model="currency" x-on:change="getChannels"
            class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
            @foreach ($currency as $c)
            <option val="{{ $c }}">{{ $c }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-6">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="channel">
            Payment Channel
        </label>
        <select name="channel" x-model="channel"
            class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
            <template x-for="c in getChannels">
                <option x-bind:val="c.name" x-text="c.name"></option>
            </template>
        </select>
    </div>
    <!-- VND NETBANK -->
    <div x-show="currency=='VND' && channel=='NETBANK'">
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="account_name">
                Account name
            </label>
            <input name="account_name"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='VND' && channel=='NETBANK')" />
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="account_number">
                Account number
            </label>
            <input name="account_number"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='VND' && channel=='NETBANK')" />
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="bank_name">
                Bank name
            </label>
            <input name="bank_name"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='VND' && channel=='NETBANK')" />
        </div>
    </div>
    <!-- INR NETBANK -->
    <div x-show="currency=='INR' && channel=='NETBANK'">
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="account_name">
                Account name
            </label>
            <input name="account_name"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='INR' && channel=='NETBANK')" />
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="account_number">
                Account number
            </label>
            <input name="account_number"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='INR' && channel=='NETBANK')" />
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="ifsc_code">
                IFSC Code
            </label>
            <input name="ifsc_code"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='INR' && channel=='NETBANK')" />
        </div>
    </div>
    <!-- INR UPI -->
    <div x-show="currency=='INR' && channel=='UPI'">
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="upi_id">
                UPI ID
            </label>
            <input name="upi_id"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='INR' && channel=='UPI')" />
        </div>
    </div>
    <!-- BDT BKASH -->
    <div x-show="currency=='BDT' && channel=='BKASH'">
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="wallet_number">
                Wallet Number
            </label>
            <input name="wallet_number"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='BDT' && channel=='BKASH')" />
        </div>
    </div>
    <!-- BDT NAGAD -->
    <div x-show="currency=='BDT' && channel=='NAGAD'">
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="wallet_number">
                Wallet Number
            </label>
            <input name="wallet_number"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='BDT' && channel=='NAGAD')" />
        </div>
    </div>
    <!-- BDT ROCKET -->
    <div x-show="currency=='BDT' && channel=='ROCKET'">
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="wallet_number">
                Wallet Number
            </label>
            <input name="wallet_number"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='BDT' && channel=='ROCKET')" />
        </div>
    </div>
    <!-- BDT UPAY -->
    <div x-show="currency=='BDT' && channel=='UPAY'">
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="wallet_number">
                Wallet Number
            </label>
            <input name="wallet_number"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                x-bind:disabled="!(currency=='BDT' && channel=='UPAY')" />
        </div>
    </div>
    <div class="mb-6">
        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grip-amount">
            Amount
        </label>
        <input name="amount"
            class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
            type="number" placeholder="Pleas input amount">
    </div>
    <div class="flex items-center justify-between">
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded
            focus:outline-none focus:shadow-outline" type="button">
            Submit
        </button>
    </div>
</form>
@endsection
@push('js')
<script>
    document.addEventListener('alpine:init', ()=>{
        Alpine.data('channels', ()=>({
            currency: '{{ $channels[0]["currency"]??'' }}',
            channel: '{{ $channels[0]["name"]??'' }}',
            _channels: @json($channels),
            getChannels(){
                return this._channels.filter(channel=>channel.currency == this.currency);
            },
            init(){
                this.$watch('currency', (value) => {
                    this.channel = this.getChannels()[0].name;
                })
            }
        }));
    });
</script>
@endpush