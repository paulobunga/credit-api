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

            $o = MerchantDeposit::where('merchant_deposits.status', MerchantDeposit::STATUS['PENDING'])
                ->join('reseller_bank_cards', 'reseller_bank_cards.id', 'merchant_deposits.reseller_bank_card_id')
                ->join('resellers', 'resellers.id', 'reseller_bank_cards.reseller_id')
                ->where('merchant_deposits.currency', $currency)
                ->where('merchant_deposits.created_at', '<=', Carbon::now()->subMinutes($expired_minutes))
                ->select('resellers.name', 'merchant_deposits.currency', 'merchant_deposits.merchant_order_id', 'merchant_deposits.amount')
                ->get();

            MerchantDeposit::where('status', MerchantDeposit::STATUS['PENDING'])
                ->where('currency', $currency)
                ->where('created_at', '<=', Carbon::now()->subMinutes($expired_minutes))
                ->update(['status' => MerchantDeposit::STATUS['EXPIRED']]);

            if (!empty($o->toArray()) && $o->count() >= $expired_limit) {
                $reports[$currency][$o->first()->name] = $o->count();
                $reports[$currency]['data'] = $o;
            }
        }

        if (!empty($reports)) {
            \App\Models\Admin::all()->each(function ($admin) use ($reports) {
                $admin->notify(new \App\Notifications\DepositExpiredReport($reports));
            });
        }
    }
}
