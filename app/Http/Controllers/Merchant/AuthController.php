<?php

namespace App\Http\Controllers\Merchant;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Controller as Controller;
use App\Models\Merchant;
use App\Transformers\Merchant\AuthTransformer;
use App\Models\MerchantWhiteList;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        if (!$token = auth('merchant')->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized Credentials'], 401);
        }

        if (auth('merchant')->user()->status === Merchant::STATUS['DISABLED']) {
            return response()->json(['message' => 'Unauthorized: Account Disabled'], 401);
        }

        if (!in_array(
            $request->ip(),
            MerchantWhiteList::where('merchant_id', auth('merchant')->id())->first()->backend ?? []
        )) {
            Log::error($request->ip() . " is not in merchant[" . auth('merchant')->id() . '] white list.');
            auth('merchant')->logout();
            return response()->json(['message' => 'Unauthorized IP Address!'], 401);
        }

        return $this->response->item(auth('merchant')->user(), new AuthTransformer($token));
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->response->item(auth()->user(), new AuthTransformer(auth()->refresh()));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => "required|unique:merchants,name," . auth()->id(),
            'username' => "required|unique:merchants,username," . auth()->id(),
            'phone' => 'required',
        ]);
        auth()->user()->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
        ]);

        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }

    public function renew(Request $request)
    {
        auth()->user()->api_key = Str::random(30);
        auth()->user()->save();

        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }

    public function whitelist(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:api,backend',
            'ip' => 'required|array',
            'ip.*' => 'required|distinct|ipv4',
        ]);
        \App\Models\MerchantWhiteList::updateOrCreate(
            ['merchant_id' => auth()->id()],
            [$request->type => $request->ip],
        );

        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }

    /**
     * Authenticate private channel request
     *
     * @param  \Dingo\Api\Http\Request $request
     * @throws \Exception $e if id not matched
     * @return \Dingo\Api\Http\Response $response
     */
    public function channel(Request $request)
    {
        return Broadcast::auth($request);
    }

    /**
     * Get Token of onesignal service
     *
     * @method POST
     * @param  \Dingo\Api\Http\Request $request
     * @return json
     */
    public function onesignal(Request $request)
    {
        $this->validate($request, [
            'data' => 'required',
            'platform' => 'required'
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
}
