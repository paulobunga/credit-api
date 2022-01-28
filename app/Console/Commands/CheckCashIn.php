<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\MerchantDeposit;

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

        foreach ($setting as $currency => $s) {
            $o = MerchantDeposit::where('status', MerchantDeposit::STATUS['PENDING'])
                ->where('currency', $currency)
                ->where('created_at', '<=', Carbon::now()->subMinutes($s['expired_minutes']))
                ->update([
                    'status' => MerchantDeposit::STATUS['EXPIRED']
                ]);
        }

        $expired_rows = MerchantDeposit::where('status', MerchantDeposit::STATUS['EXPIRED'])->get();
        $expired_limit = app(\App\Settings\AdminSetting::class)->expired_payin_limit;
        if($expired_rows->count() >= $expired_limit) {
            \App\Models\Admin::all()->each(function ($admin) use ($expired_rows) {
              $admin->notify(new \App\Notifications\DepositExpiredReport($expired_rows->toArray()));
            });
        }
    }
}
