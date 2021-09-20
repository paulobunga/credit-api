<?php

namespace App\Exports\Admin;

use App\Exports\Base;

class ResellerWithdrawalExport extends Base
{
    /**
     * Definition of filename
     * Attributes need to be overwritten
     */
    protected $fileName = 'agent_withdrawals.xlsx';

    /**
     * Definition of heading and corresponding column name
     * Attributes need to be overwritten
     */
    protected $fields = [
        'id' => 'Id',
        'name' => 'Name',
        'genre' => 'Genre',
        'admin' => 'Auditor',
        'order_id' => 'Order id',
        'type' => 'Type',
        'amount' => 'Amount',
        'status' => 'Status',
        'extra' => 'Extra',
        'created_at' => 'Created At'
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
            case 'genre':
                return in_array($model->transaction_type, [
                    6,
                    7,
                    8,
                ]) ? 'Agent Apply' : 'Admin Apply';
            case 'type':
                return $model->typeText;
            case 'status':
                return $model->statusText;
            case 'extra':
                return json_encode($val);
            default:
                return (string) $val;
        }
    }
}
