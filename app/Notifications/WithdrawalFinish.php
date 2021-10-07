<?php

namespace App\Notifications;

class WithdrawalFinish extends Base
{
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->model->id,
            'title' => 'Payout finish',
            'body' => "Payout:{$this->model->merchant_order_id} is completed!",
            'time' => $this->model->updated_at->toDateTimeString(),
        ];
    }
}
