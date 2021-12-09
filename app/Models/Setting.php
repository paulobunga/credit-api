<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const CURRENCY_TIMEZONE = [
        'BDT' => 'Asia/Dhaka',
        'INR' => 'Asia/Kolkata',
        'VND' => 'Asia/Ho_Chi_Minh',
    ];

}
