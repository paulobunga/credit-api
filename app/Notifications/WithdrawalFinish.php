<?php

namespace App\Notifications;

class WithdrawalFinish extends Base
{
    /**
     * Get icon path
     *
     * @return string
     */
    protected function getIcon(): string
    {
        return merchant_url('/icons/favicon-32x32.png');
    }

    /**
     * Get notification link
     *
     * @return string
     */
    protected function getLink(): string
    {
        return merchant_url('/withdrawals?merchant_order_id=' . $this->model->merchant_order_id);
    }
    /**
     * Get data of message.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    protected function getData($notifiable)
    {
        return [
            'id' => $this->model->id,
            'title' => 'Payout finish',
            'body' => "Payout:{$this->model->merchant_order_id} is completed!",
            'time' => $this->model->updated_at->format('Y-m-d\TH:i:s.uP'),
        ];
    }
}
