<?php

namespace App\Http\Controllers\Reseller;

use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;
use App\Models\ResellerSms;
use App\Models\PaymentChannel;

class SmsController extends Controller
{
    protected $model = ResellerSms::class;

    protected $transformer = \App\Transformers\Reseller\SmsTransformer::class;

    /**
     * Get SMS list
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method GET
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
     *ƒ
     * @method GET
     * @return json
     */
    public function inbox()
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
                        'status' => $v->status
                    ];
                })
            ]
        ];
    }

    /**
     * Create SMS.
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method POST
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

        $data = ResellerSms::parse(
            [
                'body' => $request->body,
                'address' =>  $request->address,
                'sim_num' => $request->sim_num
            ],
            PaymentChannel::where('currency', auth()->user()->currency)->get()
        );

        if (empty($data['trx_id']) || empty($data['amount'])) {
            throw new \Exception('invalid SMS!', ResellerSms::STATUS['INVALID']);
        }

        $m = ResellerSms::where([
            'reseller_id' => auth()->id(),
            'address' => $request->address,
            'trx_id' => $data['trx_id'],
        ])->first();
        if ($m) {
            throw new \Exception('Trx id is exist!');
        }

        $m = $this->model::create([
            'reseller_id' => auth()->id(),
            'payment_channel_id' => $data['channel']->id,
            'platform' => $request->platform,
            'body' => $request->body,
            'address' =>  $request->address,
            'payer' => $data['payer'],
            'trx_id' => $data['trx_id'],
            'sim_num' => $request->sim_num,
            'amount' => $data['amount'],
            'status' => ResellerSms::STATUS['PENDING'],
            'sent_at' => ($request->date_sent / 1000),
            'received_at' => ($request->date / 1000),
        ]);

        return $this->response->item($m, $this->transformer);
    }

    /**
     * Massive create sms.
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method POST
     * @return json
     */
    public function upsert(Request $request)
    {
        $this->validate($request, [
            'data' => 'array',
        ]);

        $response = $request->get('data', []);
        if (empty($response)) {
            return response()->json([
                'message' => 'SMS list is empty!'
            ]);
        }
        $channels = PaymentChannel::where('currency', auth()->user()->currency)->get();

        foreach ($response as $k => $sms) {
            $data = ResellerSms::parse([
                'body' => $sms['body'],
                'address' =>  $sms['address'],
                'sim_num' =>  $sms['address'],
            ], $channels);

            if (empty($data['trx_id']) || empty($data['amount'])) {
                $response[$k]['status'] = ResellerSms::STATUS['INVALID'];
                $response[$k]['message'] = 'invalid SMS!';
                continue;
            }
            $m = ResellerSms::where([
                'reseller_id' => auth()->id(),
                'address' => $sms['address'],
                'trx_id' => $data['trx_id'],
            ])->first();
            if ($m) {
                $response[$k]['status'] = $m->status;
                $response[$k]['message'] = 'Trx id is exist!';
                continue;
            }
            $m = $this->model::create([
                'reseller_id' => auth()->id(),
                'payment_channel_id' => $data['channel']->id,
                'platform' => $sms['platform'],
                'body' => $sms['body'],
                'address' =>  $sms['address'],
                'payer' => $data['payer'],
                'trx_id' => $data['trx_id'],
                'sim_num' => $sms['sim_num'],
                'amount' => $data['amount'],
                'status' => ResellerSms::STATUS['PENDING'],
                'sent_at' => ($sms['date_sent'] / 1000),
                'received_at' => ($sms['date'] / 1000),
            ]);
            $response[$k]['status'] = ResellerSms::STATUS['PENDING'];
        }

        return [
            'data' => $response
        ];
    }
}
