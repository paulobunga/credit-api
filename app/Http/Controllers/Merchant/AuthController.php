<?php

namespace App\Http\Controllers\Merchant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller as Controller;
use App\Transformers\Merchant\AuthTransformer;
use App\Models\Merchant;
use Illuminate\Support\Str;

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

        if (!$token = Auth::guard('merchant')->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized Credentials'], 401);
        }

        return $this->response->item(Auth::guard('merchant')->user(), new AuthTransformer($token));
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
            'name' => "required|unique:merchants,name," . Auth::id(),
            'username' => "required|unique:merchants,username," . Auth::id(),
            'phone' => 'required',
            'transaction_fee' => 'required|numeric',
            'callback_url' => 'required',
        ]);
        Auth::user()->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'transaction_fee' => $request->transaction_fee,
            'callback_url' => $request->callback_url,
        ]);

        return $this->response->item(Auth::user(), new AuthTransformer($request->bearerToken()));
    }

    public function renew(Request $request)
    {
        Auth::user()->api_key = Str::random(30);
        Auth::user()->save();

        return $this->response->item(Auth::user(), new AuthTransformer($request->bearerToken()));
    }

    public function whitelist(Request $request)
    {
        $this->validate($request, [
            'ip' => 'required|array',
            'ip.*' => 'required|distinct|ipv4',
        ]);
        \App\Models\MerchantWhiteList::where('merchant_id', Auth::id())->delete();
        \App\Models\MerchantWhiteList::insert(
            collect($request->get('ip'))->map(
                function ($v) {
                    return ['merchant_id' => Auth::id(), 'ip' => $v];
                }
            )->toArray()
        );
        
        return $this->response->item(Auth::user(), new AuthTransformer($request->bearerToken()));
    }
}
