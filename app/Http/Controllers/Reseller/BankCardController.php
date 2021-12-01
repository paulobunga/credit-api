<?php

namespace App\Http\Controllers\Reseller;

use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ResellerBankCard;
use App\Http\Controllers\Controller;

class BankCardController extends Controller
{
    protected $model = ResellerBankCard::class;

    protected $transformer = \App\Transformers\Reseller\BankCardTransformer::class;
    
    /**
     * Get bank card list
     *
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $bankcards = QueryBuilder::for($this->model)
            ->with([
                'paymentChannel',
            ])
            ->allowedFilters([
                'id',
                'name',
                AllowedFilter::exact('channel', 'payment_channel.name'),
                'status'
            ])
            ->where('reseller_id', Auth::id())
            ->paginate($this->perPage);

        return $this->response->withPaginator($bankcards, $this->transformer);
    }
    
    /**
     * Enable/Disable bank card
     *
     * @param \Dingo\Api\Http\Request $request
     * @method PUT
     * @return json
     */
    public function status(Request $request)
    {
        $bankcard = $this->model::where([
            'id' => $this->parameters('bankcard'),
            'reseller_id' => Auth::id()
        ])->firstOrFail();

        if (!in_array($bankcard->status, [
            ResellerBankCard::STATUS['ACTIVE'],
            ResellerBankCard::STATUS['DISABLED'],
        ])) {
            throw new \Exception('status is not allowd to modified!', 405);
        }

        $this->validate($request, [
            'status' => 'required|numeric|in:' . implode(',', [
                ResellerBankCard::STATUS['ACTIVE'],
                ResellerBankCard::STATUS['DISABLED'],
            ])
        ]);
        $bankcard->update([
            'status' => $request->status,
        ]);

        return $this->response->item($bankcard, $this->transformer);
    }
}
