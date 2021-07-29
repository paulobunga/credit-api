<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Trait\Sortable;
use App\Trait\Filterable;

class MerchantDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'reseller_bank_card_id',
        'order_id',
        'merchant_order_id',
        'account_no',
        'account_name',
        'amount',
        'status',
        'callback_status',
        'attempts',
        'callback_url',
        'reference_no',
        'info'
    ];

    protected const STATUS = [
        'CREATED' => 0,
        'PENDING' => 1,
        'APPROVED' => 2,
        'REJECTED' => 3,
        'ENFORCED' => 4,
        'CANCELED' => 5,
    ];

    protected const CALLBACK_STATUS = [
        'CREATED' => 0,
        'PENDING' => 1,
        'FINISH' => 2,
        'FAILED' => 3,
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function resellerBankCard()
    {
        return $this->hasOne(ResellerBankCard::class, 'id', 'reseller_bank_card_id');
    }

    public function reseller()
    {
        return $this->hasOneThrough(
            Reseller::class,
            ResellerBankCard::class,
            'id',
            'id',
            'reseller_bank_card_id',
            'reseller_id'
        );
    }

    public function bank()
    {
        return $this->hasOneThrough(
            Bank::class,
            ResellerBankCard::class,
            'id',
            'id',
            'reseller_bank_card_id',
            'bank_id'
        );
    }

    public function paymentMethod()
    {
        return $this->resellerBankCard->paymentMethod();
    }

    public function transactions()
    {
        return $this->morphToMany(Transaction::class, 'model', 'model_has_transactions');
    }

    public function setStatusAttribute($value)
    {
        $methods = TransactionMethod::all()->pluck('id', 'name');
        DB::beginTransaction();
        try {
            $this->attributes['status'] = $value;
            // approve or enforce
            if ($value == self::STATUS['APPROVED'] || $value == self::STATUS['ENFORCED']) {
                // reseller
                $transaction = $this->transactions()->create([
                    'transaction_method_id' => $methods['DEDUCT_CREDIT'],
                    'amount' => $this->amount
                ]);
                $this->reseller->decrement('credit', $transaction->amount);
                if ($value == self::STATUS['APPROVED']) {
                    $transaction = $this->transactions()->create([
                        'transaction_method_id' => $methods['TOPUP_COIN'],
                        'amount' => $this->amount * $this->reseller->commission_percentage
                    ]);
                    $this->reseller->increment('coin', $transaction->amount);
                }
                // merchant
                $transaction = $this->transactions()->create([
                    'transaction_method_id' => $methods['TOPUP_CREDIT'],
                    'amount' => $this->amount
                ]);
                $this->merchant->increment('credit', $transaction->amount);
                $transaction = $this->transactions()->create([
                    'transaction_method_id' => $methods['TRANSACTION_FEE'],
                    'amount' => $this->amount * $this->merchant->transaction_fee
                ]);
                $this->merchant->decrement('credit', $transaction->amount);
            }
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
