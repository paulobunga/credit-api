<?php

namespace App\Exports\Admin;

use App\Exports\Base;

class BankExport extends Base
{
    /**
     * Definition of filename
     * Attributes need to be overwritten
     */
    protected $fileName = 'banks.xlsx';

    /**
     * Definition of heading and corresponding column name
     * Attributes need to be overwritten
     */
    protected $fields = [
        'id' => 'Bank id',
        'ident' => 'Bank ident',
        'name' => 'Bank name',
        'currency' => 'Currency',
        'status' => 'status',
    ];

    /**
     * Transform value by key or other attribute of model
     * @param mixed $model mixed model
     * @param string $key attribute key
     * @param mixed $val attribute value
     * @return string
     */
    protected function transform(mixed $model, String $key, mixed $val): String
    {
        switch ($key) {
            case 'status':
                return $val ? 'Active' : 'Inactive';
            default:
                return (string) $val;
        }
    }
}
