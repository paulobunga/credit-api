<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Log\Events\MessageLogged;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use App\Models\Log as LogModel;

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
            if ($e = $msg->context['exception'] ?? null) {
                $msg->context['exception'] = [
                    'code' => $e->getCode(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'message' => $e->getMessage(),
                ];
            }
            $context = [
                'request' => [
                    'url' => request()->fullUrl(),
                    'data' => request()->all(),
                ],
                'context' => $msg->context,
            ];
            $this->model->create([
                'message'       => $msg->message,
                'channel'       => Log::getName(),
                'level'         => $msg->level,
                'context'       => $context
            ]);
        });
    }
}
