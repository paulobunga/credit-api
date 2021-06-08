<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Laravel\Lumen\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use Helpers;
}
