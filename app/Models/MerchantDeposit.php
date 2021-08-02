<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Transaction;

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
        DB::beginTransaction();
        try {
            $this->attributes['status'] = $value;
            // approve or enforce
            if (in_array($value, [self::STATUS['APPROVED'], self::STATUS['ENFORCED']])) {
                // merchant add credit and deduct transaction fee
                $this->transactions()->create([
                    'user_id' => $this->merchant_id,
                    'user_type' => 'merchant',
                    'type' => Transaction::TYPE['TOPUP_CREDIT'],
                    'amount' => $this->amount
                ]);
                $this->transactions()->create([
                    'user_id' => $this->merchant_id,
                    'user_type' => 'merchant',
                    'type' => Transaction::TYPE['TRANSACTION_FEE'],
                    'amount' => - ($this->amount * $this->merchant->transaction_fee)
                ]);
                $this->merchant->increment(
                    'credit',
                    $this->merchant->credit - $this->amount * $this->merchant->transaction_fee
                );
                // reseller
                $this->transactions()->create([
                    'user_id' => $this->reseller->id,
                    'user_type' => 'reseller',
                    'type' => Transaction::TYPE['DEDUCT_CREDIT'],
                    'amount' => - ($this->amount)
                ]);
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
                    'id' => $this->reseller->id,
                    'amount' => $this->amount,
                    'type' => Transaction::TYPE['COMMISSION']
                ]);
                foreach ($rows as $row) {
                    $this->transactions()->create([
                        'user_id' => $row->user_id,
                        'user_type' => 'reseller',
                        'type' => Transaction::TYPE['COMMISSION'],
                        'amount' => $row->amount
                    ]);
                    if ($row->user_id == $this->reseller->id) {
                        $this->reseller->update([
                            'credit' => $this->reseller->credit - $this->amount,
                            'coin' => $this->reseller->coin + $row->amount
                        ]);
                        continue;
                    }
                    DB::table('resellers')->where('id', $row->user_id)->increment('coin', $row->amount);
                }
            }
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
