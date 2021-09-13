<?php

namespace App\DTO;

use App\Casts\DataTransferObjectCast;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Spatie\DataTransferObject\DataTransferObject;

abstract class Base extends DataTransferObject implements Castable
{
    public static function castUsing(array $arguments)
    {
        return new DataTransferObjectCast(static::class);
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public static function fromJson($json)
    {
        return new static(json_decode($json, true));
    }
}
