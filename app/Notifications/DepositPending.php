<?php

namespace App\Notifications;

class DepositPending extends Base
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
            'title' => 'New cash in order',
            'body' => "You got a new cash in order, {$this->model->order_id}.",
            'time' => $this->model->created_at->toDateTimeString(),
        ];
    }
}
