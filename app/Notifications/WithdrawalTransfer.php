<?php

namespace App\Notifications;

class WithdrawalTransfer extends Base
{
    /**
     * Get icon path
     *
     * @return string
     */
    protected function getIcon(): string
    {
        return reseller_url('/icons/favicon-32x32.png');
    }

    /**
     * Get notification link
     *
     * @return string
     */
    protected function getLink(): string
    {
        return reseller_url('/withdrawals');
    }
    /**
     * Get data of message
     *
     * @param  mixed  $notifiable
     * @return array
     */
    protected function getData($notifiable)
    {
        return [
            'id' => $this->model->id,
            'title' => 'Transfer payout order',
            'body' => "Your payout order {$this->model->merchant_order_id} was transfered to another agent.",
            'time' => $this->model->created_at->format('Y-m-d\TH:i:s.uP')
        ];
    }
}
