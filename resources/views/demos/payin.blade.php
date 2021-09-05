@extends('layout')
@section('content')
    <div class="container mt-5 p-5 rounded bg-gradient-4 shadow">
        <div class="mb-4">
            <h2>Demo Payin</h2>
        </div>
        <form method="post" action="{{ app('api.url')->version(env('API_VERSION'))->route('api.demos.payin.create') }}">
            <div class="form-group mb-4">
                <label for="currency">Currency</label>
                <select class="form-control" name="currency" id="currency">
                    @foreach ($currency as $c)
                        <option val="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-4">
                <label for="channel">Payment Channel</label>
                <select class="form-control" name="channel" id="channel">
                </select>
            </div>
            <div class="form-group mb-4">
                <label for="method">Method</label>
                <select class="form-control" name="method" id="method">
                    @foreach ($channels as $c)
                        @foreach ($c->paymentMethods as $m)
                            <option val="{{ $m }}" data-channel="{{ $c->name }}">
                                {{ $m }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-4">
                <label for="amount">Amount</label>
                <input type="number" class="form-control" name="amount" placeholder="please input valid amount">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
@endsection
@section('js')
    <script>
        $(document).ready(function() {
            var channels = 
            {!! json_encode(                
                collect($channels)->map(function($c){
                    return [
                        'name' => $c->name,
                        'currency' => $c->currency,
                        'methods' => $c->payment_methods,
                    ];
                }));
            !!}
            $('#currency').change(function(){
                $('#channel').empty();
                channels.filter(v=>v.currency == $(this).val()).forEach(c => {
                    $('#channel').append(`<option val="${c.name}">${c.name}</option>`);
                });
                $('#channel').trigger('change');
            });
            $('#channel').change(function(){
                $('#method').empty();
                const channel = channels.find(v=>v.name == $(this).val());
                if(channel){
                    channel.methods.forEach(m => {
                        $('#method').append(`<option val="${m}">${m}</option>`);
                    });
                }
            });
            $('#currency').trigger('change');
        });
    </script>
@endsection
