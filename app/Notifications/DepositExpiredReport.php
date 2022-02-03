<?php
namespace App\Notifications;

use App\Models\MerchantDeposit;
use Carbon\Carbon;

class DepositExpiredReport extends Base
{
    protected $reports = [];

    public function __construct($reports)
    {
      parent::__construct(MerchantDeposit::first());

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
        return admin_url('/notifications?id='.$this->getNotifyId());
    }

    /**
     * Get data of message
     *
     * @param  mixed  $notifiable
     * @return array
     */
    protected function getData($notifiable)
    {
        $body = "Total payin expired: ";
        foreach ($this->reports as $report => $rs) {
            foreach ($rs as $r => $v) {
                if ($r != 'data') {
                    $body .= "\n Agent " . $r . "(" . $report . "): " . $v . " orders";
                } else {
                    foreach ($v as $k => $data) {
                        $body .= "\n Merchant OrderId: " . $data->merchant_order_id . ', Amount: ' . $data->amount;
                    }
                }
            }
        }

        return [
          'id'=> $this->getNotifyId(),
          'title' => 'Payin Expired',
          'body' => $body,
          'time' => (string)Carbon::now()
        ];
    }
}
