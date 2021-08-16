<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ResellerDeposit;
use App\Models\Transaction;

trait ResellerDepositObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $last_insert_id = DB::select("SELECT MAX(id) AS ID FROM reseller_deposits")[0]->ID ?? 0;
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
     * @param  \App\Models\MerchantDeposit
     * @return void
     */
    protected static function onStatusChangeEvent($status, ResellerDeposit $m)
    {

        DB::beginTransaction();
        try {
            // approve
            if ($status == ResellerDeposit::STATUS['APPROVED']) {
                $m->transactions()->create([
                    'user_id' => $m->reseller_id,
                    'user_type' => 'reseller',
                    'type' => $m->transaction_type,
                    'amount' => $m->amount,
                    'currency' => $m->reseller->currency
                ]);
                if ($m->type == ResellerDeposit::TYPE['CREDIT']) {
                    $m->reseller->increment('credit', $m->amount);
                } elseif ($m->type == ResellerDeposit::TYPE['COIN']) {
                    $m->reseller->increment('coin', $m->amount);
                }
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
