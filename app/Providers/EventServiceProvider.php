<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Log\Events\MessageLogged;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use App\Models\Log as LogModel;
use Illuminate\Support\Facades\Schema;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    protected $model;

    public function __construct()
    {
        $this->model = new LogModel();
    }
    /**
     * Register Event Service Provider
     *
     * @return void
     */
    public function register()
    {
        Log::listen(function (MessageLogged $msg) {
            $context = [
                'request' => [
                    'url' => request()->fullUrl(),
                    'data' => request()->all(),
                ],
                'context' => [
                    'exception' => $msg->context['exception']->__toString()
                ],
            ];
            $this->model->create([
                'message'       => $msg->message,
                'channel'       => Log::getName(),
                'level'         => $msg->level,
                'context'       => Schema::connection('log')
                                    ->getColumnType(
                                        $this->model->setTable($this->model->getTable())
                                        ->getTable(),
                                        'context'
                                    ) === 'json'
                                    ? $context
                                    : json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            ]);
        });
    }
}
