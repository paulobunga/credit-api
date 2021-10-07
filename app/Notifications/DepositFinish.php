<?php

namespace App\Notifications;

class DepositFinish extends Base
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
            'title' => 'Payin finish',
            'body' => "Payin:{$this->model->merchant_order_id} is completed!",
            'time' => $this->model->updated_at->toDateTimeString(),
        ];
    }
}
