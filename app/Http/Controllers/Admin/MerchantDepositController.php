<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class MerchantDepositController extends Controller
{
    protected $model = \App\Models\MerchantDeposit::class;
    
    protected $transformer = \App\Transformers\Admin\MerchantDepositTransformer::class;

    /**
     * Get Merchant Deposit lists
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $merchant_deposits = QueryBuilder::for($this->model)
            ->with(['merchant', 'reseller', 'paymentChannel'])
            ->join('merchants', 'merchants.id', '=', 'merchant_deposits.merchant_id')
            ->join('reseller_bank_cards', 'reseller_bank_cards.id', '=', 'merchant_deposits.reseller_bank_card_id')
            ->join('resellers', 'resellers.id', '=', 'reseller_bank_cards.reseller_id')
            ->join('payment_channels', 'payment_channels.id', '=', 'reseller_bank_cards.payment_channel_id')
            ->select(
                'merchant_deposits.*',
                'merchants.name AS merchant_name',
                'resellers.name AS reseller_name',
                'payment_channels.name AS channel'
            )
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('order_id'),
                AllowedFilter::partial('merchant_order_id'),
                AllowedFilter::partial('channel', 'payment_channels.name'),
                AllowedFilter::partial('merchant_name', 'merchants.name'),
                AllowedFilter::partial('reseller_name', 'resellers.name'),
                AllowedFilter::exact('status', 'merchant_deposits.status'),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereBetween('merchant_deposits.created_at', $v)
                ),
            ])
            ->allowedSorts([
                AllowedSort::field('id', 'merchant_deposits.id'),
                AllowedSort::field('name', 'merchants.name'),
                'method',
                'currency',
                'callback_url',
                AllowedSort::field('channel', 'payment_channels.name'),
                AllowedSort::field('order_id'),
                AllowedSort::field('merchant_order_id'),
                AllowedSort::field('reseller_name', 'resellers.name'),
                AllowedSort::field('amount'),
                AllowedSort::field('status', 'merchant_deposits.status'),
                AllowedSort::field('created_at', 'merchant_deposits.created_at'),
            ])
            ->paginate($this->perPage);
        return $this->response->withPaginator($merchant_deposits, $this->transformer);
    }

    public function update(Request $request)
    {
        $merchant_deposit = $this->model::findOrFail($this->parameters('merchant_deposit'));
        $this->validate($request, [
            'admin_id' => 'required|exists:admins,id',
            'status' => 'required|numeric',
        ]);

        $merchant_deposit->update([
            'status' => $request->status,
            'info' => [
                'admin_id' => $request->admin_id
            ]
        ]);

        return $this->response->item($merchant_deposit, $this->transformer);
    }
}
