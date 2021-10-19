<?php

namespace App\Http\Controllers\Reseller;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
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
     *
     * @method POST
     * @param \Dingo\Api\Http\Request $request
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
     * Get setting information for registering an agent.
     *
     * @method POST
     * @param \App\Settings\CurrencySetting $cs
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
     *
     * @method POST
     * @param \Dingo\Api\Http\Request $request
     * @param \App\Settings\CurrencySetting $cs
     *
     * @return array
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
            'uplines' => [],
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'currency' => $request->currency,
            'password' => $request->password,
            'payin' => [
                'commission_percentage' => $currency_setting->getCommissionPercentage(
                    $request->currency,
                    Reseller::LEVEL['RESELLER']
                ),
                'pending_limit' => $reseller_setting->getDefaultPendingLimit(Reseller::LEVEL['RESELLER']),
                'status' => true
            ],
            'payout' => [
                'commission_percentage' => $currency_setting->getCommissionPercentage(
                    $request->currency,
                    Reseller::LEVEL['RESELLER']
                ),
                'pending_limit' => $reseller_setting->getDefaultPendingLimit(Reseller::LEVEL['RESELLER']),
                'status' => true
            ],
            'downline_slot' => $agent_setting->getDefaultDownLineSlot(Reseller::LEVEL['RESELLER']),
            'status' =>  Reseller::STATUS['INACTIVE']
        ]);

        return $this->success();
    }

    /**
     * Get the authenticated User.
     *
     * @method POST
     * @param \Dingo\Api\Http\Request $request
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
     * @method POST
     * @param \Dingo\Api\Http\Request $request
     *
     * @return array
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
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
     *
     * @method PUT
     * @param \Dingo\Api\Http\Request $request
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
     * @throws \Exception $e if activated code is expired
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
        ])->where('expired_at', '>', Carbon::now())->firstOrFail();
        $agent = $code->reseller;
        if ($agent->currency != auth()->user()->currency) {
            throw new \Exception('Activated Code Currency is not match your currency');
        }
        if ($agent->downline > $agent->downline_slot) {
            throw new \Exception('Agent cannot add more downline!');
        }

        $code->update([
            'active_reseller_id' => auth()->id(),
            'status' => ResellerActivateCode::STATUS['ACTIVATED'],
            'activated_at' => Carbon::now()
        ]);
        auth()->user()->update([
            'upline_id' => $agent->id,
            'uplines' => array_merge($agent->uplines, [$agent->id]),
            'status' => Reseller::STATUS['ACTIVE'],
        ]);

        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }


    /**
     * Get token of beam service
     *
     * @method GET
     * @param \Dingo\Api\Http\Request $request
     *
     * @return array
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

        $token = $beam->generateToken('App.Models.Reseller.' . auth()->id());
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

    /**
     * Update pay setting
     *
     * @method POST
     * @param  \Dingo\Api\Http\Request $request
     * @throws \Exception $e if ID is not matched
     *
     * @return \Dingo\Api\Http\Response
     */
    public function pay(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:' . implode(',', [
                'payin',
                'payout',
            ]),
            'value' => 'boolean'
        ]);
        auth()->user()->{$request->type}->status = $request->value;
        auth()->user()->save();

        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }
}
