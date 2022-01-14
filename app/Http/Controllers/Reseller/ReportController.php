<?php

namespace App\Http\Controllers\Reseller;

use App\Filters\DateFilter;
use Dingo\Api\Http\Request;
use App\Models\MerchantDeposit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\MerchantWithdrawal;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\Transaction;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get lists of agent report
     *
     * @param  \Dingo\Api\Http\Request $request
     * @return json
     */
    public function index(Request $request)
    {
        $reports = QueryBuilder::for(\App\Models\ReportDailyReseller::class)
            ->allowedFilters([
                AllowedFilter::custom('date_between', new DateFilter('report_daily_resellers')),
            ])
            ->allowedSorts([
                'id',
                'start_at',
                'end_at',
                'turnover',
                'credit',
                'coin',
                'amount',
                'currency',
            ])
            ->where('reseller_id', auth()->id());

        return $this->paginate(
            $reports,
            \App\Transformers\Reseller\ReportTransformer::class
        );
    }
    
    /**
     * Get Transaction Summary of each bank cards of agent.
     *
     * @param  \Dingo\Api\Http\Request $request
     * @return json
     */
    public function summaryBankCards(Request $request)
    {
        $timezone = env('APP_TIMEZONE');
        $user_timezone = auth()->user()->timezone ?? env('APP_TIMEZONE');
        $model_deposit = "merchant.deposit";
        $model_withdraw = "merchant.withdrawal";

        if ($request->has('filter') && !empty($request->filter['date_between'])) {
            $date_range = explode(',', $request->filter['date_between']);
            $start_date = trim($date_range[0]);
            $end_date = trim($date_range[1]);

        } else {
            $start_date = Carbon::now()->startOfDay();
            $end_date = Carbon::now()->endOfDay();
        }

        $sql = "
            WITH deposit_summary AS (
                SELECT 
                    md.reseller_bank_card_id,
                    rbc.payment_channel_id,
                    md.amount,
                    mht.model_type,
                    t.amount AS commission
                FROM
                    merchant_deposits AS md
                        JOIN
                    reseller_bank_cards AS rbc ON rbc.id = md.reseller_bank_card_id
                        JOIN
                    model_has_transactions AS mht ON md.id = mht.model_id
                        AND mht.model_type = '{$model_deposit}'
                        JOIN
                    transactions AS t ON t.id = mht.transaction_id
                        AND t.type = :tx_type
                        AND t.user_type = :user_type
                        AND t.user_id = rbc.reseller_id
                WHERE
                    md.status = :md_status
                    AND rbc.reseller_id = :r_id
                    AND CONVERT_TZ(
                        md.created_at,
                        \"{$timezone}\",
                        \"{$user_timezone}\"
                        ) BETWEEN \"{$start_date}\" AND \"{$end_date}\"
            ),
            withdraw_summary AS (
                SELECT 
                    mw.reseller_bank_card_id,
                    rbc.payment_channel_id,
                    mw.amount,
                    mht.model_type,
                    t.amount AS commission
                FROM
                    merchant_withdrawals AS mw
                        JOIN
                    reseller_bank_cards AS rbc ON rbc.id = mw.reseller_bank_card_id
                        JOIN
                    model_has_transactions AS mht ON mw.id = mht.model_id
                        AND mht.model_type = '{$model_withdraw}'
                        JOIN
                    transactions AS t ON t.id = mht.transaction_id
                        AND t.type = :tx_type_2
                        AND t.user_type = :user_type_2
                        AND t.user_id = rbc.reseller_id
                WHERE
                    mw.status = :mw_status
                    AND rbc.reseller_id = :r_id_2
                    AND CONVERT_TZ(
                        mw.created_at,
                        \"{$timezone}\",
                        \"{$user_timezone}\"
                        ) BETWEEN \"{$start_date}\" AND \"{$end_date}\"
            ),
            transaction_collection AS (
                SELECT 
                    *
                FROM
                    deposit_summary 
                UNION ALL SELECT 
                    *
                FROM
                    withdraw_summary
            ),
            summary AS (
                SELECT 
                    pc.name AS channel,
                    t.reseller_bank_card_id,
                    pc.attributes AS attributes_keys,
                    rbc.attributes,
                    model_type,
                    COALESCE(COUNT(pc.name), 0) AS count,
                    COALESCE(SUM(t.amount), 0) AS amount,
                    COALESCE(SUM(t.commission), 0) AS commission
                FROM
                    transaction_collection AS t
                        JOIN
                    reseller_bank_cards AS rbc ON rbc.id = t.reseller_bank_card_id
                        JOIN
                    payment_channels AS pc ON pc.id = t.payment_channel_id
                GROUP BY
                    channel,
                    reseller_bank_card_id,
                    attributes_keys,
                    attributes,
                    model_type
            )
            SELECT 
                channel,
                reseller_bank_card_id,
                attributes_keys,
                attributes,
                JSON_OBJECT(
                    'count',
                    COALESCE(SUM(
                        CASE WHEN model_type = '{$model_deposit}' THEN count END
                    ), 0),
                    'amount', 
                    COALESCE(SUM(
                        CASE WHEN model_type = '{$model_deposit}' THEN amount END
                    ), 0),
                    'commission',
                    COALESCE(SUM(
                        CASE WHEN model_type = '{$model_deposit}' THEN commission END
                    ), 0)
                ) AS cash_in,
                JSON_OBJECT(
                    'count',
                    COALESCE(SUM(
                        CASE WHEN model_type = '{$model_withdraw}' THEN count END
                    ), 0),
                    'amount', 
                    COALESCE(SUM(
                        CASE WHEN model_type = '{$model_withdraw}' THEN amount END
                    ), 0),
                    'commission',
                    COALESCE(SUM(
                        CASE WHEN model_type = '{$model_withdraw}' THEN commission END
                    ), 0)
                ) AS cash_out
            FROM
                summary
            GROUP BY
                channel,
                reseller_bank_card_id,
                attributes_keys,
                attributes
        ";
        
        $summary = DB::select($sql, [
            ":tx_type" => Transaction::TYPE["SYSTEM_TOPUP_COMMISSION"],
            ":tx_type_2" => Transaction::TYPE["SYSTEM_TOPUP_COMMISSION"],
            ":user_type" => "reseller",
            ":user_type_2" => "reseller",
            ":r_id" => auth()->id(),
            ":r_id_2" => auth()->id(),
            ":md_status" => MerchantDeposit::STATUS['APPROVED'],
            ":mw_status" => MerchantWithdrawal::STATUS['APPROVED']
        ]);

        return response()->json([
            "message" => "success",
            "data" => $summary
        ]);
    }
}
