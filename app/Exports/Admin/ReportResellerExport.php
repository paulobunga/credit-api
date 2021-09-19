<?php

namespace App\Exports\Admin;

use App\Exports\Base;

class ReportResellerExport extends Base
{
    /**
     * Definition of filename
     * Attributes need to be overwritten
     */
    protected $fileName = 'report_agent.xlsx';

    /**
     * Definition of heading and corresponding column name
     * Attributes need to be overwritten
     */
    protected $fields = [
        'id' => 'Id',
        'name' => 'Agent name',
        'start_at' => 'Start at',
        'end_at' => 'End at',
        'turnover' => 'Turnover',
        'credit' => 'Credit',
        'coin' => 'Coin',
        'currency' => 'Currency',
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
            case 'currency':
                return $model->reseller->currency;
            default:
                return (string) $val;
        }
    }
}
