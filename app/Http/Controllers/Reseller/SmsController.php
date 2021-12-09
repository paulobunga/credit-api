<?php

namespace App\Http\Controllers\Reseller;

use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Controllers\Controller;
use App\Models\ResellerSms;

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
            ]);

        return $this->paginate($m, $this->transformer);
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
            'date' => 'required|numeric',
            'date_sent' => 'required|numeric',
        ]);

        $m = $this->model::create([
            'reseller_id' => auth()->id(),
            'platform' => $request->platform,
            'body' => $request->body,
            'address' =>  $request->address,
            'status' => ResellerSms::STATUS['PENDING'],
            'sent_at' => ($request->date_sent / 1000),
            'received_at' => ($request->date / 1000),
        ]);

        return $this->response->item($m, $this->transformer);
    }
}
