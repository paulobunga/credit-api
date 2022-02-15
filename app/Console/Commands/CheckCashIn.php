<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\MerchantDeposit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

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

        $redis_table = "payin_expired_records";
        $redis = Redis::connection();
        $redis_data = json_decode($redis->get($redis_table), true);

        foreach ($setting as $currency => $s) {
            $expired_minutes = $s['expired_minutes'];

            $o = MerchantDeposit::where('merchant_deposits.status', MerchantDeposit::STATUS['PENDING'])
                ->join('reseller_bank_cards', 'reseller_bank_cards.id', 'merchant_deposits.reseller_bank_card_id')
                ->join('resellers', 'resellers.id', 'reseller_bank_cards.reseller_id')
                ->where('merchant_deposits.currency', $currency)
                ->where('merchant_deposits.created_at', '<=', Carbon::now()->subMinutes($expired_minutes))
                ->select(
                    'resellers.name',
                    'merchant_deposits.currency',
                    DB::raw('COUNT(resellers.name) AS total_expired'),
                    DB::raw('TRUNCATE(SUM(merchant_deposits.amount), 2) AS total_amount')
                )
                ->groupBy('resellers.name', 'merchant_deposits.currency')
                ->get();

            MerchantDeposit::where('status', MerchantDeposit::STATUS['PENDING'])
                ->where('currency', $currency)
                ->where('created_at', '<=', Carbon::now()->subMinutes($expired_minutes))
                ->update(['status' => MerchantDeposit::STATUS['EXPIRED']]);

            if (!empty($o->toArray())) {
                foreach ($o->toArray() as $val) {
                    $agent_name = $val["name"];
                    $p = [ "total_amount" => 0, "total_expired" => 0 ];

                    if (!is_null($redis_data)) {
                        if (!empty($redis_data[$currency])) {
                            $p = isset($redis_data[$currency][$agent_name]) ? $redis_data[$currency][$agent_name] : $p;
                        }
                    }

                    $total_amount = (float) $p["total_amount"] + (float) $val["total_amount"];
                    $total_expired = (int) $p["total_expired"] + $val["total_expired"];

                    if ($total_expired >= $expired_limit) {
                        $reports[$currency][$val["name"]] = [];
                        $reports[$currency][$val["name"]] = [
                            "total_amount" => $total_amount,
                            "total_expired" => $total_expired,
                        ];

                        if (isset($redis_data[$currency][$val["name"]])) {
                            unset($redis_data[$currency][$val["name"]]);
                        }
                    } else {
                        $redis_data[$currency][$val["name"]] = [];
                        $redis_data[$currency][$val["name"]] = [
                            "total_amount" => $total_amount,
                            "total_expired" => $total_expired,
                        ];
                    }
                }
            }
        }
        $redis->set($redis_table, json_encode($redis_data));

        if (!empty($reports)) {
            $notifyModel = new \App\Notifications\DepositExpiredReport($reports, Carbon::now()->toDateTimeString());
            broadcast(new \App\Events\AdminNotification($notifyModel->toArray($reports), $notifyModel));
        }
    }
}
