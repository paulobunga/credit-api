<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Dingo\Api\Http\Request;
use App\Trait\SignValidator;
use App\Models\Merchant;
use App\Models\PaymentChannel;
use App\Models\Bank;

/**
 * Demo endpoint
 * @package Controllers\Api
 */
class DemoController extends Controller
{
    use SignValidator;

    /**
     * Function to get payin demo page, post form data
     * and redirect to payment pages.
     * @param \Dingo\Api\Http\Request $request Http request
     * @throws \Exception $exception if method is not supported
     * @return html
     */
    public function payin(Request $request)
    {
        switch ($request->method()) {
            case 'POST':
                $this->validate($request, [
                    'currency' => 'required',
                    'channel' => 'required',
                    'method' => 'required',
                    'amount' => 'required|numeric',
                ]);
                $merchant = Merchant::with('credits')
                    ->where(
                        'uuid',
                        '224d4a1f-6fc5-4039-bd81-fcbc7f88c659'
                    )
                    ->firstOrFail();
                $data = array_merge($request->all(), [
                    'merchant_order_id' => Str::uuid()->toString(),
                    'uuid' => $merchant->uuid,
                ]);
                $data['sign'] = $this->createSign($data, $merchant->api_key);
                try {
                    $response = app('api.dispatcher')->post('/deposits', $data);
                } catch (\Dingo\Api\Exception\InternalHttpException $e) {
                    $response = $e->getResponse();
                    return json_decode($response->getContent(), true);
                }
                return redirect()->to($response->payUrl);
            case 'GET':
                $merchant = Merchant::with('credits')
                    ->where(
                        'uuid',
                        '224d4a1f-6fc5-4039-bd81-fcbc7f88c659'
                    )
                    ->firstOrFail();
                $currency = $merchant->credits->pluck('currency')->toArray();
                $channels = PaymentChannel::whereIn(
                    'currency',
                    $currency
                )
                    ->get()
                    ->map(function ($c) {
                        return [
                            'name' => $c->name,
                            'currency' => $c->currency,
                            'methods' => $c->payment_methods,
                        ];
                    });
                $action = '/' . app('api.router')->current()->getPath();
                return view('demos.payin', compact('merchant', 'currency', 'channels', 'action'));
        }
        throw new \Exception('invalid request', 405);
    }

    /**
     * Function to get payout demo page, post form data
     * and redirect to payment pages.
     * @param \Dingo\Api\Http\Request $request Http request
     * @throws \Exception $exception if method is not supported
     * @return html
     */
    public function payout(Request $request)
    {
        switch ($request->method()) {
            case 'POST':
                $this->validate($request, [
                    'currency' => 'required',
                    'channel' => 'required',
                    'amount' => 'required|numeric',
                ]);
                $merchant = Merchant::with('credits')
                    ->where(
                        'uuid',
                        '224d4a1f-6fc5-4039-bd81-fcbc7f88c659'
                    )
                    ->firstOrFail();
                $data = array_merge($request->all(), [
                    'merchant_order_id' => Str::uuid()->toString(),
                    'uuid' => $merchant->uuid,
                    'callback_url' => env('APP_URL') . '/demos/callback'
                ]);
                $data['sign'] = $this->createSign($data, $merchant->api_key);
                try {
                    $response = app('api.dispatcher')->post('/withdrawals', $data);
                } catch (\Dingo\Api\Exception\InternalHttpException $e) {
                    $response = $e->getResponse();
                    return json_decode($response->getContent(), true);
                }
                return redirect()->to($response->payUrl);
            case 'GET':
                $merchant = Merchant::with('credits')
                    ->where(
                        'uuid',
                        '224d4a1f-6fc5-4039-bd81-fcbc7f88c659'
                    )
                    ->firstOrFail();
                $currency = $merchant->credits->pluck('currency')->toArray();
                $channels = PaymentChannel::whereIn(
                    'currency',
                    $currency
                )
                    ->get()
                    ->map(function ($c) {
                        return [
                            'name' => $c->name,
                            'currency' => $c->currency,
                            'methods' => $c->payment_methods,
                        ];
                    });
                $banks = Bank::select('id', 'name', 'currency')->where('status', 1)->get();

                $action = '/' . app('api.router')->current()->getPath();
                return view('demos.payout', compact('merchant', 'currency', 'channels', 'banks', 'action'));
        }
        throw new \Exception('invalid request', 405);
    }

    /**
     * Demo callback function
     * logging request and response success callback response.
     *
     * @param  \Dingo\Api\Http\Request $request
     * @throws \Exception $e if sign is invalid
     * @return \Dingo\Api\Http\Response $response
     */
    public function callback(Request $request)
    {
        Log::channel('callback')->info($request->all());
        if ($this->validateSign($request)) {
            return response()->json([
                'message' => 'ok'
            ]);
        }
        throw new \Exception('Sign is invalide', 405);
    }
}
