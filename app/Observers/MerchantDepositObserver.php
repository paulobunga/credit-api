<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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
            $query->order_id = Str::random(4)
                . $query->merchant_id
                . '@'
                . Str::random(10);
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
     * @param  \App\Models\MerchantDeposit
     * @return void
     */
    protected static function onStatusChangeEvent($status, MerchantDeposit $m)
    {
        if (in_array($status, [
            MerchantDeposit::STATUS['APPROVED'],
            MerchantDeposit::STATUS['ENFORCED']
        ])) {
            // merchant add credit and deduct transaction fee
            $m->transactions()->create([
                'user_id' => $m->merchant_id,
                'user_type' => 'merchant',
                'type' => Transaction::TYPE['TOPUP_CREDIT'],
                'amount' => $m->amount
            ]);
            $m->transactions()->create([
                'user_id' => $m->merchant_id,
                'user_type' => 'merchant',
                'type' => Transaction::TYPE['TRANSACTION_FEE'],
                'amount' => - ($m->amount * $m->merchant->transaction_fee)
            ]);
            $m->merchant->increment(
                'credit',
                $m->amount * (1 - $m->merchant->transaction_fee)
            );
            // reseller
            $m->transactions()->create([
                'user_id' => $m->reseller->id,
                'user_type' => 'reseller',
                'type' => Transaction::TYPE['DEDUCT_CREDIT'],
                'amount' => - ($m->amount)
            ]);
            $m->reseller->decrement(
                'credit',
                $m->amount
            );
            // commission
            $rows = DB::select("
            WITH recursive recuresive_resellers ( id, upline_id, level, name, commission_percentage ) AS (
                SELECT
                    id,
                    upline_id,
                    level,
                    name,
                    commission_percentage 
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
                    r.commission_percentage 
                FROM
                    resellers r
                    INNER JOIN recuresive_resellers ON r.id = recuresive_resellers.upline_id 
            )
            SELECT
                id AS user_id,
                'reseller' AS user_type,
                :type AS type,
                :amount * commission_percentage AS amount 
            FROM
                recuresive_resellers
            ORDER BY user_id DESC
            ", [
                'id' => $m->reseller->id,
                'amount' => $m->amount,
                'type' => Transaction::TYPE['COMMISSION']
            ]);
            foreach ($rows as $row) {
                $m->transactions()->create([
                    'user_id' => $row->user_id,
                    'user_type' => 'reseller',
                    'type' => Transaction::TYPE['COMMISSION'],
                    'amount' => $row->amount
                ]);
                if ($row->user_id == $m->reseller->id) {
                    $m->reseller->update([
                        'credit' => $m->reseller->credit - $m->amount,
                        'coin' => $m->reseller->coin + $row->amount
                    ]);
                    continue;
                }
                DB::table('resellers')->where('id', $row->user_id)->increment('coin', $row->amount);
            }
        }
        // send notification
        switch ($status) {
            case MerchantDeposit::STATUS['PENDING']:
                $m->reseller->notify(new \App\Notifications\DepositPendingNotification($m));
                break;
            case MerchantDeposit::STATUS['APPROVED']:
            case MerchantDeposit::STATUS['ENFORCED']:
                $m->merchant->notify(new \App\Notifications\DepositUpdateNotification($m));
                $m->update([
                    'callback_status' => MerchantDeposit::CALLBACK_STATUS['PENDING']
                ]);
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
