<?php

namespace App\Exports\Reseller;

use App\Exports\Base;

class ReportExport extends Base
{
    /**
     * Definition of filename
     * Attributes need to be overwritten
     */
    protected $fileName = 'reports.xlsx';

    /**
     * Definition of heading and corresponding column name
     * Attributes need to be overwritten
     */
    protected $fields = [
        'id' => 'Id',
        'start_at' => 'Start At',
        'end_at' => 'End At',
        'turnover' => 'Turnover',
        'credit' => 'Credit',
        'coin' => 'Coin',
    ];
}
