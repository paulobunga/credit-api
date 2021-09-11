<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidArgumentException;
use App\Models\Transaction;
use App\Models\ReportDailyMerchant;
use App\Models\ReportDailyReseller;
use App\Models\ReportMonthlyReseller;
use App\Models\ReportMonthlyMerchant;

class ReportDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gnerate daily report';

    protected $type = Transaction::TYPE;
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
        try {
            $date = $this->argument('date') ?
                Carbon::parse($this->argument('date'))->toDateString() : date('Y-m-d', strtotime('-1 day'));
        } catch (InvalidArgumentException $e) {
            $this->error('invalid argument date');
            return;
        }
        $start_datetime = "{$date} 00:00:00";
        $end_datetime = "{$date} 23:59:59";
        $this->calulateResellers($start_datetime, $end_datetime);
        $this->calulateMerchants($start_datetime, $end_datetime);
    }

    protected function calulateResellers($start_datetime, $end_datetime)
    {
        if (ReportDailyReseller::where('start_at', '<=', $start_datetime)
            ->where('end_at', '>=', $end_datetime)->first()
        ) {
            $this->warn("reseller daily report[{$start_datetime}-{$end_datetime}] exists'");
            return;
        }

        $sql = "
        WITH daily_transaction AS (        
            SELECT
                    t.id, 
                    t.user_id,
                    t.user_type, 
                    t.type,
                    t.amount,
                    t.currency,
                    t.created_at
            FROM transactions AS t
            WHERE t.created_at BETWEEN '{$start_datetime}' AND '{$end_datetime}'
                AND user_type = 'reseller'
        ),
        daily_merchant_deposit AS(
            SELECT 
                dt.user_id AS reseller_id,
                COUNT( DISTINCT md.id) AS turnover,
                SUM(
                    CASE 
                        WHEN dt.type = {$this->type['SYSTEM_DEDUCT_CREDIT']} THEN dt.amount
                    END
                ) AS credit,
                SUM(
                    CASE 
                        WHEN dt.type = {$this->type['SYSTEM_TOPUP_COMMISSION']} THEN dt.amount
                    END
                ) AS coin
            FROM daily_transaction AS dt
            JOIN model_has_transactions AS mht ON dt.id = mht.transaction_id AND mht.model_type = 'merchant.deposit'
            LEFT JOIN merchant_deposits AS md ON mht.model_id = md.id
            WHERE dt.type IN (
                {$this->type['SYSTEM_DEDUCT_CREDIT']}, 
                {$this->type['SYSTEM_TOPUP_COMMISSION']}
            )
            GROUP BY reseller_id
        ),
        daily_reseller AS(
            SELECT 
                dt.user_id AS reseller_id,
                SUM(
                    CASE 
                        WHEN dt.type = {$this->type['ADMIN_WITHDRAW_CREDIT']} THEN dt.amount
                        WHEN dt.type = {$this->type['RESELLER_WITHDRAW_CREDIT']} THEN dt.amount
                    END
                ) AS withdrawal_credit,
                SUM(
                    CASE 
                        WHEN dt.type = {$this->type['ADMIN_WITHDRAW_COIN']} THEN dt.amount
                        WHEN dt.type = {$this->type['RESELLER_WITHDRAW_COIN']} THEN dt.amount
                    END
                ) AS withdrawal_coin,
                SUM(
                    CASE 
                        WHEN dt.type = {$this->type['ADMIN_TOPUP_CREDIT']} THEN dt.amount
                        WHEN dt.type = {$this->type['RESELLER_TOPUP_CREDIT']} THEN dt.amount
                    END
                ) AS deposit_credit,
                SUM(
                    CASE 
                        WHEN dt.type = {$this->type['ADMIN_TOPUP_COIN']} THEN dt.amount
                    END
                ) AS deposit_coin
            FROM daily_transaction AS dt
            JOIN model_has_transactions AS mht ON dt.id = mht.transaction_id 
            AND mht.model_type IN (
                'reseller.deposit',
                'reseller.withdrawal'
            )
            WHERE dt.type IN (
                {$this->type['ADMIN_WITHDRAW_CREDIT']},
                {$this->type['ADMIN_WITHDRAW_COIN']},
                {$this->type['RESELLER_WITHDRAW_CREDIT']},
                {$this->type['RESELLER_WITHDRAW_COIN']},
                {$this->type['ADMIN_TOPUP_CREDIT']},
                {$this->type['ADMIN_TOPUP_COIN']},
                {$this->type['RESELLER_TOPUP_CREDIT']}
            )
            GROUP BY reseller_id
        ),
        daily_report AS (
            SELECT 
                reseller_id,
                COALESCE(daily_merchant_deposit.turnover, 0) AS turnover,
                COALESCE(daily_merchant_deposit.credit, 0) AS credit,
                COALESCE(daily_merchant_deposit.coin, 0) AS coin,
                COALESCE(daily_reseller.withdrawal_credit, 0) AS withdrawal_credit,
                COALESCE(daily_reseller.withdrawal_coin, 0) AS withdrawal_coin,
                COALESCE(daily_reseller.deposit_credit, 0) AS deposit_credit,
                COALESCE(daily_reseller.deposit_coin, 0) AS deposit_coin
            FROM daily_merchant_deposit
            LEFT JOIN daily_reseller USING(reseller_id)
        )
        SELECT * FROM daily_report
        ";

        $rows = DB::select($sql);
        foreach ($rows as $row) {
            DB::table('report_daily_resellers')->insert([
                'reseller_id' => $row->reseller_id,
                'start_at' => $start_datetime,
                'end_at' => $end_datetime,
                'turnover' => $row->turnover,
                'credit' => $row->credit,
                'coin' => $row->coin,
            ]);

            ReportMonthlyReseller::updateOrCreate(
                [
                    'date' => Carbon::now()->startOfMonth()->toDateString('Y-m-d'),
                    'reseller_id' => $row->reseller_id,
                ],
                [
                    'turnover' => DB::raw('turnover + ' . $row->turnover),
                    'payin' => DB::raw('payin + ' . $row->credit),
                    'payout' => DB::raw('payout + ' . $row->withdrawal_credit),
                    'coin' => DB::raw('coin + ' . $row->coin),
                ],
            );
        }
    }

    protected function calulateMerchants($start_datetime, $end_datetime)
    {
        if (ReportDailyMerchant::where('start_at', '<=', $start_datetime)
            ->where('end_at', '>=', $end_datetime)->first()
        ) {
            $this->warn("merchant daily report[{$start_datetime}-{$end_datetime}] exists'");
            return;
        }
        $sql = "
        WITH daily_transaction AS (        
            SELECT
                    t.id, 
                    t.user_id,
                    t.user_type, 
                    t.type,
                    t.amount,
                    t.currency,
                    t.created_at
            FROM transactions AS t
            WHERE t.created_at BETWEEN '{$start_datetime}' AND '{$end_datetime}'
            AND user_type = 'merchant'
        ),
        daily_merchant_deposit AS(
          SELECT 
                dt.*,
                md.id AS merchant_deposit_id,
                md.merchant_id,
                md.merchant_order_id
            FROM daily_transaction AS dt
            JOIN model_has_transactions AS mht ON dt.id = mht.transaction_id AND mht.model_type = 'merchant.deposit'
            LEFT JOIN merchant_deposits AS md ON mht.model_id = md.id
            LEFT JOIN merchants AS m ON md.merchant_id = m.id
            WHERE dt.type IN (
                {$this->type['MERCHANT_TOPUP_CREDIT']}, 
                {$this->type['SYSTEM_TRANSACTION_FEE']}
            )
        ),
        daily_report AS (
            SELECT 
                merchant_id,
                currency,
                COUNT( DISTINCT merchant_deposit_id) AS turnover,
                SUM(
                    CASE 
                        WHEN type = {$this->type['MERCHANT_TOPUP_CREDIT']} THEN amount
                    END
                ) AS credit,
                SUM(
                    CASE 
                        WHEN type = {$this->type['SYSTEM_TRANSACTION_FEE']} THEN amount
                    END
                ) AS transaction_fee
            FROM daily_merchant_deposit
            GROUP BY merchant_id, currency
        )
        SELECT * FROM daily_report
        ";

        $rows = DB::select($sql);

        foreach ($rows as $row) {
            DB::table('report_daily_merchants')->insert([
                'merchant_id' => $row->merchant_id,
                'start_at' => $start_datetime,
                'end_at' => $end_datetime,
                'turnover' => $row->turnover,
                'credit' => $row->credit,
                'transaction_fee' => $row->transaction_fee,
                'currency' => $row->currency
            ]);

            ReportMonthlyMerchant::updateOrCreate(
                [
                    'date' => Carbon::now()->startOfMonth()->toDateString('Y-m-d'),
                    'merchant_id' => $row->merchant_id,
                    'currency' => $row->currency,
                ],
                [
                    'turnover' => DB::raw('turnover + ' . $row->turnover),
                    'payin' => DB::raw('payin + ' . $row->credit),
                    'payout' => DB::raw('payout + ' . $row->transaction_fee),
                ],
            );
        }
    }
}
