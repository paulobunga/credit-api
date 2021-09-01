<div class="mb-3 row">
    <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">Account Number</label>
    <div class="col-sm-10 mb-3">
        <div class="input-group">
            <label id="account_no" type="text" class="form-control">
                {{ $attributes['account_number']  }}
            </label>
            <button class="btn btn-primary" data-clipboard-target="#account_no">
                <i class="fas fa-copy"> </i>
            </button>
        </div>
    </div>
</div>
<div class="mb-3 row">
    <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">
        Account Holder Name
    </label>
    <div class="col-sm-10 mb-3">
        <div class="input-group">
            <label id="account_name" type="text" class="form-control">
                {{ $attributes['account_name'] }}
            </label>
            <button class="btn btn-primary" data-clipboard-target="#account_name">
                <i class="fas fa-copy"> </i>
            </button>
        </div>
    </div>
</div>
<div class="mb-3 row">
    <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">IFSC Code</label>
    <div class="col-sm-10 mb-3">
        <div class="input-group">
            <label id="ifsc_code" type="text" class="form-control">
                {{ $attributes['ifsc_code'] }}
            </label>
            <button class="btn btn-primary" data-clipboard-target="#ifsc_code">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
</div>