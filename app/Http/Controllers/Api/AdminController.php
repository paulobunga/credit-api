<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as Controller;
use App\Models\Admin;
use App\Transformers\AdminTransformer;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    public function index()
    {
        return $this->response->item(Admin::first(), new AdminTransformer);
    }
}
