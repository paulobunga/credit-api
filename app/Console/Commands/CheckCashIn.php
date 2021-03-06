<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\MerchantDeposit;
use App\Notifications\Admin\DepositExpired;

class CheckCashIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:cashin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expiration of cash in orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $setting = app(\App\Settings\CurrencySetting::class)->currency;
        $expired_limit = app(\App\Settings\AdminSetting::class)->expired_payin_limit_notify;
        $reports = [];

        $redis = Redis::connection();

        foreach ($setting as $currency => $s) {
            $expired_minutes = $s['expired_minutes'];

            $o = MerchantDeposit::where('merchant_deposits.status', MerchantDeposit::STATUS['PENDING'])
                ->join('reseller_bank_cards', 'reseller_bank_cards.id', 'merchant_deposits.reseller_bank_card_id')
                ->join('resellers', 'resellers.id', 'reseller_bank_cards.reseller_id')
                ->where('merchant_deposits.currency', $currency)
                ->where('merchant_deposits.created_at', '<=', Carbon::now()->subMinutes($expired_minutes))
                ->select(
                    'resellers.id',
                    'resellers.name',
                    'merchant_deposits.currency',
                    DB::raw('GROUP_CONCAT(merchant_deposits.merchant_order_id) AS merchant_order_id'),
                    DB::raw('COUNT(resellers.name) AS count'),
                    DB::raw('TRUNCATE(SUM(merchant_deposits.amount), 2) AS amount')
                )
                ->groupBy('resellers.id', 'resellers.name', 'merchant_deposits.currency')
                ->get();

            MerchantDeposit::where('status', MerchantDeposit::STATUS['PENDING'])
                ->where('currency', $currency)
                ->where('created_at', '<=', Carbon::now()->subMinutes($expired_minutes))
                ->update(['status' => MerchantDeposit::STATUS['EXPIRED']]);

            foreach ($o->toArray() as $val) {
                $redis_key = "payin_expire_order_" . $val["id"];
                $redis_data = json_decode($redis->get($redis_key), true) ??
                    $redis_data = [
                        "count" => 0,
                        "amount" => 0,
                        "agent" => $val["name"],
                        "currency" => $currency,
                        "merchant_order_id" => []
                    ];

                $redis_data["count"] = (int) $redis_data["count"] + $val["count"];
                $redis_data["amount"] = (float) $redis_data["amount"] + (float)$val["amount"];
                $redis_data["merchant_order_id"] = array_merge(
                    $redis_data["merchant_order_id"],
                    explode(",", $val["merchant_order_id"])
                );

                if ($redis_data["count"] >= $expired_limit) {
                    $notifyModel = new DepositExpired($redis_data);
                    broadcast(new \App\Events\AdminNotification($notifyModel->toArray($redis_data), $notifyModel));
                    $redis->del($redis_key);
                } else {
                    $redis->set($redis_key, json_encode($redis_data));
                }
            }
        }
    }
}
