<?php

namespace App\Exports\Admin;

use App\Exports\Base;

class ResellerSmsExport extends Base
{
    /**
     * Definition of filename
     * Attributes need to be overwritten
     */
    protected $fileName = 'agent_sms.xlsx';

    /**
     * Definition of heading and corresponding column name
     * Attributes need to be overwritten
     */
    protected $fields = [
        'id' => 'Id',
        'agent' => 'Agent',
        'address' => 'Address',
        'trx_id' => 'Trx_id',
        'sim_num' => 'Sim No',
        'body' => 'Body',
        'status' => 'Status',
        'sent_at' => 'Sent At',
        'received_at' => 'Received At',
        'created_at' => 'Created At'
    ];

    /**
     * Transform value by key or other attribute of model
     * @param mixed $model mixed model
     * @param string $key attribute key
     * @param mixed $val attribute value
     * @return string
     */
    protected function transform(mixed $model, string $key, mixed $val): string
    {
        switch ($key) {
            case 'agent':
                return $model->reseller->name;
            default:
                return (string) $val;
        }
    }
}
