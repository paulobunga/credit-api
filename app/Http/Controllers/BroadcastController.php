<?php

namespace App\Http\Controllers;

use Dingo\Api\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;


class BroadcastController extends Controller
{

    public function authenticate(Request $request)
    {
        $channel = array_slice(explode('.', $request->get('channel_name', '')), -2, 2);
        $guard = strtolower($channel[0]) ?? null;
        $id = $channel[1] ?? null;
        if ($guard == null || $id == null) {
            throw new UnauthorizedHttpException('JWTAuth', 'Unauthorized');
        }
        if (Auth::guard($guard)->guest()) {
            throw new UnauthorizedHttpException('JWTAuth', 'Unauthorized');
        }

        try {
            $this->validateAuthorizationHeader($request);

            $token = $this->parseAuthorizationHeader($request);
            if (!$user = Auth::guard($guard)->setToken($token)->authenticate()) {
                throw new UnauthorizedHttpException('JWTAuth', 'Unable to authenticate with invalid token.');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
        \Log::info($user);
        if ($id != $user->id) {
            throw new UnauthorizedHttpException('JWTAuth', 'Unauthorized');
        };

        return true;
    }

    protected function validateAuthorizationHeader(Request $request)
    {
        if (Str::startsWith(strtolower($request->headers->get('authorization')), $this->getAuthorizationMethod())) {
            return true;
        }

        throw new BadRequestHttpException;
    }

    protected function parseAuthorizationHeader(Request $request)
    {
        return trim(str_ireplace($this->getAuthorizationMethod(), '', $request->header('authorization')));
    }

    protected function getAuthorizationMethod()
    {
        return 'bearer';
    }
}
