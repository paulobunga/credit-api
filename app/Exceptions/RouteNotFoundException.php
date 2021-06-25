<?php

namespace App\Exceptions;

use Exception;

class RouteNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('Route Not Found', 404);
    }
}
