<?php

namespace App\Notifications;

use App\Models\MerchantDeposit;
use Carbon\Carbon;

class DepositExpiredReport extends Base
{
    protected $reports = [];
    protected $notify_time;

    public function __construct($reports, $notify_time)
    {
        parent::__construct();

        $this->reports = $reports;
        $this->notify_time = $notify_time;
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
        return admin_url('/merchant_deposits?status=' . MerchantDeposit::STATUS['EXPIRED'] . '&updated_at_between=' . $this->notify_time . ',' . $this->notify_time);
    }

    /**
     * Get data of message
     *
     * @param  mixed  $notifiable
     * @return array
     */
    protected function getData($notifiable)
    {
        $body = "Total payin expired";
        foreach ($this->reports as $currency => $rs) {
            foreach ($rs as $agent => $v) {
                $body .= "\n" . $agent . "(" . $currency . "):" . $v["total_expired"] . " orders, Total Amount: " . $v["total_amount"];
            }
        }
        return [
            'id'    => \Illuminate\Support\Str::uuid(),
            'title' => 'Payin Expired',
            'body'  => $body,
            'time'  => Carbon::now()->toDateTimeString()
        ];
    }
}
