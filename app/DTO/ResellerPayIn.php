<?php

namespace App\DTO;

final class ResellerPayIn extends Base
{
    public float $commission_percentage;

    public int $pending_limit;

    public bool $status;

    public const STATUS = [
        'INACTIVE' => 'false',
        'ACTIVE' => 'true',
    ];
}
