<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use App\Models\MerchantDeposit;
use App\Models\Transaction;

trait MerchantDepositObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $last_insert_id = DB::select("SELECT MAX(id) AS ID FROM merchant_deposits")[0]->ID ?? 0;
            $query->order_id = Str::random(4) . ($last_insert_id + 1) . '@' . Str::random(10);
        });

        static::created(function ($m) {
            static::onStatusChangeEvent($m->status, $m);
        });
    }

    public function setStatusAttribute($value)
    {
        if ($this->exists) {
            static::onStatusChangeEvent($value, $this);
        }
        $this->attributes['status'] = $value;
    }
    /**
     * Handle the status "changed" event.
     *
     * @param  \App\Models\MerchantDeposit
     * @return void
     */
    protected static function onStatusChangeEvent($status, MerchantDeposit $m)
    {
        if (in_array($status, [
            MerchantDeposit::STATUS['APPROVED'],
            MerchantDeposit::STATUS['ENFORCED'],
            MerchantDeposit::STATUS['MAKEUP'],
        ])) {
            if ($m->reseller->credit < $m->amount && $status == MerchantDeposit::STATUS['APPROVED']) {
                throw new \Exception('Amount exceed credit of agent', 405);
            }
            // merchant add credit and deduct transaction fee
            $credit = $m->merchant->credits()->where('currency', $m->currency)->first();
            if (!$credit) {
                throw new \Exception('Currency type is not supported!');
            }
            DB::beginTransaction();
            try {
                $m->transactions()->create([
                    'user_id' => $m->merchant_id,
                    'user_type' => 'merchant',
                    'type' => Transaction::TYPE['MERCHANT_TOPUP_CREDIT'],
                    'amount' => $m->amount,
                    'before' => $credit->credit,
                    'after' => $credit->credit + $m->amount,
                    'currency' => $m->currency,
                ]);
                $m->transactions()->create([
                    'user_id' => $m->merchant_id,
                    'user_type' => 'merchant',
                    'type' => Transaction::TYPE['SYSTEM_TRANSACTION_FEE'],
                    'amount' => $m->amount * $credit->transaction_fee,
                    'before' => $credit->credit + $m->amount,
                    'after' => $credit->credit + $m->amount * (1 - $credit->transaction_fee),
                    'currency' => $m->currency,
                ]);
                $credit->increment(
                    'credit',
                    $m->amount * (1 - $credit->transaction_fee)
                );
                // reseller
                $m->transactions()->create([
                    'user_id' => $m->reseller->id,
                    'user_type' => 'reseller',
                    'type' => Transaction::TYPE['SYSTEM_DEDUCT_CREDIT'],
                    'amount' => $m->amount,
                    'before' => $m->reseller->credit,
                    'after' => $m->reseller->credit - $m->amount,
                    'currency' => $m->currency,
                ]);
                $m->reseller->decrement(
                    'credit',
                    $m->amount
                );
                // commission
                $rows = DB::select("
                WITH agents AS (
                    SELECT
                        id,
                        upline_id,
                        level,
                        name,
                        payin,
                        coin 
                    FROM
                        resellers 
                    WHERE
                        id = {$m->reseller->id} 
                    UNION ALL
                    SELECT
                        r.id,
                        upline_id,
                        level,
                        name,
                        payin,
                        coin 
                    FROM
                    (
                        SELECT
                            id,
                            uplines
                        FROM
                            resellers
                        WHERE
                            id = {$m->reseller->id} 
                    ) AS temp
                    INNER JOIN resellers AS r ON JSON_CONTAINS(
                        temp.uplines,
                        CAST( r.id AS json ),
                        '$' 
                    ) 
                )
                SELECT
                    id AS user_id,
                    'reseller' AS user_type,
                    :type AS type,
                    {$m->amount}  * payin->>'$.commission_percentage' AS amount,
                    coin AS coin 
                FROM
                    agents
                ORDER BY level DESC
                ", [
                    'type' => Transaction::TYPE['SYSTEM_TOPUP_COMMISSION']
                ]);
                foreach ($rows as $row) {
                    $m->transactions()->create([
                        'user_id' => $row->user_id,
                        'user_type' => 'reseller',
                        'type' => Transaction::TYPE['SYSTEM_TOPUP_COMMISSION'],
                        'amount' => $row->amount,
                        'before' => $row->coin,
                        'after' => $row->coin + $row->amount,
                        'currency' => $m->currency,
                    ]);
                    DB::table('resellers')->where('id', $row->user_id)->increment('coin', $row->amount);
                }
            } catch (\Exception $e) {
                DB::rollback();
                Log::error($e->getMessage());
                throw $e;
            }
            DB::commit();
        }
        // send notification
        switch ($status) {
            case MerchantDeposit::STATUS['PENDING']:
                $m->reseller->notify(new \App\Notifications\DepositPending($m));
                break;
            case MerchantDeposit::STATUS['CANCELED']:
            case MerchantDeposit::STATUS['REJECTED']:
            case MerchantDeposit::STATUS['APPROVED']:
            case MerchantDeposit::STATUS['ENFORCED']:
                $m->merchant->notify(new \App\Notifications\DepositFinish($m));
                $m->callback_status = MerchantDeposit::CALLBACK_STATUS['PENDING'];
                // push deposit information callback to callback url
                Queue::push((new \App\Jobs\GuzzleJob(
                    $m,
                    new \App\Transformers\Api\DepositTransformer,
                    $m->merchant->api_key
                )));
                break;
        }
    }
}
