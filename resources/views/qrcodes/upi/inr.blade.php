<div class="mb-3 row">
    <div class="col-sm-12 mb-3 text-center">
        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($attributes['upi_id']) !!}
    </div>
</div>