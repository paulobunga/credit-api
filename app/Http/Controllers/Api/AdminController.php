<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as Controller;
use App\Models\Admin;
use App\Transformers\AdminTransformer;

class AdminController extends Controller
{
    public function index()
    {
        throw new \Exception('test', 1234);
        return $this->response->item(Admin::first(), new AdminTransformer);
    }
}
