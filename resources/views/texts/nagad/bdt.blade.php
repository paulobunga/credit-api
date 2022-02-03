@extends('texts.bdt')

@section('head')
<title>{{ env('APP_NAME') }} - NAGAD PayIn</title>
@endsection

@push('style')
<style>
    .gradient-color {
        --tw-gradient-from: #F5811C;
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgb(245, 129, 28 / 0));
        --tw-gradient-to: #E92321;
    }

    .divider {
        --tw-border-opacity: 1;
        border-color: rgb(39 39 42) / var(--tw-border-opacity));
        border-style: dotted;
        border-top-width: 2px;
    }

    .btn {
        --tw-bg-opacity: 1;
        background-color: rgb(234 88 12 / var(--tw-bg-opacity));
    }
</style>
@endpush

@section('logo')
<img src="/img/logos/nagad.png" />
@endsection