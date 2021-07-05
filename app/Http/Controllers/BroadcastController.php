<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BroadcastController extends Controller
{

    public function authenticate(Request $request)
    {
        $id = explode('.', $request->get('channel_name', '0'));
        $id = $id[array_key_last($id)];
        if ($request->user() == null) {
            throw new UnauthorizedHttpException('JWTAuth', 'Unauthorized');
        }

        if ($id != $request->user()->id) {
            throw new UnauthorizedHttpException('JWTAuth', 'Unauthorized');
        };

        return true;
    }
}
