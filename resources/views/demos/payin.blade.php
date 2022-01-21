@extends('layout')

@section('head')
<title>{{ env('APP_NAME') }} - Try PayIn</title>
@endsection

@section('content')
<div class="sm:max-w-2xl md:container lg:max-w-4xl mx-auto px-4 sm:px-6 py-2">
    <h2 class="font-bold text-lg my-4">Demo Payin</h2>
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
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="method">
                Payment Method
            </label>
            <select name="method" x-model="method"
                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                <template x-for="m in getMethods">
                    <option x-bind:val="m" x-text="m"></option>
                </template>
            </select>
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
</div>
@endsection

@push('js')
<script>
    document.addEventListener('alpine:init', ()=>{
        Alpine.data('channels', ()=>({
            currency: '{{ $channels[0]["currency"]??'' }}',
            channel: '{{ $channels[0]["name"]??'' }}',
            method: '{{ $channels[0]["methods"][0]??'' }}',
            _channels: @json($channels),
            getChannels(){
                return this._channels.filter(channel=>channel.currency == this.currency);
            },
            getMethods(){
                const $channel = this._channels.find(c => c.name == this.channel);
                return $channel?.methods || [];
            },
            init(){
                this.$watch('channel', (value) => {
                    this.method = this.getMethods()[0];
                })
                this.$watch('currency', (value) => {
                    this.channel = this.getChannels()[0].name;
                })
            }
        }));
    });
</script>
@endpush