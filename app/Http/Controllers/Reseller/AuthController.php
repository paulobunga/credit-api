<?php

namespace App\Http\Controllers\Reseller;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Carbon\Carbon;
use Pusher\PushNotifications\PushNotifications;
use App\Http\Controllers\Controller as Controller;
use App\Transformers\Reseller\AuthTransformer;
use App\Models\Reseller;
use App\Models\ResellerActivateCode;
use App\Settings\CurrencySetting;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     * @method POST
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        if (!$token = auth('reseller')->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized Credentials'], 401);
        }

        return $this->response->item(auth('reseller')->user(), new AuthTransformer($token));
    }

    /**
     * Get a register setting
     * @method POST
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function setting(CurrencySetting $cs)
    {
        return [
            'message' => 'success',
            'data' => [
                'currency' => $cs->currency
            ]
        ];
    }

    /**
     * Create a reseller
     * @method POST
     *
     * @return array success response
     */
    public function register(Request $request, CurrencySetting $cs)
    {
        $this->validate($request, [
            'name' => 'required|unique:resellers,name',
            'username' => 'required|unique:resellers,username',
            'phone' => 'required|unique:resellers,phone',
            'currency' => 'required|in:' . implode(',', array_keys($cs->currency)),
            'password' => 'required|confirmed',
        ]);
        $currency_setting = app(\App\Settings\CurrencySetting::class);
        $reseller_setting = app(\App\Settings\ResellerSetting::class);
        $agent_setting = app(\App\Settings\AgentSetting::class);

        Reseller::create([
            'level' => Reseller::LEVEL['RESELLER'],
            'upline' => 0,
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'currency' => $request->currency,
            'password' => $request->password,
            'commission_percentage' => $currency_setting->getCommissionPercentage(
                $request->currency,
                Reseller::LEVEL['RESELLER']
            ),
            'pending_limit' => $reseller_setting->getDefaultPendingLimit(Reseller::LEVEL['RESELLER']),
            'downline_slot' => $agent_setting->getDefaultDownLineSlot(Reseller::LEVEL['RESELLER']),
            'status' =>  Reseller::STATUS['INACTIVE']
        ]);

        return $this->success();
    }

    /**
     * Get the authenticated User.
     * @method GET
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }

    /**
     * Log the user out (Invalidate the token).
     * @method POST
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     * @method POST
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->response->item(auth()->user(), new AuthTransformer(auth()->refresh()));
    }

    /**
     * Update user information.
     * @method PUT
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => "required|unique:resellers,name," . auth()->id(),
            'username' => "required|unique:resellers,username," . auth()->id(),
            'phone' => 'required'
        ]);
        auth()->user()->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
        ]);

        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }

    /**
     * Activate user by code.
     *
     * @param \Dingo\Api\Http\Request $request
     * @method PUT
     *
     * @return \Dingo\Api\Http\JsonResponse $response
     */
    public function activate(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);
        $code = ResellerActivateCode::where([
            'code' => $request->code,
            'status' => ResellerActivateCode::STATUS['PENDING']
        ])->where('expired_at', '>', Carbon::now())
            ->firstOrFail();
        if ($code->reseller->currency != auth()->user()->currency) {
            throw new \Exception('Activated Code Currency is not match your currency');
        }

        $code->update([
            'active_reseller_id' => auth()->id(),
            'status' => ResellerActivateCode::STATUS['ACTIVATED'],
            'activated_at' => Carbon::now()
        ]);
        auth()->user()->update([
            'upline_id' => $code->reseller_id,
            'status' => Reseller::STATUS['ACTIVE'],
        ]);

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

        $device = auth()->user()->devices()->firstOrNew(
            [
                'platform' => $request->platform
            ],
            [
                'token' => $beam->generateToken('App.Models.Reseller.' . auth()->id())['token'],
            ]
        );
        $device->logined_at = Carbon::now();
        $device->save();

        return response()->json([
            'token' => $device->token
        ]);
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
