<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportDailyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily';

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
        $this->calResellers();
        $this->calMerchants();
    }

    protected function calResellers()
    {
        $sql = "
        WITH report AS (
            SELECT 
                r.name,
                d.reseller_id,
                f.type,
                f.amount
            FROM reseller_fund_records AS f
            LEFT JOIN reseller_deposits AS d ON d.id =  f.fundable_id 
            LEFT JOIN resellers AS r ON r.id =  d.reseller_id
            WHERE f.fundable_type = 'App\\\\Models\\\\ResellerDeposit'
            UNION ALL
            SELECT 
                r.name,
                d.reseller_id,
                f.type,
                f.amount
            FROM reseller_fund_records AS f
            LEFT JOIN reseller_withdrawals AS d ON d.id =  f.fundable_id 
            LEFT JOIN resellers AS r ON r.id =  d.reseller_id
            WHERE f.fundable_type = 'App\\\\Models\\\\ResellerWithdrawal'
        ),
        report_all AS (
          SELECT
                reseller_id,
                COUNT(*) AS turnover,
                SUM(CASE WHEN type=0 THEN amount ELSE 0 END) as total_top_credit,
                SUM(CASE WHEN type=1 THEN amount ELSE 0 END) as total_withdraw_credit,
                SUM(CASE WHEN type=2 THEN amount ELSE 0 END) as total_top_coin,
                SUM(CASE WHEN type=3 THEN amount ELSE 0 END) as total_deduct_coin
            FROM report 
            GROUP BY reseller_id
        )
        SELECT * FROM report_all
        ";
        $rows = DB::select($sql);
        foreach ($rows as $row) {
            DB::table('report_reseller_funds')->insert([
                'reseller_id' => $row->reseller_id,
                'start_at' => Carbon::now()->startOfDay(),
                'end_at' => Carbon::now()->endOfDay(),
                'turnover' => $row->turnover,
                'credit' => $row->total_top_credit - $row->total_withdraw_credit,
                'coin' => $row->total_top_coin - $row->total_deduct_coin,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    protected function calMerchants()
    {
        $sql = "
        WITH report AS (
            SELECT 
                r.name,
                d.merchant_id,
                f.type,
                f.amount
            FROM merchant_fund_records AS f
            LEFT JOIN merchant_deposits AS d ON d.id =  f.fundable_id 
            LEFT JOIN merchants AS r ON r.id =  d.merchant_id
            WHERE f.fundable_type = 'App\\\\Models\\\\MerchantDeposit'
            UNION ALL
            SELECT 
                r.name,
                d.merchant_id,
                f.type,
                f.amount
            FROM merchant_fund_records AS f
            LEFT JOIN merchant_withdrawals AS d ON d.id =  f.fundable_id 
            LEFT JOIN merchants AS r ON r.id =  d.merchant_id
            WHERE f.fundable_type = 'App\\\\Models\\\\MerchantWithdrawal'
        ),
        report_all AS (
          SELECT
                merchant_id,
                COUNT(*) AS turnover,
                SUM(CASE WHEN type=0 THEN amount ELSE 0 END) as total_top_credit,
                SUM(CASE WHEN type=1 THEN amount ELSE 0 END) as total_withdraw_credit,
                SUM(CASE WHEN type=2 THEN amount ELSE 0 END) as total_top_bonus,
                SUM(CASE WHEN type=3 THEN amount ELSE 0 END) as total_transaction_fee
            FROM report
            GROUP BY merchant_id
        )
        SELECT * FROM report_all
        ";
        $rows = DB::select($sql);
        foreach ($rows as $row) {
            DB::table('report_merchant_funds')->insert([
                'merchant_id' => $row->merchant_id,
                'start_at' => Carbon::now()->startOfDay(),
                'end_at' => Carbon::now()->endOfDay(),
                'turnover' => $row->turnover,
                'credit' => $row->total_top_credit - $row->total_withdraw_credit,
                'transaction_fee' => $row->total_transaction_fee,
                'info' => json_encode([
                    'bonus' => $row->total_top_bonus,
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
