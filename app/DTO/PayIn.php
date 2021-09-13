<?php

namespace App\DTO;

final class PayIn extends Base
{
    public int $min;

    public int $max;

    public bool $status;
}
