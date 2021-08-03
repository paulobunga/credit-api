<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Database\Eloquent\Builder;

class DepositController extends Controller
{
    protected $model = \App\Models\MerchantDeposit::class;
    protected $transformer = \App\Transformers\Reseller\DepositTransformer::class;

    public function index(Request $request)
    {
        $deposits = QueryBuilder::for($this->model)
            ->join(
                'reseller_bank_cards',
                'merchant_deposits.reseller_bank_card_id',
                '=',
                'reseller_bank_cards.id'
            )
            ->whereHas('reseller', function (Builder $query) {
                $query->where('resellers.id', Auth::id());
            })
            ->select(
                'merchant_deposits.*',
            )
            ->allowedFilters([
                'name'
            ])
            ->paginate($this->perPage);

        return $this->response->withPaginator($deposits, $this->transformer);
    }

    public function update(Request $request)
    {
        $deposit = $this->model::findOrFail($this->parameters('deposit'));
        if ($deposit->reseller->id != Auth::id()) {
            throw new \Exception('Unauthorize', 401);
        }
        $this->validate($request, [
            'status' => 'required|numeric',
        ]);

        $deposit->update([
            'status' => $request->status,
            'callback_status' => 1,
        ]);
        // send notification via websocket and stored in notification table
        $deposit->merchant->notify(new \App\Notifications\DepositUpdateNotification($deposit));
        // push deposit information callback to callback url
        Queue::push((new \App\Jobs\GuzzleJob(
            $deposit,
            new \App\Transformers\Api\DepositTransformer,
            $deposit->merchant->api_key
        )));

        return $this->response->item($deposit, $this->transformer);
    }
}
