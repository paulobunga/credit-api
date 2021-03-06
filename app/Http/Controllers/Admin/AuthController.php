<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Broadcast;
use Dingo\Api\Http\Request;
use App\Http\Controllers\Controller as Controller;
use App\Models\Admin;
use App\Transformers\Admin\AuthTransformer;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        if (!$token = auth()->guard('admin')->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized Credentials'], 401);
        }

        if (auth('admin')->user()->status === Admin::STATUS['DISABLED']) {
            return response()->json(['message' => 'Unauthorized: Account Disabled'], 401);
        }

        if (
            !auth('admin')->user()->isSuperAdmin &&
            !in_array($request->ip(), app(\App\Settings\AdminSetting::class)->white_lists)
        ) {
            Log::error($request->ip() . " is not in admin[" . auth()->id() . '] white list.');
            auth()->guard('admin')->logout();
            return response()->json(['message' => 'Unauthorized IP Address!'], 401);
        }

        return $this->response->item(auth()->user(), new AuthTransformer($token));
    }

    /**
     * Get the authenticated User.
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->response->item(auth()->user(), new AuthTransformer(auth()->refresh()));
    }

    /**
     * Update user information.
     *
     * @method PUT
     * @param \Dingo\Api\Http\Request $request
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'timezone' => "required",
        ]);
        auth()->user()->update([
            'timezone' => $request->timezone,
        ]);

        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }

    /**
     * Reset user password.
     *
     * @method PUT
     * @param \Dingo\Api\Http\Request $request
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    { 
        $this->validate($request, [
            'password' => 'required|confirmed',
        ]);
        auth()->user()->update([
            'password' => $request->password,
        ]);

        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }



    /**
     * Get token of onesignal service
     *
     * @method POST
     * @param \Dingo\Api\Http\Request $request
     *
     * @return array
     */
    public function onesignal(Request $request)
    {
        $this->validate($request, [
            'data' => 'required',
            'platform' => 'required',
        ]);

        auth()->user()->devices()->updateOrCreate(
            [
                'platform' => $request->platform,
                'uuid' => $request->data['userId']
            ],
            [
                'logined_at' => Carbon::now(),
                'token' => ''
            ]
        );

        return $this->success();
    }


    /**
     * Authenticate to private channel
     *
     * @method POST
     * @param  \Dingo\Api\Http\Request $request
     * @throws \Exception $e if ID is not matched
     *
     * @return \Dingo\Api\Http\Response
     */
    public function channel(Request $request)
    {
        return Broadcast::auth($request);
    }
}
