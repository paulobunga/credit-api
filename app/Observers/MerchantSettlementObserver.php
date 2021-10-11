<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MerchantSettlement;
use App\Models\Transaction;

trait MerchantSettlementObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $last_insert_id = DB::select("SELECT MAX(id) AS ID FROM merchant_settlements")[0]->ID ?? 0;
            $query->order_id = Str::random(4) . ($last_insert_id + 1) . '@' . Str::random(20);
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
     * @param int $status
     * @param \App\Models\MerchantSettlement $m
     * @throws \Exception $e if currency type is not supported
     * @throws \Exception $e if DB transaction error occurs
     * @return void
     */
    protected static function onStatusChangeEvent($status, MerchantSettlement $m)
    {
        // approve
        if ($status == MerchantSettlement::STATUS['APPROVED']) {
            $credit = $m->merchant->credits()->where('currency', $m->currency)->first();
            if (!$credit) {
                throw new \Exception('Currency type is not supported!');
            }
            if ($credit->credit < $m->amount) {
                throw new \Exception('exceed merchant credit', 405);
            }
            DB::beginTransaction();
            try {
                $m->transactions()->create([
                    'user_id' => $m->merchant_id,
                    'user_type' => 'merchant',
                    'type' => Transaction::TYPE['MERCHANT_SETTLE_CREDIT'],
                    'amount' => $m->amount,
                    'before' => $credit->credit,
                    'after' => $credit->credit - $m->amount,
                    'currency' => $m->currency,
                ]);
                $credit->decrement('credit', $m->amount);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                DB::rollback();
                throw $e;
            }
            DB::commit();
        }
    }
}
