<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidArgumentException;
use App\Models\Transaction;
use App\Models\MerchantDeposit;
use App\Models\MerchantWithdrawal;
use App\Models\ReportDailyMerchant;
use App\Models\ReportDailyReseller;

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

    protected $deposit_status = MerchantDeposit::STATUS;

    protected $withdrawal_status = MerchantWithdrawal::STATUS;
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
                model_id,
                model_type,
                user_id,
                type,
                user_type,
                SUM(amount) AS amount,
                currency
            FROM
                model_has_transactions AS mht 
            LEFT JOIN transactions AS t ON t.id = mht.transaction_id
            WHERE 
                t.user_type = 'reseller'
                AND t.created_at BETWEEN '{$start_datetime}' AND '{$end_datetime}'
            GROUP BY 
                model_id, model_type, user_type, user_id, type, currency
        ),
        cashin_commissions AS ( 
            SELECT
                user_id AS reseller_id,
                COALESCE(SUM(CASE WHEN type = {$this->type['SYSTEM_DEDUCT_CREDIT']} THEN amount END),0) AS credit,
                COALESCE(SUM(CASE WHEN type = {$this->type['SYSTEM_TOPUP_COMMISSION']} THEN amount END),0) AS coin
            FROM (
                SELECT
                    *
                FROM
                    daily_transaction
                WHERE type = {$this->type['SYSTEM_TOPUP_COMMISSION']} AND model_type = 'merchant.deposit'
                UNION ALL 
                SELECT
                    dt.model_id,
                    model_type,
                    r.reseller_id as user_id,
                    type,
                    user_type,
                    amount,
                    currency
                FROM
                    daily_transaction AS dt 
                LEFT JOIN (
                    SELECT 
                        resellers.id,
                        agent.Value AS reseller_id
                    FROM
                        resellers,
                        JSON_TABLE(
                            JSON_ARRAY_APPEND( resellers.uplines, '$', resellers.id),
                            '$[*]'
                            COLUMNS(Value INT PATH '$')
                        ) AS agent
                    ) AS r ON dt.user_id = r.id 
                    AND dt.type = {$this->type['SYSTEM_DEDUCT_CREDIT']}
                    AND model_type = 'merchant.deposit'
            ) AS temp
            GROUP BY
                user_id
        ),
        cashin_transactions AS (
            SELECT
                r.reseller_id,
                COUNT(DISTINCT md.id) AS turnover,
                SUM(md.amount) AS total,
                COUNT(DISTINCT md.player_id) AS players,
                COALESCE(SUM(CASE WHEN md.status IN (
                    {$this->deposit_status['APPROVED']},
                    {$this->deposit_status['ENFORCED']}
                ) THEN 1 END), 0) success,
                COALESCE(SUM(CASE WHEN md.status IN (
                    {$this->deposit_status['REJECTED']},
                    {$this->deposit_status['CANCELED']}
                ) THEN 1 END), 0) decline
            FROM merchant_deposits AS md
            LEFT JOIN reseller_bank_cards AS rbc ON md.reseller_bank_card_id = rbc.id
            LEFT JOIN (
                SELECT 
                    resellers.id,
                    agent.Value AS reseller_id
                FROM
                    resellers,
                    JSON_TABLE(
                        JSON_ARRAY_APPEND( resellers.uplines, '$', resellers.id),
                        '$[*]'
                        COLUMNS(Value INT PATH '$')
                    ) AS agent
            ) AS r ON rbc.reseller_id = r.id
            GROUP BY reseller_id
        ),
        daily_cashin AS(
            SELECT 
                *	 
            FROM 
                cashin_commissions LEFT JOIN cashin_transactions USING (reseller_id)
            ORDER BY reseller_id ASC
        ),
        cashout_commissions AS ( 
            SELECT
                user_id AS reseller_id,
                COALESCE(SUM(CASE WHEN type = {$this->type['SYSTEM_TOPUP_CREDIT']} THEN amount END),0) AS credit,
                COALESCE(SUM(CASE WHEN type = {$this->type['SYSTEM_TOPUP_COMMISSION']} THEN amount END),0) AS coin
                FROM (
                    SELECT
                        *
                    FROM
                        daily_transaction
                    WHERE type = {$this->type['SYSTEM_TOPUP_COMMISSION']} AND model_type = 'merchant.withdrawal'
                    UNION ALL 
                    SELECT
                        dt.model_id,
                        model_type,
                        r.reseller_id as user_id,
                        type,
                        user_type,
                        amount,
                        currency
                    FROM
                        daily_transaction AS dt 
                    LEFT JOIN (
                        SELECT 
                            resellers.id,
                            agent.Value AS reseller_id
                        FROM
                            resellers,
                            JSON_TABLE(
                                JSON_ARRAY_APPEND( resellers.uplines, '$', resellers.id),
                                '$[*]'
                                COLUMNS(Value INT PATH '$')
                            ) AS agent
                        ) AS r ON dt.user_id = r.id 
                        AND dt.type = {$this->type['SYSTEM_TOPUP_CREDIT']}
                        AND model_type = 'merchant.withdrawal'
                ) AS temp
            GROUP BY
                user_id
        ),
        cashout_transactions AS (
            SELECT
                r.reseller_id,
                COUNT(DISTINCT mw.id) AS turnover,
                SUM(mw.amount) AS total,
                COUNT(DISTINCT mw.player_id) AS players,
                COALESCE(SUM(CASE WHEN mw.status IN (
                    {$this->withdrawal_status['APPROVED']}
                ) THEN 1 END), 0) success,
                COALESCE(SUM(CASE WHEN mw.status IN (
                    {$this->withdrawal_status['REJECTED']},
                    {$this->withdrawal_status['CANCELED']}
                ) THEN 1 END), 0) decline
            FROM merchant_withdrawals AS mw
            LEFT JOIN (
                SELECT 
                    resellers.id,
                    agent.Value AS reseller_id
                FROM
                    resellers,
                    JSON_TABLE(
                        JSON_ARRAY_APPEND( resellers.uplines, '$', resellers.id),
                        '$[*]'
                        COLUMNS(Value INT PATH '$')
                    ) AS agent
            ) AS r ON mw.reseller_id = r.id
            GROUP BY reseller_id
        ),
        daily_cashout AS(
            SELECT 
                *	 
            FROM 
                cashout_commissions LEFT JOIN cashout_transactions USING (reseller_id)
            ORDER BY reseller_id ASC
        ),
        daily_reseller AS(
            SELECT 
                user_id AS reseller_id,
                SUM(
                    CASE 
                        WHEN type = {$this->type['ADMIN_WITHDRAW_CREDIT']} THEN amount
                        WHEN type = {$this->type['RESELLER_WITHDRAW_CREDIT']} THEN amount
                    END
                ) AS withdrawal_credit,
                SUM(
                    CASE 
                        WHEN type = {$this->type['ADMIN_WITHDRAW_COIN']} THEN amount
                        WHEN type = {$this->type['RESELLER_WITHDRAW_COIN']} THEN amount
                    END
                ) AS withdrawal_coin,
                SUM(
                    CASE 
                        WHEN type = {$this->type['ADMIN_TOPUP_CREDIT']} THEN amount
                        WHEN type = {$this->type['RESELLER_TOPUP_CREDIT']} THEN amount
                    END
                ) AS deposit_credit,
                SUM(
                    CASE 
                        WHEN type = {$this->type['ADMIN_TOPUP_COIN']} THEN amount
                    END
                ) AS deposit_coin
            FROM daily_transaction
            WHERE 
                type IN (
                    {$this->type['ADMIN_WITHDRAW_CREDIT']},
                    {$this->type['ADMIN_WITHDRAW_COIN']},
                    {$this->type['RESELLER_WITHDRAW_CREDIT']},
                    {$this->type['RESELLER_WITHDRAW_COIN']},
                    {$this->type['ADMIN_TOPUP_CREDIT']},
                    {$this->type['ADMIN_TOPUP_COIN']},
                    {$this->type['RESELLER_TOPUP_CREDIT']}
                )
                AND model_type IN (
                    'reseller.deposit',
                    'reseller.withdrawal'
                )
            GROUP BY reseller_id
        ),
        daily_report AS (
            SELECT 
                resellers.id AS reseller_id,
                JSON_OBJECT(
                    'turnover', COALESCE(daily_cashin.turnover, 0),
                    'total', COALESCE(daily_cashin.total, 0),
                    'credit', COALESCE(daily_cashin.credit, 0),
                    'coin', COALESCE(daily_cashin.coin, 0),
                    'players', COALESCE(daily_cashin.players, 0),
                    'success', COALESCE(daily_cashin.success, 0),
                    'decline', COALESCE(daily_cashin.decline, 0)
                ) AS cashin,
                JSON_OBJECT(
                    'turnover', COALESCE(daily_cashout.turnover, 0),
                    'total', COALESCE(daily_cashout.total, 0),
                    'credit', COALESCE(daily_cashout.credit, 0),
                    'coin', COALESCE(daily_cashout.coin, 0),
                    'players', COALESCE(daily_cashout.players, 0),
                    'success', COALESCE(daily_cashout.success, 0),
                    'decline', COALESCE(daily_cashout.decline, 0)
                ) AS cashout,
                COALESCE(daily_reseller.withdrawal_credit, 0) AS withdrawal_credit,
                COALESCE(daily_reseller.withdrawal_coin, 0) AS withdrawal_coin,
                COALESCE(daily_reseller.deposit_credit, 0) AS deposit_credit,
                COALESCE(daily_reseller.deposit_coin, 0) AS deposit_coin
            FROM resellers
            LEFT JOIN daily_cashin ON resellers.id = daily_cashin.reseller_id
            LEFT JOIN daily_cashout ON resellers.id = daily_cashout.reseller_id
            LEFT JOIN daily_reseller ON resellers.id = daily_reseller.reseller_id
            ORDER BY resellers.id ASC
        )
        SELECT * FROM daily_report
        ";

        $rows = DB::select($sql);
        // dd($rows);
        foreach ($rows as $row) {
            $row->cashin = json_decode($row->cashin);
            $row->cashout = json_decode($row->cashout);
            ReportDailyReseller::updateOrCreate([
                'reseller_id' => $row->reseller_id,
                'start_at' => $start_datetime,
                'end_at' => $end_datetime,
            ], [
                'turnover' => $row->cashin->success + $row->cashout->success,
                'credit' => $row->cashin->credit + $row->cashout->credit,
                'coin' => $row->cashin->coin + $row->cashout->coin,
                'extra' => [
                    'cashin' => $row->cashin,
                    'cashout' => $row->cashout,
                    'withdrawal_credit' => $row->withdrawal_credit,
                    'withdrawal_coin' => $row->withdrawal_coin,
                    'deposit_credit' => $row->deposit_credit,
                    'deposit_coin' => $row->deposit_coin,
                ]
            ]);
        }
    }

    protected function calulateMerchants($start_datetime, $end_datetime)
    {
        $sql = "
        WITH daily_transaction AS (        
            SELECT
                model_id,
                model_type,
                user_id,
                type,
                user_type,
                SUM(amount) AS amount,
                currency
            FROM
                model_has_transactions AS mht 
            LEFT JOIN transactions AS t ON t.id = mht.transaction_id
            WHERE 
                t.user_type = 'merchant'
                AND t.created_at BETWEEN '{$start_datetime}' AND '{$end_datetime}'
            GROUP BY 
                model_id, model_type, user_type, user_id, type, currency
        ),
        payin_transactions AS (
            SELECT 
                model_id,
                SUM(CASE WHEN type = {$this->type['MERCHANT_TOPUP_CREDIT']} THEN amount END) AS credit,
                SUM(CASE WHEN type = {$this->type['SYSTEM_TRANSACTION_FEE']} THEN amount END) AS transaction_fee
            FROM daily_transaction
            WHERE model_type = 'merchant.deposit'
            GROUP BY model_id
        ),
        daily_payin AS(
            SELECT 
                md.merchant_id,
                COUNT(DISTINCT md.id) AS turnover,
                COALESCE(SUM(CASE WHEN md.status in (
                    {$this->deposit_status['APPROVED']},
                    {$this->deposit_status['ENFORCED']}
                ) THEN 1 END),0) AS success,
                COALESCE(SUM(CASE WHEN md.status in (
                    {$this->deposit_status['REJECTED']},
                    {$this->deposit_status['CANCELED']}
                ) THEN 1 END),0) AS decline,
                SUM(ct.credit) AS credit,
                SUM(ct.transaction_fee) AS transaction_fee,
                SUM(md.amount) AS total,
                COUNT(DISTINCT md.player_id) AS players,
                md.currency
            FROM merchant_deposits AS md
            LEFT JOIN payin_transactions AS ct ON md.id = ct.model_id 
            GROUP BY merchant_id, currency
        ),
        payout_transactions AS (
            SELECT 
                model_id,
                SUM(
                    CASE WHEN type = {$this->type['MERCHANT_WITHDRAW_CREDIT']} THEN amount 
                    WHEN type = {$this->type['SYSTEM_TOPUP_CREDIT']}  THEN -amount 
                END) AS credit,
                SUM(
                    CASE WHEN type = {$this->type['SYSTEM_TRANSACTION_FEE']} THEN amount 
                    WHEN type = {$this->type['ROLLBACK_TRANSACTION_FEE']} THEN -amount END
                ) AS transaction_fee
            FROM daily_transaction
            WHERE model_type = 'merchant.withdrawal'
            GROUP BY model_id
        ),
        daily_payout AS(
            SELECT 
                mw.merchant_id,
                COUNT(DISTINCT mw.id) AS turnover,
                COALESCE(SUM(CASE WHEN mw.status in ({$this->withdrawal_status['APPROVED']}) THEN 1 END),0) AS success,
                COALESCE(SUM(CASE WHEN mw.status in (
                    {$this->withdrawal_status['REJECTED']},
                    {$this->withdrawal_status['CANCELED']}
                ) THEN 1 END),0) AS decline,
                SUM(CASE WHEN mw.status IN (
                    {$this->withdrawal_status['REJECTED']},
                    {$this->withdrawal_status['APPROVED']},
                    {$this->withdrawal_status['CANCELED']}
                ) THEN ct.credit END) AS credit,
                SUM(CASE WHEN mw.status IN (
                    {$this->withdrawal_status['REJECTED']},
                    {$this->withdrawal_status['APPROVED']},
                    {$this->withdrawal_status['CANCELED']}
                ) THEN ct.transaction_fee END) AS transaction_fee,
                SUM(mw.amount) AS total,
                COUNT(DISTINCT mw.player_id) AS players,
                mw.currency
            FROM merchant_withdrawals AS mw
            LEFT JOIN payout_transactions AS ct ON mw.id = ct.model_id 
            GROUP BY merchant_id, currency
        ),
        daily_merchant AS(
            SELECT 
                user_id AS merchant_id,
                SUM(
                    CASE 
                        WHEN type = {$this->type['MERCHANT_SETTLE_CREDIT']} THEN amount
                    END
                ) AS withdrawal_credit,
                currency
            FROM daily_transaction
            WHERE 
                type IN (
                    {$this->type['MERCHANT_SETTLE_CREDIT']}
                )
                AND model_type IN (
                    'merchant.settlement'
                )
            GROUP BY merchant_id, currency
        ),
        daily_report AS (
            SELECT 
                m.id AS merchant_id,
                mc.currency,
                JSON_OBJECT(
                    'turnover', COALESCE(daily_payin.turnover, 0),
                    'total', COALESCE(daily_payin.total, 0),
                    'credit', COALESCE(daily_payin.credit, 0),
                    'transaction_fee', COALESCE(daily_payin.transaction_fee, 0),
                    'players', COALESCE(daily_payin.players, 0),
                    'success', COALESCE(daily_payin.success, 0),
                    'decline', COALESCE(daily_payin.decline, 0)
                ) AS payin,
                JSON_OBJECT(
                    'turnover', COALESCE(daily_payout.turnover, 0),
                    'total', COALESCE(daily_payout.total, 0),
                    'credit', COALESCE(daily_payout.credit, 0),
                    'transaction_fee', COALESCE(daily_payout.transaction_fee, 0),
                    'players', COALESCE(daily_payout.players, 0),
                    'success', COALESCE(daily_payout.success, 0),
                    'decline', COALESCE(daily_payout.decline, 0)
                ) AS payout,
                COALESCE(dm.withdrawal_credit, 0) AS withdrawal_credit
            FROM merchants AS m
            LEFT JOIN merchant_credits AS mc ON m.id = mc.merchant_id
            LEFT JOIN daily_payin ON mc.merchant_id = daily_payin.merchant_id 
            AND mc.currency = daily_payin.currency
            LEFT JOIN daily_payout ON mc.merchant_id = daily_payout.merchant_id 
            AND mc.currency = daily_payout.currency
            LEFT JOIN daily_merchant AS dm ON mc.merchant_id = dm.merchant_id
            AND mc.currency = dm.currency
            ORDER BY merchant_id, currency
        )
        SELECT * FROM daily_report
        ";

        $rows = DB::select($sql);
        // dd($rows);
        foreach ($rows as $row) {
            $row->payin = json_decode($row->payin);
            $row->payout = json_decode($row->payout);
            ReportDailyMerchant::updateOrCreate([
                'merchant_id' => $row->merchant_id,
                'currency' => $row->currency,
                'start_at' => $start_datetime,
                'end_at' => $end_datetime,
            ], [
                'turnover' => $row->payin->turnover + $row->payout->turnover,
                'credit' => $row->payin->credit + $row->payout->credit,
                'transaction_fee' => $row->payin->transaction_fee + $row->payout->transaction_fee,
                'extra' => [
                    'payin' => $row->payin,
                    'payout' => $row->payout,
                    'settlement' => $row->withdrawal_credit,
                ]
            ]);
        }
    }
}
