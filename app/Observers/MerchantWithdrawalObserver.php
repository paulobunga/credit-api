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
        $this->attributes['status'] = $value;
        if ($this->exists) {
            static::onStatusChangeEvent($value, $this);
        }
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
            if ($credit->credit < $m->amount) {
                throw new \Exception('exceed merchant credit', 405);
            }
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
        } elseif (in_array($status, [
            MerchantWithdrawal::STATUS['REJECTED'],
            MerchantWithdrawal::STATUS['CANCELED'],
        ])) {
            // rollback merchant credit
            $credit = $m->merchant->credits()->where('currency', $m->currency)->first();
            if (!$credit) {
                throw new \Exception('Currency type is not supported!');
            }
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
                'type' => Transaction::TYPE['SYSTEM_TOPUP_CREDIT'],
                'amount' => $m->amount * $credit->transaction_fee,
                'before' => $credit->credit + $m->amount,
                'after' => $credit->credit + $m->amount * (1 + $credit->transaction_fee),
                'currency' => $m->currency,
            ]);
            $credit->increment(
                'credit',
                $m->amount * (1 + $credit->transaction_fee)
            );
        } elseif ($status == MerchantWithdrawal::STATUS['APPROVED']) {
            // reseller
            $m->transactions()->create([
                'user_id' => $m->reseller->id,
                'user_type' => 'reseller',
                'type' => Transaction::TYPE['SYSTEM_TOPUP_CREDIT'],
                'amount' => $m->amount,
                'before' => $m->reseller->credit,
                'after' => $m->reseller->credit + $m->amount,
                'currency' => $m->currency,
            ]);
            $m->reseller->increment(
                'credit',
                $m->amount
            );
            // commission
            $rows = DB::select("
            WITH recursive recuresive_resellers ( id, upline_id, level, name, commission_percentage, coin ) AS (
                SELECT
                    id,
                    upline_id,
                    level,
                    name,
                    commission_percentage,
                    coin 
                FROM
                    resellers 
                WHERE
                    id = :id
                UNION ALL
                SELECT
                    r.id,
                    r.upline_id,
                    r.level,
                    r.name,
                    r.commission_percentage,
                    r.coin 
                FROM
                    resellers r
                    INNER JOIN recuresive_resellers ON r.id = recuresive_resellers.upline_id 
            )
            SELECT
                id AS user_id,
                'reseller' AS user_type,
                :type AS type,
                :amount * commission_percentage AS amount,
                coin AS coin 
            FROM
                recuresive_resellers
            ORDER BY user_id DESC
            ", [
                'id' => $m->reseller->id,
                'amount' => $m->amount,
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
        }
        // send notification
        switch ($status) {
            case MerchantWithdrawal::STATUS['PENDING']:
                $m->reseller->notify(new \App\Notifications\WithdrawalPending($m));
                break;
            case MerchantWithdrawal::STATUS['APPROVED']:
                $m->merchant->notify(new \App\Notifications\WithdrawalFinish($m));
                $m->update([
                    'callback_status' => MerchantWithdrawal::CALLBACK_STATUS['PENDING']
                ]);
                // push deposit information callback to callback url
                Queue::push((new \App\Jobs\GuzzleJob(
                    $m,
                    new \App\Transformers\Api\WithdrawalTransformer,
                    $m->merchant->api_key
                )));
                break;
        }
    }
}
