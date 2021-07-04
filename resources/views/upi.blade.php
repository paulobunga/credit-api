<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <style>
        */ .countdown {
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
</head>

<body>
    <!-- Flexbox container for aligning the toasts -->
    <div aria-live="polite" aria-atomic="true" class="d-flex justify-content-center align-items-center w-100 position-fixed top-10 " style="z-index: 11">
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
                <form 
                    id="form"
                    method="post" 
                    action="<?= app('Dingo\Api\Routing\UrlGenerator')->version(env('API_VERSION'))
                        ->route('api.deposits.update',$deposit->merchant_order_id) ?>">
                        <input type="hidden" name="_method" value="put" />
                        <input type="hidden" name="merchant_id" value="{{ $deposit->merchant_id }}" />
                    @endif
                    <div class="card p-3">
                        <div class="mb-3 row">
                            <div class="col-sm-12 mb-3 text-center">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($deposit->resellerBankCard->account_no) !!}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">UPI ID</label>
                            <div class="col-sm-10 mb-3">
                                <div class="input-group">
                                    <input id="account_no" type="text" class="form-control readonly" required="required" readonly value="<?= $deposit->resellerBankCard->account_no ?>">
                                    <button class="btn btn-primary" data-clipboard-target="#account_no"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">Amount</label>
                            <label for="staticEmail" class="col-sm-10 col-form-label fw-bold fs-4 text-warning"><?= $deposit->amount ?></label>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">Reference No</label>
                            @if($deposit->status == 0)
                            <input class="col-sm-10 col-form-label fw-bold fs-5" name="reference_no" value="" placeholder="Please input reference number"/>
                            @else 
                            <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">{{ $deposit->reference_no }}</label>
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
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.countdown/2.2.0/jquery.countdown.min.js"></script>
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

</html>