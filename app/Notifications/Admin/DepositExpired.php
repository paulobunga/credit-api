<?php

namespace App\Notifications\Admin;

use Carbon\Carbon;
use App\Models\MerchantDeposit;

class DepositExpired extends Base
{
    protected $reports = [];

    public function __construct($reports)
    {
        parent::__construct();

        $this->reports = $reports;
    }

    /**
     * Get icon path
     *
     * @return string
     */
    protected function getIcon(): string
    {
        return admin_url('/icons/favicon-32x32.png');
    }

    /**
     * Get notification link
     *
     * @return string
     */
    protected function getLink(): string
    {
        return admin_url('/merchant_deposits?status=' .
            MerchantDeposit::STATUS['EXPIRED'] .
            '&reseller_name=' .
            $this->reports["agent"]);
    }

    /**
     * Get data of message
     *
     * @param  mixed  $notifiable
     * @return array
     */
    protected function getData($notifiable)
    {
        $body = <<<EOT
                Payin Order Expired for {$this->reports["agent"]} ({$this->reports["currency"]})
                Total Order: {$this->reports["count"]}
                Total Amount: {$this->reports["amount"]}
                Merchant Order:
                EOT;
        $body .= PHP_EOL . implode(PHP_EOL, $this->reports["merchant_order_id"]);

        return [
            'title' => 'Payin Order Expired',
            'body'  => $body,
            'time'  => Carbon::now()->toDateTimeString()
        ];
    }
}
