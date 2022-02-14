@extends('payouts.bdt')

@section('head')
<title>{{ env('APP_NAME') }} - Upay PayOut</title>
@endsection

@push('style')
<style>
  .gradient-color {
    --tw-gradient-from: #4D87C1;
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgb(77, 135, 193 / 0));
    --tw-gradient-to: #1856AB;
  }

  .divider {
    --tw-border-opacity: 1;
    border-color: rgb(39 39 42) / var(--tw-border-opacity));
    border-style: dotted;
    border-top-width: 2px;
  }

  .btn {
    --tw-bg-opacity: 1;
    background-color: rgb(250 204 21/ var(--tw-bg-opacity));
    color: #000;
  }

  .relative.bg-white {
    background: #E5E5E5;
  }
</style>
@endpush

@section('logo')
<img src="/img/logos/upay.png" />
@endsection