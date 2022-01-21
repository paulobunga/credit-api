<?php

namespace App\Http\Controllers\Admin;

use GuzzleHttp\Client;
use Dingo\Api\Http\Request;
use App\Http\Controllers\Controller;

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
}
