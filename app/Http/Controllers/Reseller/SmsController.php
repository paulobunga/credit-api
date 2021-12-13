<?php

namespace App\Http\Controllers\Reseller;

use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;
use App\Models\ResellerSms;
use App\Models\PaymentChannel;
use App\Models\MerchantDeposit;

class SmsController extends Controller
{
    protected $model = ResellerSms::class;

    protected $transformer = \App\Transformers\Reseller\SmsTransformer::class;

    /**
     * Get SMS list
     *
     * @param  \Dingo\Api\Http\Request $request
     * @return json
     */
    public function index(Request $request)
    {
        $m = QueryBuilder::for($this->model)
            ->allowedFilters([
                'id',
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts([
                'id',
                'status',
            ])
            ->where('reseller_id', auth()->id());

        return $this->paginate($m, $this->transformer);
    }

    /**
     * Get SMS and payment channels for inbox use
     *
     * @param  \Dingo\Api\Http\Request $request
     * @return json
     */
    public function inbox(Request $request)
    {
        return [
            'data' => [
                'bankcards' => auth()->user()->bankCards()->get()->map(function ($v) {
                    switch (auth()->user()->currency) {
                        case 'BDT':
                            return $v->attributes['wallet_number'];
                        default:
                            return '';
                    }
                }),
                'payment_channels' => PaymentChannel::where('currency', auth()->user()->currency)->get(),
                'sms' => $this->model::where('reseller_id', auth()->id())->get()->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'address' => $v->address,
                        'trx_id' => $v->trx_id,
                    ];
                })
            ]
        ];
    }

    /**
     * Store sms.
     *
     * @param  \Dingo\Api\Http\Request $request
     * @return json
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'platform' => 'required',
            'body' => 'required',
            'address' => 'required',
            'sim_num' => 'required',
            'date' => 'required|numeric',
            'date_sent' => 'required|numeric',
        ]);

        $data = ResellerSms::match([
            'reseller_id' => auth()->id(),
            'currency' => auth()->user()->currency,
            'body' => $request->body,
            'address' =>  $request->address,

        ]);

        if (!$data['trx_id']) {
            throw new \Exception('invalid SMS format');
        }

        $m = null;
        if ($deposit = $data['match']) {
            $deposit->update([
                'status' => MerchantDeposit::STATUS['APPROVED'],
                'extra' => $deposit->extra + ['reference_id' => $data['trx_id']]
            ]);
            $m = $this->model::create([
                'reseller_id' => auth()->id(),
                'platform' => $request->platform,
                'body' => $request->body,
                'address' =>  $request->address,
                'trx_id' => $data['trx_id'],
                'sim_num' => $request->sim_num,
                'model_id' => $deposit->id,
                'model_name' => 'merchant.deposit',
                'status' => ResellerSms::STATUS['MATCH'],
                'sent_at' => ($request->date_sent / 1000),
                'received_at' => ($request->date / 1000),
            ]);
        } else {
            $m = $this->model::create([
                'reseller_id' => auth()->id(),
                'platform' => $request->platform,
                'body' => $request->body,
                'address' =>  $request->address,
                'trx_id' => $data['trx_id'],
                'sim_num' => $request->sim_num,
                'status' => ResellerSms::STATUS['UNMATCH'],
                'sent_at' => ($request->date_sent / 1000),
                'received_at' => ($request->date / 1000),
            ]);
        }

        return $this->response->item($m, $this->transformer);
    }
}
