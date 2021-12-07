<?php

namespace App\Trait;

trait HasOnline
{
    public function online()
    {
        return $this->morphOne(\App\Models\Online::class, 'user');
    }
}
