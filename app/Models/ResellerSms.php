<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'amount',
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
        'INVALID' => 403,
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * Parse SMS data from channels
     *
     * @param  array $sms
     * @param  mixed $channels
     * @return array
     */
    public static function parse(array $sms, $channels)
    {
        $data = [];
        foreach ($channels as $ch) {
            if (!$ch->isSupportSMS()) {
                continue;
            }
            if (!in_array($sms['address'], $ch->payin->sms_addresses)) {
                continue;
            }
            $data = $ch->getReference()->extractSMS($sms['body']);
            if (empty($data)) {
                continue;
            }
            if (!empty($data['trx_id']) && !empty($data['amount'])) {
                break;
            }
        }

        return $data;
    }
}
