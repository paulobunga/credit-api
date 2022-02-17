<?php

namespace App\Notifications\Admin;

use Carbon\Carbon;

class PayInOutOff extends Base
{
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
        return admin_url("/resellers?name={$this->model->name}&level={$this->model->level}");
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
            'title' => 'Agent offline',
            'body' => "Automatically turn off payin and payout status of {$this->model->name}.",
            'time' => Carbon::now()->toDateTimeString(),
        ];
    }
}
