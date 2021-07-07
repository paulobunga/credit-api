<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidArgumentException;

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
        $sql = "
        WITH daily_transaction AS (        
            SELECT
                    t.id, 
                    m.name,
                    t.amount,
                    t.created_at
            FROM transactions AS t
            LEFT JOIN transaction_methods AS m ON m.id =  t.transaction_method_id
            WHERE t.created_at BETWEEN '{$start_datetime}' AND '{$end_datetime}'
        ),
        daily_merchant_deposit AS(
          SELECT 
                dt.*,
                md.id AS merchant_deposit_id,
                md.merchant_order_id,
                md.reseller_bank_card_id,
                rbc.account_name,
                rbc.account_no,
                rbc.reseller_id
            FROM daily_transaction AS dt
            JOIN model_has_transactions AS mht ON dt.id = mht.transaction_id AND mht.model_type = 'App\\\\Models\\\\MerchantDeposit'
            LEFT JOIN merchant_deposits AS md ON mht.model_id = md.id
            LEFT JOIN reseller_bank_cards AS rbc ON md.reseller_bank_card_id = rbc.id
            LEFT JOIN resellers AS r ON rbc.reseller_id = r.id
            WHERE dt.name IN ('DEDUCT_CREDIT', 'TOPUP_COIN')
        ),
        daily_report AS (
            SELECT 
                reseller_id,
                COUNT( DISTINCT merchant_deposit_id) AS turnover,
                SUM(
                    CASE WHEN name = 'DEDUCT_CREDIT' THEN -amount
                    END
                ) AS credit,
                SUM(
                    CASE WHEN name = 'TOPUP_COIN' THEN amount
                    END
                ) AS coin
            FROM daily_merchant_deposit
            GROUP BY reseller_id
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
        }
    }

    protected function calulateMerchants($start_datetime, $end_datetime)
    {
        $sql = "
        WITH daily_transaction AS (        
            SELECT
                    t.id, 
                    m.name,
                    t.amount,
                    t.created_at
            FROM transactions AS t
            LEFT JOIN transaction_methods AS m ON m.id =  t.transaction_method_id
            WHERE t.created_at BETWEEN '{$start_datetime}' AND '{$end_datetime}'
        ),
        daily_merchant_deposit AS(
          SELECT 
                dt.*,
                md.id AS merchant_deposit_id,
								md.merchant_id,
                md.merchant_order_id
            FROM daily_transaction AS dt
            JOIN model_has_transactions AS mht ON dt.id = mht.transaction_id AND mht.model_type = 'App\\\\Models\\\\MerchantDeposit'
            LEFT JOIN merchant_deposits AS md ON mht.model_id = md.id
            LEFT JOIN merchants AS m ON md.merchant_id = m.id
            WHERE dt.name IN ('TOPUP_CREDIT', 'TRANSACTION_FEE')
        ),
        daily_report AS (
            SELECT 
                merchant_id,
                COUNT( DISTINCT merchant_deposit_id) AS turnover,
                SUM(
                    CASE WHEN name = 'TOPUP_CREDIT' THEN amount
                    END
                ) AS credit,
                SUM(
                    CASE WHEN name = 'TRANSACTION_FEE' THEN -amount
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
        }
    }
}
