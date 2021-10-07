<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ResellerWithdrawal;

trait ResellerWithdrawalObserver
{
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $last_insert_id = DB::select("SELECT MAX(id) AS ID FROM reseller_withdrawals")[0]->ID ?? 0;
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
    protected static function onStatusChangeEvent($status, ResellerWithdrawal $m)
    {

        DB::beginTransaction();
        try {
            // approve
            if ($status == ResellerWithdrawal::STATUS['APPROVED']) {
                $reseller = $m->reseller;
                if ($m->type == ResellerWithdrawal::TYPE['CREDIT']) {
                    if ($reseller->withdrawalCredit < $m->amount) {
                        throw new \Exception('exceed agent withdrawal credit', 405);
                    }
                    $m->transactions()->create([
                        'user_id' => $m->reseller_id,
                        'user_type' => 'reseller',
                        'type' => $m->transaction_type,
                        'amount' => $m->amount,
                        'before' => $reseller->credit,
                        'after' => $reseller->credit - $m->amount,
                        'currency' => $reseller->currency
                    ]);
                    $reseller->decrement('credit', $m->amount);
                } elseif ($m->type == ResellerWithdrawal::TYPE['COIN']) {
                    if ($reseller->withdrawalCoin < $m->amount) {
                        throw new \Exception('exceed agent withdrawal coin', 405);
                    }
                    $m->transactions()->create([
                        'user_id' => $m->reseller_id,
                        'user_type' => 'reseller',
                        'type' => $m->transaction_type,
                        'amount' => $m->amount,
                        'before' => $reseller->coin,
                        'after' => $reseller->coin - $m->amount,
                        'currency' => $reseller->currency
                    ]);
                    $reseller->decrement('coin', $m->amount);
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
