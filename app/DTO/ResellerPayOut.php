<?php

namespace App\DTO;

final class ResellerPayOut extends Base
{
    public float $commission_percentage;

    public int $pending_limit;

    public bool $status;

    public int $daily_amount_limit;

    public const STATUS = [
        'INACTIVE' => 'false',
        'ACTIVE' => 'true',
    ];
}
