<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Currency implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return app('settings.currency')->has($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.currency');
    }
}
