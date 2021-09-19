<?php

namespace App\Exports\Admin;

use App\Exports\Base;

class ReportMerchantExport extends Base
{
    /**
     * Definition of filename
     * Attributes need to be overwritten
     */
    protected $fileName = 'report_merchant.xlsx';

    /**
     * Definition of heading and corresponding column name
     * Attributes need to be overwritten
     */
    protected $fields = [
        'id' => 'Id',
        'name' => 'Merchant name',
        'start_at' => 'Start at',
        'end_at' => 'End at',
        'turnover' => 'Turnover',
        'credit' => 'Credit',
        'transaction_fee' => 'Transaction fee',
        'currency' => 'Currency',
    ];
}
