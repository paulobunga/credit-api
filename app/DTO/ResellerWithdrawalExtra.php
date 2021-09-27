<?php

namespace App\DTO;

final class ResellerWithdrawalExtra extends Base
{
    public ?string $payment_type;

    public ?string $reason;

    public ?string $remark;

    public ?string $memo;

    public int $creator;
}
