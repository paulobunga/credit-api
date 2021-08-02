<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidArgumentException;
use App\Models\ReportMonthlyReseller;
use App\Models\ReportMonthlyMerchant;
use App\Models\Transaction;

class ReportDailyCommand extends Command
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
        // $this->calulateResellers($start_datetime, $end_datetime);
        $this->calulateMerchants($start_datetime, $end_datetime);
    }

    protected function calulateResellers($start_datetime, $end_datetime)
    {
        $sql = "
        WITH daily_transaction AS (        
            SELECT
                    t.id, 
                    t.user_id,
                    t.user_type, 
                    t.type,
                    t.amount,
                    t.created_at
            FROM transactions AS t
            WHERE t.created_at BETWEEN '{$start_datetime}' AND '{$end_datetime}'
        ),
        daily_merchant_deposit AS(
            SELECT 
                dt.user_id AS reseller_id,
                COUNT( DISTINCT md.id) AS turnover,
                SUM(
                    CASE WHEN dt.type = {$this->type['DEDUCT_CREDIT']} THEN dt.amount
                END
                ) AS credit,
                SUM(
                    CASE WHEN dt.type = {$this->type['COMMISSION']} THEN dt.amount
                END
                ) AS coin
            FROM daily_transaction AS dt
            JOIN model_has_transactions AS mht ON dt.id = mht.transaction_id AND mht.model_type = 'merchant.deposit'
            LEFT JOIN merchant_deposits AS md ON mht.model_id = md.id
            LEFT JOIN resellers AS r ON dt.user_id = r.id
            WHERE dt.type IN ({$this->type['DEDUCT_CREDIT']}, {$this->type['COMMISSION']})
            GROUP BY reseller_id
        ),
        daily_reseller_withdrawal AS(
            SELECT 
                rw.reseller_id,
                SUM(
                    CASE WHEN dt.type = {$this->type['DEDUCT_COIN']} THEN dt.amount
                    END
                ) AS withdrawal
            FROM daily_transaction AS dt
            JOIN model_has_transactions AS mht ON dt.id = mht.transaction_id AND mht.model_type = 'reseller.withdrawal'
            LEFT JOIN reseller_withdrawals AS rw ON mht.model_id = rw.id
            WHERE dt.type IN ({$this->type['DEDUCT_COIN']})
            GROUP BY reseller_id
          ),
        daily_report AS (
            SELECT 
                reseller_id,
                COALESCE(daily_merchant_deposit.turnover, 0) AS turnover,
                COALESCE(daily_merchant_deposit.credit, 0) AS credit,
                COALESCE(daily_merchant_deposit.coin, 0) AS coin,
                COALESCE(daily_reseller_withdrawal.withdrawal, 0) AS withdrawal
            FROM daily_merchant_deposit
            LEFT JOIN daily_reseller_withdrawal USING(reseller_id)
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

            $month_report = ReportMonthlyReseller::firstOrCreate(
                [
                    'date' => Carbon::now()->startOfMonth()->toDateString('Y-m-d'),
                    'reseller_id' => $row->reseller_id,
                ],
                [
                    'date' => Carbon::now()->startOfMonth()->toDateString('Y-m-d'),
                    'reseller_id' => $row->reseller_id,
                    'turnover' => 0,
                    'payin' => 0,
                    'payout' => 0,
                    'coin' => 0,
                ],
            );
            $month_report->increment('turnover', $row->turnover);
            $month_report->increment('payin', abs($row->credit));
            $month_report->increment('payout', abs($row->withdrawal));
            $month_report->increment('coin', $row->coin);
        }
    }

    protected function calulateMerchants($start_datetime, $end_datetime)
    {
        $sql = "
        WITH daily_transaction AS (        
            SELECT
                    t.id, 
                    t.user_id,
                    t.user_type, 
                    t.type,
                    t.amount,
                    t.created_at
            FROM transactions AS t
            WHERE t.created_at BETWEEN '{$start_datetime}' AND '{$end_datetime}'
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
            WHERE dt.type IN ({$this->type['TOPUP_CREDIT']}, {$this->type['TRANSACTION_FEE']})
        ),
        daily_report AS (
            SELECT 
                merchant_id,
                COUNT( DISTINCT merchant_deposit_id) AS turnover,
                SUM(
                    CASE WHEN type = {$this->type['TOPUP_CREDIT']} THEN amount
                    END
                ) AS credit,
                SUM(
                    CASE WHEN type = {$this->type['TRANSACTION_FEE']} THEN amount
                    END
                ) AS transaction_fee
            FROM daily_merchant_deposit
            GROUP BY merchant_id
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
            ]);
            $month_report = ReportMonthlyMerchant::firstOrCreate(
                [
                    'date' => Carbon::now()->startOfMonth()->toDateString('Y-m-d'),
                    'merchant_id' => $row->merchant_id,
                ],
                [
                    'date' => Carbon::now()->startOfMonth()->toDateString('Y-m-d'),
                    'merchant_id' => $row->merchant_id,
                    'turnover' => 0,
                    'payin' => 0,
                    'payout' => 0,
                    'coin' => 0,
                ],
            );
            $month_report->increment('turnover', $row->turnover);
            $month_report->increment('payin', $row->credit);
            $month_report->increment('payout', abs($row->transaction_fee));
        }
    }
}
