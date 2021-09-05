@extends('layout')
@section('style')
<style>
    .countdown {
        text-transform: uppercase;
        font-weight: bold;
    }

    .countdown span {
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        font-size: 3rem;
        margin-left: 0.8rem;
    }

    .countdown span:first-of-type {
        margin-left: 0;
    }

    .countdown-circles {
        text-transform: uppercase;
        font-weight: bold;
    }

    .countdown-circles span {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    }

    .countdown-circles span:first-of-type {
        margin-left: 0;
    }
</style>
@endsection
@section('content')
<!-- Flexbox container for aligning the toasts -->
<div aria-live="polite" aria-atomic="true"
    class="d-flex justify-content-center align-items-center w-100 position-fixed top-10 " style="z-index: 11">
    <!-- Then put toasts within -->
    <div class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto text-center">Alert</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
        </div>
    </div>
</div>
<div class="container mt-5 p-5 rounded bg-gradient-4 shadow">
    <div class="mb-4">
        <h2>Payment details</h2>
    </div>
    @if($deposit->created_at->addHours(1) > \Carbon\Carbon::now())
    <div class="row" id="payment_detail">
        <div class="col-md-12">
            @if($deposit->status == 0)
            <form id="form" method="post" action="<?= app('api.url')->version(env('API_VERSION'))
                        ->route('api.deposits.update',$deposit->merchant_order_id) ?>">
                <input type="hidden" name="_method" value="put" />
                <input type="hidden" name="merchant_id" value="{{ $deposit->merchant_id }}" />
                @endif
                <div class="card p-3">
                    <div class="mb-3 row align-items-center">
                        <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">Order Id</label>
                        <label class="col-sm-10 col-form-label fw-bold fs-4 text-warning">
                            {{ $deposit->merchant_order_id }}
                        </label>
                    </div>
                    @includeFirst([$subview, 'texts.default'], ['attributes' => $attributes])
                    <div class="mb-3 row align-items-center">
                        <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">Amount</label>
                        <label for="staticEmail" class="col-sm-10 col-form-label fw-bold fs-4 text-warning">
                            {{ $deposit->amount }} {{ $deposit->currency }}
                        </label>
                    </div>
                    <div class="mb-3 row align-items-center">
                        <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">Reference No</label>
                        @if($deposit->status == 0)
                        <input class="col-sm-10 col-form-label fw-bold fs-5" name="reference_no" value=""
                            placeholder="Please input reference number" />
                        @else
                        <label for="staticEmail"
                            class="col-sm-2 col-form-label fw-bold fs-6">{{ $deposit->reference_no }}</label>
                        @endif
                    </div>
                    @if($deposit->status == 0)
                    <button type="submit" class="btn btn-primary">Submit</button>
                    @endif
                </div>
                @if($deposit->status == 0)
            </form>
            @endif
        </div>
    </div>
    @endif
    <div class="row text-center mt-2">
        <h5 id="expiration" class="display-4 mb-4 text-danger" style="display:none">Deposit has expired</h5>
        <div id="clock-c" class="countdown py-4"></div>
    </div>
</div>
@endsection
@section('js')
<script>
    $(document).ready(function() {
        @if($deposit->status == 0)
            $('#clock-c').countdown("<?= $deposit->created_at->addHours(1)->toDateTimeString() ?>", function(event) {
                var $this = $(this).html(event.strftime('' +
                    '<span class="h1 font-weight-bold">%M</span> Min' +
                    '<span class="h1 font-weight-bold">%S</span> Sec'));
            }).on('finish.countdown', function(e) {
                $('#expiration').show();
            });
            @if($deposit->created_at->addHours(1) <= \Carbon\Carbon::now())
            $('#expiration').show();
        @endif
        @else
            $('#expiration').text('Your order is pending, please wait it completed').show();
        @endif
        $( "#form").submit(function( event ) {
            event.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                type: 'put',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(data) {
                    location.reload();
                }
            });
        });
        var clipboard = new ClipboardJS('.btn');
        clipboard.on('success', function(e) {
            $('.toast .toast-body').text(e.text + ' is Copied!');
            $('.toast').toast('show');
        });
    });
</script>
@endsection