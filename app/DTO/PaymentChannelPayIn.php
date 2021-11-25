<?php

namespace App\DTO;

final class PaymentChannelPayIn extends Base
{
    public int $min;

    public int $max;

    public array $sms_addresses;

    public bool $status;
}
