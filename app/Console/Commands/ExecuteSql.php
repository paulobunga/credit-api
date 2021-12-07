<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Reseller;
use App\Models\PaymentChannel;
use App\Models\ResellerDeposit;
use App\Models\ResellerWithdrawal;
use App\Models\ResellerOnline;

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

    protected function resellerDepositsExtra()
    {
        foreach (ResellerDeposit::all() as $rd) {
            $extra = $rd->extra;
            $extra['payment_type'] ??= 'OTHER';
            $extra['reason'] ??= 'OTHER';
            $extra['remark'] ??= 'Top Up';
            $extra['memo'] ??= $extra['audit']['memo'] ?? 'success';
            $extra['creator'] ??= $rd->audit_admin_id;
            $rd->extra = $extra;
            $rd->save();
        }
    }

    # payout migration
    protected function merchantSettlementMerchantWithdrawals()
    {
        if (!Schema::hasTable('merchant_settlements')) {
            Schema::rename('merchant_withdrawals', 'merchant_settlements');
            Schema::table('merchant_settlements', function ($table) {
                $table->renameIndex(
                    'merchant_withdrawals_order_id_unique',
                    'merchant_settlements_order_id_unique'
                );
                $table->renameIndex(
                    'merchant_withdrawals_merchant_id_foreign',
                    'merchant_settlements_merchant_id_foreign'
                );
                $table->dropForeign('merchant_withdrawals_merchant_id_foreign');
                $table->foreign('merchant_id')->references('id')->on('merchants');
            });
        }
        if (!Schema::hasTable('merchant_withdrawals')) {
            Schema::create('merchant_withdrawals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('merchant_id')
                    ->constrained();
                $table->foreignId('reseller_id')
                    ->constrained();
                $table->foreignId('payment_channel_id')
                    ->constrained();
                $table->string('order_id', 60)->unique();
                $table->string('merchant_order_id', 60);
                $table->json('attributes')->default(new Expression('(JSON_ARRAY())'));
                $table->decimal('amount', 14, 4);
                $table->string('currency', 6);
                $table->unsignedTinyInteger('status')
                    ->default(0)
                    ->comment('0:Created,1:Pending,2:Approved,3:Rejected,4:Enforced,5:Canceled');
                $table->unsignedTinyInteger('callback_status')
                    ->default(0)
                    ->comment('0:Created,1:Pending,2:Finish,3:Failed');
                $table->unsignedTinyInteger('attempts')
                    ->default(0);
                $table->string('callback_url');
                $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
                $table->timestamps();
                $table->timestamp('notified_at')->nullable();
                $table->unique(
                    ['merchant_id', 'merchant_order_id', 'currency'],
                    'merchant_withdrawals_merchant_order_id_unique'
                );
            });
        }
    }

    protected function createDevicesTable()
    {
        if (!Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->unsignedBigInteger('user_id');
                $table->string('user_type', 30);
                $table->string('platform', 10);
                $table->text('token');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('logined_at');
            });
        }
    }

    protected function resellerPayinPayOut()
    {
        $cs = app(\App\Settings\CurrencySetting::class);
        $rs = app(\App\Settings\ResellerSetting::class);
        if (!Schema::hasColumn('resellers', 'payin')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->json('payin')->after('currency')->default(new Expression('(JSON_OBJECT())'));
            });
            foreach (Reseller::all() as $r) {
                $r->payin = [
                    'commission_percentage' => $r->commission_percentage ??
                        $cs->getCommissionPercentage($r->currency, $r->level),
                    'pending_limit' => $r->pending_limit ?? $rs->getDefaultPendingLimit($r->level),
                    'status' => $r->level == Reseller::LEVEL['RESELLER']
                ];
                $r->save();
            }
        }
        if (!Schema::hasColumn('resellers', 'payout')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->json('payout')->after('payin')->default(new Expression('(JSON_OBJECT())'));
            });
            foreach (Reseller::all() as $r) {
                $r->payout = [
                    'commission_percentage' => $r->commission_percentage ??
                        $cs->getCommissionPercentage($r->currency, $r->level),
                    'pending_limit' => $r->pending_limit ?? $rs->getDefaultPendingLimit($r->level),
                    'status' => $r->level == Reseller::LEVEL['RESELLER'],
                ];
                $r->save();
            }
        }
        if (Schema::hasColumn('resellers', 'pending_limit')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->dropColumn(['pending_limit']);
            });
        }
        if (Schema::hasColumn('resellers', 'commission_percentage')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->dropColumn(['commission_percentage']);
            });
        }
    }

    protected function addPayinPayoutPlayerID()
    {
        if (!Schema::hasColumn('merchant_deposits', 'player_id')) {
            Schema::table('merchant_deposits', function (Blueprint $table) {
                $table->string('player_id', 40)->after('merchant_order_id')->default(0);
            });
        }
        if (!Schema::hasColumn('merchant_withdrawals', 'player_id')) {
            Schema::table('merchant_withdrawals', function (Blueprint $table) {
                $table->string('player_id', 40)->after('merchant_order_id')->default(0);
            });
        }
    }

    protected function addPayoutAutoApproval()
    {
        DB::beginTransaction();
        try {
            DB::statement("
                Update payment_channels
                SET payout = JSON_SET(payout, '$.auto_approval', false)
            ");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }

    protected function addAgentLevel()
    {
        if (!Schema::hasColumn('resellers', 'uplines')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->json('uplines')->after('upline_id')->default(new Expression('(JSON_ARRAY())'));
            });
        }
        DB::beginTransaction();
        try {
            foreach (Reseller::all() as $r) {
                $uplines = [];
                $agent = $r->agent;
                while ($agent) {
                    array_unshift($uplines, $agent->id);
                    $agent = $agent->agent;
                }
                $r->uplines = $uplines;
                $r->save();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }

    protected function addReportExtra()
    {
        if (!Schema::hasColumn('report_daily_resellers', 'extra')) {
            Schema::table('report_daily_resellers', function (Blueprint $table) {
                $table->json('extra')->after('coin')->default(new Expression('(JSON_OBJECT())'));
            });
        }
        if (!Schema::hasColumn('report_daily_merchants', 'extra')) {
            Schema::table('report_daily_merchants', function (Blueprint $table) {
                $table->json('extra')->after('currency')->default(new Expression('(JSON_OBJECT())'));
            });
        }
    }

    protected function addResellersPayoutDailyAmount()
    {
        DB::beginTransaction();
        try {
            DB::statement("
                Update resellers
                SET payout = JSON_SET(payout, '$.daily_amount_limit', 50000)
            ");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }

    protected function reportRegenerate($start_date, $end_date = null)
    {
        DB::statement('TRUNCATE TABLE report_daily_merchants');
        DB::statement('TRUNCATE TABLE report_daily_resellers');
        $end_date ??= Carbon::yesterday()->toDateString();
        $periods = CarbonPeriod::create($start_date, $end_date);
        foreach ($periods as $date) {
            $this->info($date->format('Y-m-d'));
            Artisan::call("report:daily {$date->format('Y-m-d')}");
        }
    }

    protected function addBDTCurrencyAndPaymentChannel()
    {
        $cs = app(\App\Settings\CurrencySetting::class);
        $currency = array_merge($cs->currency, [
            'BDT' => [
                'referrer_percentage' => 0,
                'master_agent_percentage' => 0.003,
                'agent_percentage' => 0.004,
                'reseller_percentage' => 0.005,
                'transaction_fee_percentage' => 0.001,
                'expired_minutes' => 5,
            ]
        ]);
        $cs->currency = $currency;
        $cs->save();
        $cs->refresh();
        $channels = [
            'BKASH' => [
                'BDT' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['wallet_number']
                ],
            ],
            'NAGAD' => [
                'BDT' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['wallet_number']
                ],
            ],
            'ROCKET' => [
                'BDT' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['wallet_number']
                ],
            ],
            'UPAY' => [
                'BDT' => [
                    'methods' => [
                        PaymentChannel::METHOD['QRCODE'],
                    ],
                    'attributes' => ['wallet_number']
                ],
            ],
        ];
        foreach ($channels as $name => $ch) {
            foreach ($ch as $currency => $s) {
                PaymentChannel::firstOrCreate(
                    [
                        'name' => $name,
                        'currency' => $currency,
                    ],
                    [
                        'payment_methods' => implode(',', $s['methods']),
                        'attributes' => $s['attributes'],
                        'banks' => implode(',', $s['banks'] ?? []),
                        'payin' => [
                            'status' => false,
                            'min' => 500,
                            'max' => 30000
                        ],
                        'payout' => [
                            'status' => true,
                            'min' => 500,
                            'max' => 30000,
                            'auto_approval' => false
                        ]
                    ]
                );
            }
        }
    }

    protected function addDeviceUUID()
    {
        if (!Schema::hasColumn('devices', 'uuid')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->uuid('uuid')->after('platform')->default('');
            });
        }
    }

    protected function addResellerSms()
    {
        if (!Schema::hasTable('reseller_sms')) {
            Schema::create('reseller_sms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reseller_id');
                $table->unsignedBigInteger('model_id')->default(0);
                $table->string('model_name', 20)->default('');
                $table->string('platform', 10);
                $table->string('address', 30);
                $table->string('body', 1024);
                $table->unsignedTinyInteger('status')->default(0)->comment('0:Pending,1:Match,2:UnMatch');
                $table->timestamp('sent_at');
                $table->timestamp('received_at');
                $table->timestamp('created_at')->useCurrent();
            });
        }
        DB::beginTransaction();
        try {
            DB::statement("
                Update resellers
                SET payin = JSON_SET(payin, '$.auto_sms_approval', false)
            ");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }

    protected function addPaymentChannelPayInSmsAddresses()
    {
        $channels = [
            'BKASH' => 'bKash',
            'NAGAD' => 'NAGAD',
            'ROCKET' => '16216',
            'UPAY' => 'UPAY',
        ];
        DB::beginTransaction();
        try {
            DB::statement("
                Update payment_channels
                SET payin = JSON_SET(payin, '$.sms_addresses', JSON_ARRAY())
            ");
            foreach ($channels as $name => $address) {
                $p = PaymentChannel::where('name', $name)->first();
                if (!$p) {
                    continue;
                }
                $p->payin->sms_addresses = [$address];
                $p->save();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }

    protected function addAdminTimezone()
    {
        if (!Schema::hasColumn('admins', 'timezone')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('timezone', 60)->after('status')->default(env('APP_TIMEZONE'));
            });
        }
    }

    protected function addResellerOnline()
    {   
        if (!Schema::hasTable('reseller_onlines')) {
            Schema::create('reseller_onlines', function (Blueprint $table) {
              $table->id();
              $table->unsignedBigInteger('reseller_id');
              $table->tinyInteger('status')->default(0)->comment('0:offline,1:online');
              $table->timestamp('last_seen_at')->useCurrent()->useCurrentOnUpdate();
              $table->timestamp('created_at')->useCurrent();
            });
        }

        foreach (Reseller::all() as $r) {
          ResellerOnline::create([
            'reseller_id' => $r->id,
            'status' => 0
          ]);
        }

        if (Schema::hasColumn('resellers', 'online')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->dropColumn(['online']);
            });
        }
        if (Schema::hasColumn('resellers', 'last_seen')) {
            Schema::table('resellers', function (Blueprint $table) {
                $table->dropColumn(['last_seen']);
            });
        }
    }
}
