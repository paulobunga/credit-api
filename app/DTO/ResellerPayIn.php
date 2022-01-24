<?php

namespace App\DTO;

final class ResellerPayIn extends Base
{
    public float $commission_percentage;

    public int $pending_limit;

    public bool $status;

    public bool $auto_sms_approval;

    public int $min;

    public int $max;

    public const STATUS = [
        'INACTIVE' => 'false',
        'ACTIVE' => 'true',
    ];
}
