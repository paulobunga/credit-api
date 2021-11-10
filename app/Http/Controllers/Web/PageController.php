<?php

namespace App\Http\Controllers\Web;

use Laravel\Lumen\Routing\Controller;
use Dingo\Api\Http\Request;

class PageController extends Controller
{
    /**
     * Landing Page
     * @param \Dingo\Api\Http\Request $request
     * @return html
     */
    public function index(Request $request)
    {
        return view('www.index');
    }
}
