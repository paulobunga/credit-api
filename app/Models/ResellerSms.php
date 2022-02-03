<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentChannel;
use App\Models\MerchantDeposit;
use App\Trait\UserTimezone;

class ResellerSms extends Model
{
    use UserTimezone;

    public $timestamps = false;

    protected $fillable = [
        'reseller_id',
        'model_id',
        'model_name',
        'platform',
        'address',
        'trx_id',
        'sim_num',
        'body',
        'status',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'sent_at'  => 'datetime:Y-m-d H:i:s',
        'received_at' => 'datetime:Y-m-d H:i:s',
    ];

    public const STATUS = [
        'PENDING' => 0,
        'MATCH' => 1,
        'UNMATCH' => 2,
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * Match SMS with payin orders
     *
     * @param  array $sms
     * @return array
     */
    public static function match(array $sms)
    {
        $channels = PaymentChannel::where('currency', $sms['currency'])->get();

        $data = [];
        foreach ($channels as $ch) {
            if (!$ch->isSupportSMS()) {
                continue;
            }
            if ($ch->currency != $sms['currency']) {
                continue;
            }
            if (!in_array($sms['address'], $ch->payin->sms_addresses)) {
                continue;
            }
            $data = $ch->getReference()->extractSMS($sms['body']);
            if (empty($data)) {
                continue;
            }
            if (!$data['trx_id']) {
                continue;
            } else {
                break;
            }
        }

        if (!isset($data['trx_id']) || empty($data['trx_id'])) {
            return $data;
        }

        $match = null;

        $deposits = MerchantDeposit::with('paymentChannel')->whereHas('reseller', function ($q) use ($sms) {
            $q->where('resellers.id', $sms['reseller_id']);
        })->whereIn('status', [
            MerchantDeposit::STATUS['PENDING'],
        ])->orderByDesc('id')->get();

        foreach ($deposits as $d) {
            if (
                $data['amount'] == $d->amount &&
                $data['payer'] == $d->extra['sender_mobile_number'] &&
                in_array($sms['address'], $d->paymentChannel->payin->sms_addresses)
            ) {
                $match = $d;
                break;
            }
        }
        $data['match'] = $match;

        return $data;
    }
}
