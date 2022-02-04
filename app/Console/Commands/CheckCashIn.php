<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\MerchantDeposit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        foreach ($setting as $currency => $s) {
            $expired_minutes = $s['expired_minutes'];

            $k = MerchantDeposit::where('status', MerchantDeposit::STATUS['PENDING'])
              ->where('currency', $currency)
              ->where('created_at', '<=', Carbon::now()->subMinutes($expired_minutes))
              ->update(['status' => MerchantDeposit::STATUS['EXPIRED']]);

            // $o = MerchantDeposit::where('merchant_deposits.status', MerchantDeposit::STATUS['EXPIRED'])
            //   ->join('reseller_bank_cards', 'reseller_bank_cards.id', 'merchant_deposits.reseller_bank_card_id')
            //   ->join('resellers', 'resellers.id', 'reseller_bank_cards.reseller_id')
            //   ->where('merchant_deposits.currency', $currency)
            //   ->where('merchant_deposits.created_at', '<=', Carbon::now()->subMinutes($expired_minutes))
            //   ->having(DB::raw('COUNT(resellers.name)'), '>=', $expired_limit)
            //   ->select('resellers.name', DB::raw('COUNT(resellers.name) AS total_expired'), DB::raw('TRUNCATE(SUM(merchant_deposits.amount), 2) AS total_amount'), 'merchant_deposits.currency')
            //   ->groupBy('resellers.name', 'merchant_deposits.currency')
            //   ->get();

            // if (!empty($o->toArray()) && $k > 0) {
            //     $reports[$currency] = [];
            //     foreach ($o as $k => $v) {
            //         $reports[$currency][$v->name] = $v->total_expired;
            //         $reports[$currency]['Total Amount'] = $v->total_amount;
            //     }
            // }
        }

        if (!empty($reports)) {
            \App\Models\Admin::all()->each(function ($admin) use ($reports) {
                $admin->notify(new \App\Notifications\DepositExpiredReport($reports));
            });
        }
    }
}
