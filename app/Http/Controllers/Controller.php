<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Laravel\Lumen\Routing\Controller as BaseController;
use Dingo\Api\Http\Response;

abstract class Controller extends BaseController
{
    use Helpers;

    public function __construct()
    {
        $this->perPage = min(request()->get('per_page', 10), 100);
    }

    protected function success()
    {
        return [
            'message' => 'success'
        ];
    }
}
