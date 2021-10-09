<?php

namespace App\Http\Controllers\Merchant;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Broadcast;
use Pusher\PushNotifications\PushNotifications;
use App\Http\Controllers\Controller as Controller;
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
            'transaction_fee' => 'required|numeric',
            'callback_url' => 'required',
        ]);
        auth()->user()->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'transaction_fee' => $request->transaction_fee,
            'callback_url' => $request->callback_url,
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
     * Authenticate beam notification
     *
     * @param  mixed $request
     * @return void
     */
    public function beam(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'platform' => 'required',
        ]);
        $user_id = Arr::last(explode('.', $request->user_id));
        if ($user_id !=  auth()->id()) {
            return response('Inconsistent request', 401);
        }
        $beam = new PushNotifications([
            'secretKey' => config('broadcasting.connections.beams.secret_key'),
            'instanceId' => config('broadcasting.connections.beams.instance_id'),
        ]);

        $token = $beam->generateToken('App.Models.Merchant.' . auth()->id());
        auth()->user()->devices()->updateOrCreate(
            [
                'platform' => $request->platform
            ],
            [
                'logined_at' => Carbon::now(),
                'token' => $token['token']
            ]
        );

        return response()->json($token);
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
}
