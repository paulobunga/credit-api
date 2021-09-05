<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Dingo\Api\Http\Request;
use App\Trait\SignValidator;
use App\Models\Merchant;
use App\Models\PaymentChannel;

/**
 * @group Deposit API
 *
 * Simple html to try payin and payout API.
 *
 *
 */
class DemoController extends Controller
{
    use SignValidator;

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
                    return redirect()->to($response->payUrl);
                } catch (\Dingo\Api\Exception\InternalHttpException $e) {
                    $response = $e->getResponse();
                    return json_decode($response->getContent(), true);
                }
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
                )->get();
                return view('demos.payin', compact('merchant', 'currency', 'channels'));
            default:
                throw new \Exception('invalid request', 405);
        }
        // if($request)
        // $merchant = $this->validateSign($request);
        // $this->validate($request, [
        //     'time' => 'required|numeric',
        // ]);
        // $deposit = $this->model::with(['merchant', 'resellerBankCard', 'paymentChannel'])->where([
        //     'merchant_id' => $merchant->id,
        //     'merchant_order_id' => $request->merchant_order_id,
        // ])->firstOrFail();
        // $channel = $deposit->paymentChannel;

        // return view(strtolower($deposit->method), [
        //     'deposit' => $deposit,
        //     'channel' => $channel,
        //     'subview' => strtolower("{$deposit->method}s.{$channel->name}.{$channel->currency}"),
        //     'attributes' => $deposit->resellerBankCard->attributes
        // ]);
    }
}
