<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class KeysIn implements Rule
{
    protected array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Swap keys with their values in our field list, so we
        // get ['foo' => 0, 'bar' => 1] instead of ['foo', 'bar']
        $allowedKeys = array_flip($this->values);
        // Compare the value's array *keys* with the flipped fields
        $unknownKeys = array_diff_key($value, $allowedKeys);
        // The validation only passes if there are no unknown keys

        return count($unknownKeys) === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.keysin');
    }
}
