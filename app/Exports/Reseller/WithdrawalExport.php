<?php

namespace App\Exports\Reseller;

use App\Exports\Base;

class WithdrawalExport extends Base
{
    /**
     * Definition of filename
     * Attributes need to be overwritten
     */
    protected $fileName = 'withdrawals.xlsx';

    /**
     * Definition of heading and corresponding column name
     * Attributes need to be overwritten
     */
    protected $fields = [
        'id' => 'Id',
        'merchant_order_id' => 'Order Id',
        'channel' => 'Channel',
        'attributes' => 'Attributes',
        'amount' => 'Amount',
        'status' => 'Status',
        'extra' => 'Extra',
        'created_at' => 'created_at'
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
            case 'channel':
                return $model->paymentChannel->name;
            case 'attributes':
                return json_encode($val);
            case 'status':
                return $model->statusText;
            case 'extra':
                return json_encode($val);
            default:
                return (string) $val;
        }
    }
}
