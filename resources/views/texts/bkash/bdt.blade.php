@extends('texts.bdt')

@section('head')
<title>{{ env('APP_NAME') }} - BKash PayIn</title>
@endsection

@push('style')
<style>
    .gradient-color {
        --tw-gradient-from: #f472b6;
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgb(244 114 182 / 0));
        --tw-gradient-to: #dc2626;
    }

    .divider {
        --tw-border-opacity: 1;
        border-color: rgb(74 222 128 / var(--tw-border-opacity));
        border-style: dotted;
        border-top-width: 2px;
    }

    .btn {
        --tw-bg-opacity: 1;
        background-color: rgb(219 39 119 / var(--tw-bg-opacity));
    }
</style>
@endpush

@section('logo')
<img src="/img/logos/bkash.png" />
@endsection