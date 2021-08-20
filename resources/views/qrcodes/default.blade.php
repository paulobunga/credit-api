<div class="mb-3 row">
    <div class="col-sm-12 mb-3 text-center">
        {!!\SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($attributes['qrcode'])!!}
    </div>
</div>
<div class="mb-3 row">
    <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">Qrcode</label>
    <div class="col-sm-10 mb-3">
        <div class="input-group">
            <label id="qrcode" type="text" class="form-control">
                {{ $attributes['qrcode']}}
            </label>
            <button class="btn btn-primary" data-clipboard-target="#qrcode">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
</div>