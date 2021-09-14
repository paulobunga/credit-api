<?php

namespace App\DTO;

final class PayOut extends Base
{
    public int $min;

    public int $max;

    public bool $status;
}
