<div class="mb-3 row">
    <label for="staticEmail" class="col-sm-2 col-form-label fw-bold fs-6">upi_id</label>
    <div class="col-sm-10 mb-3">
        <div class="input-group">
            <label id="upi_id" type="text" class="form-control readonly" required="required"
                readonly>
                {{ $attributes['upi_id']}}"
            </label>
            <button class="btn btn-primary" data-clipboard-target="#upi_id">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
</div>