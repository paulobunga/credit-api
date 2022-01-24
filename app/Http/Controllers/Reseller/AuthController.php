<?php

namespace App\Http\Controllers\Reseller;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
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
     * @return json
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        if (!$token = auth('reseller')->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized Credentials'], 401);
        }

        if  (auth('reseller')->user()->status === Reseller::STATUS['DISABLED']) {
            return response()->json(['message' => 'Unauthorized: Account Disabled'], 401);
        }

        return $this->response->item(auth('reseller')->user(), new AuthTransformer($token));
    }

    /**
     * Get setting information for registering an agent.
     *
     * @method POST
     * @param \App\Settings\CurrencySetting $cs
     * @return json
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
        $cs = $currency_setting->currency[$request->currency];

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
                'status' => true,
                'auto_sms_approval' => false,
                'min' => $cs['payin']['min'],
                'max' => $cs['payin']['max'],
            ],
            'payout' => [
                'commission_percentage' => $currency_setting->getCommissionPercentage(
                    $request->currency,
                    Reseller::LEVEL['RESELLER']
                ),
                'pending_limit' => $reseller_setting->getDefaultPendingLimit(Reseller::LEVEL['RESELLER']),
                'status' => true,
                'daily_amount_limit' => 50000,
                'min' => $cs['payout']['min'],
                'max' => $cs['payout']['max'],
            ],
            'downline_slot' => $agent_setting->getDefaultDownLineSlot(Reseller::LEVEL['RESELLER']),
            'status' =>  Reseller::STATUS['INACTIVE'],
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
            'phone' => 'required',
            'payout_daily_amount_limit' => 'required|numeric|min:1',
            'timezone' => 'required',
        ]);
        auth()->user()->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'payout' => auth()->user()->payout->clone(
                daily_amount_limit: $request->payout_daily_amount_limit
            ),
            'timezone' => $request->timezone,
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
     * Update pay setting
     *
     * @method POST
     * @param  \Dingo\Api\Http\Request $request
     * @throws \Exception $e if type is not matched
     *
     * @return json
     */
    public function pay(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:' . implode(',', [
                'auto_sms_approval'
            ]),
            'value' => 'boolean'
        ]);
        switch ($request->type) {
            case 'auto_sms_approval':
                auth()->user()->payin->auto_sms_approval = $request->value;
                auth()->user()->save();
                break;
        }

        return $this->response->item(auth()->user(), new AuthTransformer($request->bearerToken()));
    }
}
