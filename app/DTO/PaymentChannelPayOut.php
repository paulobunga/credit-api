<?php

namespace App\DTO;

final class PaymentChannelPayOut extends Base
{
    public int $min;

    public int $max;

    public bool $status;
}
