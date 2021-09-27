<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PaymentChannel;
use App\Models\ResellerWithdrawal;

class ExecuteSql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exec:sql {method} {args?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'execute SQL method';

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
        $method =  Str::camel($this->argument('method'));
        $args = $this->argument('args');
        try {
            $this->$method(...$args);
            $this->info('success');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function withdrawalAddResellerBankCardId()
    {
        if (Schema::hasColumn('reseller_withdrawals', 'reseller_bank_card_id')) {
            throw new \Exception('column exist!');
        }
        DB::statement('TRUNCATE TABLE reseller_withdrawals');
        Schema::table('reseller_withdrawals', function (Blueprint $table) {
            $table->foreignId('reseller_bank_card_id')->after('reseller_id')->constrained();
        });
    }

    protected function paymentChannelAddPayinPayout()
    {
        if (!Schema::hasColumn('payment_channels', 'payin')) {
            Schema::table('payment_channels', function (Blueprint $table) {
                $table->json('payin')->after('attributes')->default(new Expression('(JSON_OBJECT())'));
            });
            PaymentChannel::where('id', '<>', 0)->update([
                'payin' => [
                    'status' => true,
                    'min' => 500,
                    'max' => 50000
                ]
            ]);
        }
        if (!Schema::hasColumn('payment_channels', 'payout')) {
            Schema::table('payment_channels', function (Blueprint $table) {
                $table->json('payout')->after('payin')->default(new Expression('(JSON_OBJECT())'));
            });
            PaymentChannel::where('id', '<>', 0)->update([
                'payout' => [
                    'status' => true,
                    'min' => 2000,
                    'max' => 50000
                ]
            ]);
        }
        if (Schema::hasColumn('payment_channels', 'status')) {
            Schema::table('payment_channels', function (Blueprint $table) {
                $table->dropColumn(['status']);
            });
        }
    }

    protected function addCurrencyExpiredMinute()
    {
        $setting = app(\App\Settings\CurrencySetting::class);
        foreach ($setting->currency as $currency => $s) {
            if (isset($s['expired_minutes'])) {
                continue;
            }
            $s['expired_minutes'] = 5;
            $setting->currency[$currency] = $s;
        }
        $setting->save();
    }

    protected function resellerWithdrawalsAlterCardId()
    {
        if (Schema::hasColumn('reseller_withdrawals', 'reseller_bank_card_id')) {
            Schema::table('reseller_withdrawals', function (Blueprint $table) {
                $table->dropForeign(['reseller_bank_card_id']);
                $table->dropIndex('reseller_withdrawals_reseller_bank_card_id_foreign');
                $table->unsignedBigInteger('reseller_bank_card_id')->default(0)->change();
            });
        }
    }

    protected function resellerWithdrawalsExtra()
    {
        foreach (ResellerWithdrawal::all() as $rw) {
            $extra = $rw->extra;
            $extra['payment_type'] ??= 'OTHER';
            $extra['reason'] ??= 'Withdrawal';
            $extra['remark'] ??= 'OTHER';
            $extra['memo'] ??= 'success';
            $extra['creator'] ??= $rw->reseller_bank_card_id ? $rw->reseller_id : $rw->audit_admin_id;
            $rw->extra = $extra;
            $rw->save();
        }
    }
}
