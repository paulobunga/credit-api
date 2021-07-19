<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

class CallbackJob extends Job
{
    protected $payload;
    protected $url;
    protected $method;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

    public $maxExceptions = 5;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $payload, String $url, String $method = 'POST')
    {
        $this->payload = $payload;
        $this->url = $url;
        $this->method = strtoupper($method);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client([
            'timeout'  => 15,
            'connect_timeout' => 15
        ]);
        $response = $client->request($this->method, $this->url, [
            'json' => $this->payload
        ]);
        $response = json_decode($response->getBody()->getContents(), true);
        \Log::info([
            'payload' => $this->payload,
            'response' => $response
        ]);
        if (!isset($response['message']) || $response['message'] != 'ok') {
            throw new \Exception('response format is incorrect');
        }
    }
}
