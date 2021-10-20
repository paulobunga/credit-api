<?php

namespace App\Notifications;

class DepositPending extends Base
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
        return reseller_url('/deposits?merchant_order_id=' . $this->model->merchant_order_id);
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
            'title' => 'New cash in order',
            'body' => "You got a new cash in order, {$this->model->merchant_order_id}.",
            'time' => $this->model->created_at->toDateTimeString(),
        ];
    }
}
