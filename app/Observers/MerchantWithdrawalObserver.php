<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use App\Models\MerchantWithdrawal;
use App\Models\Transaction;

trait MerchantWithdrawalObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($m) {
            $last_insert_id = DB::select("SELECT MAX(id) AS ID FROM merchant_withdrawals")[0]->ID ?? 0;
            $m->order_id = Str::random(4) . ($last_insert_id + 1) . '@' . Str::random(10);
            if (
                $m->merchant->getWithdrawalCredit($m->currency) < ($m->amount +
                $m->merchant->getPayOutFee($m->currency, $m->amount))
            ) {
                throw new \Exception("Amount exceed credit of merchant!", 405);
            }
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
     * @param  \App\Models\MerchantWithdrawal
     * @return void
     */
    protected static function onStatusChangeEvent($status, MerchantWithdrawal $m)
    {
        if ($status == MerchantWithdrawal::STATUS['PENDING']) {
            // merchant deduct credit
            $credit = $m->merchant->credits()->where('currency', $m->currency)->first();
            if (!$credit) {
                throw new \Exception('Currency type is not supported!');
            }
            if ($credit->credit < $m->amount * (1 + $credit->transaction_fee)) {
                throw new \Exception('Amount exceed credit of merchant', 405);
            }
            DB::beginTransaction();
            try {
                $m->transactions()->create([
                    'user_id' => $m->merchant_id,
                    'user_type' => 'merchant',
                    'type' => Transaction::TYPE['MERCHANT_WITHDRAW_CREDIT'],
                    'amount' => $m->amount,
                    'before' => $credit->credit,
                    'after' => $credit->credit - $m->amount,
                    'currency' => $m->currency,
                ]);
                $m->transactions()->create([
                    'user_id' => $m->merchant_id,
                    'user_type' => 'merchant',
                    'type' => Transaction::TYPE['SYSTEM_TRANSACTION_FEE'],
                    'amount' => $m->amount * $credit->transaction_fee,
                    'before' => $credit->credit - $m->amount,
                    'after' => $credit->credit - $m->amount * (1 + $credit->transaction_fee),
                    'currency' => $m->currency,
                ]);
                $credit->decrement(
                    'credit',
                    $m->amount * (1 + $credit->transaction_fee)
                );
            } catch (\Exception $e) {
                DB::rollback();
                Log::error($e->getMessage());
                throw $e;
            }
            DB::commit();
        } elseif (
            in_array($status, [
                MerchantWithdrawal::STATUS['REJECTED'],
                MerchantWithdrawal::STATUS['CANCELED'],
            ])
        ) {
            if (
                $status == MerchantWithdrawal::STATUS['REJECTED'] &&
                $m->status != MerchantWithdrawal::STATUS['PENDING']
            ) {
                throw new \Exception("Status is not allowed to update!");
            } elseif (
                $status == MerchantWithdrawal::STATUS['CANCELED']
                && !in_array($m->status, [
                    MerchantWithdrawal::STATUS['PENDING'],
                    MerchantWithdrawal::STATUS['FINISHED'],
                ])
            ) {
                throw new \Exception("Status is not allowed to update!");
            }
            // rollback merchant credit
            $credit = $m->merchant->credits()->where('currency', $m->currency)->first();
            if (!$credit) {
                throw new \Exception('Currency type is not supported!');
            }
            DB::beginTransaction();
            try {
                $m->transactions()->create([
                    'user_id' => $m->merchant_id,
                    'user_type' => 'merchant',
                    'type' => Transaction::TYPE['SYSTEM_TOPUP_CREDIT'],
                    'amount' => $m->amount,
                    'before' => $credit->credit,
                    'after' => $credit->credit + $m->amount,
                    'currency' => $m->currency,
                ]);
                $m->transactions()->create([
                    'user_id' => $m->merchant_id,
                    'user_type' => 'merchant',
                    'type' => Transaction::TYPE['ROLLBACK_TRANSACTION_FEE'],
                    'amount' => $m->amount * $credit->transaction_fee,
                    'before' => $credit->credit + $m->amount,
                    'after' => $credit->credit + $m->amount * (1 + $credit->transaction_fee),
                    'currency' => $m->currency,
                ]);
                $credit->increment(
                    'credit',
                    $m->amount * (1 + $credit->transaction_fee)
                );
            } catch (\Exception $e) {
                DB::rollback();
                Log::error($e->getMessage());
                throw $e;
            }
            DB::commit();
        } elseif ($status == MerchantWithdrawal::STATUS['APPROVED']) {
            if ($m->status != MerchantWithdrawal::STATUS['FINISHED']) {
                throw new \Exception('Status is not allowed to update!');
            }
            DB::beginTransaction();
            try {
                // reseller
                $m->transactions()->create([
                    'user_id' => $m->reseller->id,
                    'user_type' => 'reseller',
                    'type' => Transaction::TYPE['SYSTEM_TOPUP_CREDIT'],
                    'amount' => $m->amount,
                    'before' => $m->reseller->credits->credit,
                    'after' => $m->reseller->credits->credit + $m->amount,
                    'currency' => $m->currency,
                ]);
                $m->reseller->credits->increment(
                    'credit',
                    $m->amount
                );
                // commission
                $rows = DB::select("
                WITH agents AS (
                    SELECT
                        r.id,
                        upline_id,
                        level,
                        name,
                        payout,
                        coin 
                    FROM
                        resellers AS r
                    INNER JOIN
                        reseller_credits AS rc ON r.id = rc.reseller_id
                    WHERE
                        r.id = {$m->reseller->id}
                    UNION ALL
                    SELECT
                        r.id,
                        r.upline_id,
                        r.level,
                        r.name,
                        r.payout,
                        rc.coin 
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
                    INNER JOIN
                        reseller_credits AS rc
                        ON r.id = rc.reseller_id
                )
                SELECT
                    id AS user_id,
                    'reseller' AS user_type,
                    :type AS type,
                    {$m->amount} * payout->>'$.commission_percentage' AS amount,
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
                    DB::table('reseller_credits')->where('reseller_id', $row->user_id)->increment('coin', $row->amount);
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
            case MerchantWithdrawal::STATUS['PENDING']:
                $m->reseller->notify(new \App\Notifications\WithdrawalPending($m));
                break;
            case MerchantWithdrawal::STATUS['REJECTED']:
            case MerchantWithdrawal::STATUS['CANCELED']:
            case MerchantWithdrawal::STATUS['APPROVED']:
                $m->merchant->notify(new \App\Notifications\WithdrawalFinish($m));
                $m->callback_status = MerchantWithdrawal::CALLBACK_STATUS['PENDING'];
                // push withdrawal information callback to callback url
                Queue::push((new \App\Jobs\GuzzleJob(
                    $m,
                    new \App\Transformers\Api\WithdrawalTransformer,
                    $m->merchant->api_key
                )));
                break;
        }
    }
}
