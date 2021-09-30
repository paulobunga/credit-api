<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use League\Fractal\TransformerAbstract;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Trait\SignValidator;

class GuzzleJob extends Job
{
    use SignValidator;

    protected $model;

    protected $transformer;

    protected $key;

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
    public function __construct(
        Model $model,
        TransformerAbstract $transformer,
        String $key,
        String $method = 'POST'
    ) {
        $this->model = $model;
        $this->transformer = $transformer;
        $this->key = $key;
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
        $data = [];
        // create payload
        $payload = new \League\Fractal\Resource\Item(
            $this->model,
            $this->transformer,
            false
        );
        $payload = app('api.transformer')->getFractal()->createData($payload)->toArray();
        if (env('APP_ENV') !== 'production') {
            $payload['uuid'] = $this->model->merchant->uuid;
            $data['verify'] =  false;
        }
        $payload['sign'] = $this->createSign($payload, $this->key);
        $data['json'] = $payload;
        $response = null;
        try {
            $response = $client->request($this->method, $this->model->callback_url, $data);
            $response = json_decode($response->getBody()->getContents(), true);
            if (!isset($response['message']) || $response['message'] != 'ok') {
                throw new \Exception('response format is incorrect');
            }
            Log::channel('callback')->info([
                'data' => $data,
                'response' => $response
            ]);
            $this->model->timestamps = false;
            $this->model->callback_status = 2;
            $this->model->notified_at = Carbon::now();
            $this->model->save();
        } catch (\Exception $e) {
            Log::channel('callback')->error([
                'data' => $data,
                'response' => $response,
                'exception' => $e->getMessage()
            ]);
            $this->model->timestamps = false;
            $this->model->attempts = $this->model->attempts + 1;
            $this->model->save();
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        $this->model->timestamps = false;
        $this->model->callback_status = 3;
        $this->model->save();
    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->model->id)];
    }
}
