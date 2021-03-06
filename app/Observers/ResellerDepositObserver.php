<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ResellerDeposit;

trait ResellerDepositObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($m) {
            $last_insert_id = DB::select("SELECT MAX(id) AS ID FROM reseller_deposits")[0]->ID ?? 0;
            $m->order_id = Str::random(4) . ($last_insert_id + 1) . '@' . Str::random(20);
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
    protected static function onStatusChangeEvent($status, ResellerDeposit $m)
    {
        $reseller = $m->reseller;
        if ($status == ResellerDeposit::STATUS['APPROVED']) {
            if ($m->type == ResellerDeposit::TYPE['CREDIT']) {
                DB::beginTransaction();
                try {
                    $m->transactions()->create([
                        'user_id' => $m->reseller_id,
                        'user_type' => 'reseller',
                        'type' => $m->transaction_type,
                        'amount' => $m->amount,
                        'before' => $reseller->credits->credit,
                        'after' => $reseller->credits->credit + $m->amount,
                        'currency' => $reseller->currency
                    ]);
                    $reseller->credits->increment('credit', $m->amount);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    DB::rollback();
                    throw $e;
                }
                DB::commit();
            } elseif ($m->type == ResellerDeposit::TYPE['COIN']) {
                DB::beginTransaction();
                try {
                    $m->transactions()->create([
                        'user_id' => $m->reseller_id,
                        'user_type' => 'reseller',
                        'type' => $m->transaction_type,
                        'amount' => $m->amount,
                        'before' => $reseller->credits->coin,
                        'after' => $reseller->credits->coin + $m->amount,
                        'currency' => $reseller->currency
                    ]);
                    $reseller->credits->increment('coin', $m->amount);
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    DB::rollback();
                    throw $e;
                }
                DB::commit();
            }
        }
    }
}
