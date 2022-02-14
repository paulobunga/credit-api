@extends('payouts.bdt')

@section('head')
<title>{{ env('APP_NAME') }} - Rocket PayOut</title>
@endsection

@push('style')
<style>
  .gradient-color {
    --tw-gradient-from: #8A238E;
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgb(138, 35, 142 / 0));
    --tw-gradient-to: #631368;
  }

  .divider {
    --tw-border-opacity: 1;
    border-color: rgb(39 39 42) / var(--tw-border-opacity));
    border-style: dotted;
    border-top-width: 2px;
  }

  .btn {
    --tw-bg-opacity: 1;
    background-color: rgb(126 34 206 / var(--tw-bg-opacity));
  }

  .relative.bg-white {
    background: inherit;
  }
</style>
@endpush

@section('logo')
<img src="/img/logos/rocket.png" />
@endsection