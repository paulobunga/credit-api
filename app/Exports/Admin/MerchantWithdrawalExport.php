<?php

namespace App\Exports\Admin;

use App\Exports\Base;

class MerchantWithdrawalExport extends Base
{
    /**
     * Definition of filename
     * Attributes need to be overwritten
     */
    protected $fileName = 'merchant_deposit.xlsx';

    /**
     * Definition of heading and corresponding column name
     * Attributes need to be overwritten
     */
    protected $fields = [
        'id' => 'Id',
        'name' => 'Name',
        'order_id' => 'Order id',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'status' => 'Status',
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
                return $model->statusText;
            default:
                return (string) $val;
        }
    }
}
