<?php

namespace App\Http\Controllers\Reseller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller as Controller;
use App\Transformers\Reseller\AuthTransformer;
use App\Models\Reseller;

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

        if (!$token = Auth::guard('reseller')->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized Credentials'], 401);
        }

        return $this->response->item(Auth::guard('reseller')->user(), new AuthTransformer($token));
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:resellers,name',
            'username' => 'required|unique:resellers,username',
            'phone' => 'required|unique:resellers,phone',
            'password' => 'required|confirmed',
        ]);
        $commission_setting = app(\App\Settings\CommissionSetting::class);
        $reseller_setting = app(\App\Settings\ResellerSetting::class);
        $agent_setting = app(\App\Settings\AgentSetting::class);

        Reseller::create([
            'level' => Reseller::LEVEL['reseller'],
            'upline' => 0,
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'password' => $request->password,
            'commission_percentage' => $commission_setting->getDefaultPercentage(Reseller::LEVEL['reseller']),
            'pending_limit' => $reseller_setting->getDefaultPendingLimit(Reseller::LEVEL['reseller']),
            'downline_slot' => $agent_setting->getDefaultDownLineSlot(Reseller::LEVEL['reseller']),
            'status' =>  Reseller::STATUS['inactive']
        ]);

        return $this->success();
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return $this->response->item(Auth::user(), new AuthTransformer($request->bearerToken()));
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->response->item(Auth::user(), new AuthTransformer(Auth::refresh()));
    }

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
}
