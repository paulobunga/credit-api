<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Transformers\PermissionTransformer;
use Dingo\Api\Http\Request;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    public function index()
    {
        return $this->response->collection(Permission::all(), new PermissionTransformer);
    }
}
