<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ExistCurrency implements Rule
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
        $settings = app(\App\Settings\CurrencySetting::class);
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                return $settings->checkExsit($k);
            }
        }
        return $settings->checkExsit($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.currency.exist');
    }
}
