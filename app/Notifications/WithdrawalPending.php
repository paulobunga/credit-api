<?php

namespace App\Notifications;

class WithdrawalPending extends Base
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
            'title' => 'New cash out order',
            'body' => "You got a new cash out order, {$this->model->order_id}.",
            'time' => $this->model->created_at->toDateTimeString(),
        ];
    }
}
