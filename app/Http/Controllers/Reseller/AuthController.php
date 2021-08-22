<?php

namespace App\Http\Controllers\Reseller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (!$token = Auth::guard('reseller')->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized Credentials'], 401);
        }

        return $this->response->item(Auth::guard('reseller')->user(), new AuthTransformer($token));
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
        return $this->response->item(Auth::user(), new AuthTransformer($request->bearerToken()));
    }

    /**
     * Log the user out (Invalidate the token).
     * @method POST
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

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
        return $this->response->item(Auth::user(), new AuthTransformer(Auth::refresh()));
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
            'name' => "required|unique:resellers,name," . Auth::id(),
            'username' => "required|unique:resellers,username," . Auth::id(),
            'phone' => 'required'
        ]);
        Auth::user()->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
        ]);

        return $this->response->item(Auth::user(), new AuthTransformer($request->bearerToken()));
    }

    /**
     * Activate user via code.
     * @method PUT
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function activate(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);
        $code = ResellerActivateCode::where([
            'code' => $request->code,
            'status' => ResellerActivateCode::STATUS['PENDING']
        ])->where('expired_at', '>', \Carbon\Carbon::now())
            ->firstOrFail();
        if ($code->reseller->currency != Auth::user()->currency) {
            throw new \Exception('Activated Code Currency is not match your currency');
        }

        $code->update([
            'active_reseller_id' => Auth::id(),
            'status' => ResellerActivateCode::STATUS['ACTIVATED'],
            'activated_at' => \Carbon\Carbon::now()
        ]);
        Auth::user()->update([
            'upline_id' => $code->reseller_id,
            'status' => Reseller::STATUS['ACTIVE'],
        ]);

        return $this->response->item(Auth::user(), new AuthTransformer($request->bearerToken()));
    }
}
