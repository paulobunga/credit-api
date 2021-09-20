<?php

namespace App\Exports\Admin;

use App\Exports\Base;

class MerchantDepositExport extends Base
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
        'order_id' => 'Order id',
        'merchant_order_id' => 'Merchant order id',
        'merchant_name' => 'Merchant name',
        'reseller_name' => 'Agent name',
        'channel' => 'Channel',
        'method' => 'Method',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'status' => 'Status',
        'created_at' => 'Created time',
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
