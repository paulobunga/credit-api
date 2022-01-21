<?php

namespace App\Http\Controllers\Admin;

use GuzzleHttp\Client;
use Dingo\Api\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class Pm2Controller extends Controller
{
    public function index(Request $request)
    {
        $client = new Client();
        $res = $client->get('http://workspace:' . env('PM2_PORT'));
        return [
            'data' => json_decode($res->getBody()->getContents())
        ];
    }

    public function store(Request $request)
    {   
        $this->validate($request, [
            'worker_name' => 'required',
            'action' => 'required|in:restart,start,stop,delete'
        ]);
        $client = new Client();
        $res = $client->post('http://workspace:' . env('PM2_PORT').'/trigger', [
          'json' => [
              'worker_name' => $request->worker_name,
              'action' => $request->action
            ]
        ]);
        return [
            'data' => json_decode($res->getBody()->getContents())
        ];
    }
}
