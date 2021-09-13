<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Spatie\DataTransferObject\DataTransferObject;

class DataTransferObjectCast implements CastsAttributes
{
    protected string $class;

    /**
     * @param string $class The DataTransferObject class to cast to
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return DataTransferObject|null
     */
    public function get($model, $key, $value, $attributes): ?DataTransferObject
    {
        if (is_null($value)) {
            return null;
        }

        return new $this->class(json_decode($value, true));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     * @throws \Exception
     */
    public function set($model, $key, $value, $attributes): string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            $value = new $this->class($value);
        }

        if (!$value instanceof  $this->class) {
            throw new \Exception("The provided value must be an instance of " . $this->class);
        }

        return json_encode($value->toArray());
    }
}
