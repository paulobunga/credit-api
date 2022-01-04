<?php

namespace App\Http\Controllers\Reseller;

use App\Filters\DateFilter;
use Dingo\Api\Http\Request;
use App\Models\MerchantDeposit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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

        if ($request->has('filter') && !empty($request->filter['date_between'])) {
            $date_range = explode(',', $request->filter['date_between']);
            $start_date = trim($date_range[0]);
            $end_date = trim($date_range[1]);

        } else {
            $start_date = Carbon::now()->startOfDay();
            $end_date = Carbon::now()->endOfDay();
        }
        
        $sql = "
            SELECT 
                pc.name AS channel,
                md.reseller_bank_card_id,
                pc.attributes AS 'attributes_keys',
                rbc.attributes,
                COALESCE(COUNT(md.id), 0) AS cash_in_count,
                COALESCE(SUM(md.amount), 0) AS cash_in_amount,
                COALESCE(SUM(t.amount), 0) AS cash_in_commission
            FROM
                reseller_bank_cards AS rbc
                    JOIN
                payment_channels AS pc ON pc.id = rbc.payment_channel_id
                    JOIN
                merchant_deposits AS md ON rbc.id = md.reseller_bank_card_id
                    AND md.status = :md_status
                    AND CONVERT_TZ(
                        md.created_at,
                        \"{$timezone}\",
                        \"{$user_timezone}\"
                    ) BETWEEN \"{$start_date}\" AND \"{$end_date}\"
                    INNER JOIN
                model_has_transactions AS mht ON md.id = mht.model_id AND mht.model_type = :tx_model
                    JOIN
                transactions AS t ON t.id = mht.transaction_id 
                    AND t.user_type = :tx_user_type 
                    AND t.user_id = rbc.reseller_id 
                    AND t.Type = :tx_type
            WHERE
                EXISTS( SELECT 
                        *
                    FROM
                        resellers AS r
                    WHERE
                        rbc.reseller_id = r.id AND r.id = :r_id)
            GROUP BY
                channel,
                reseller_bank_card_id,
                attributes_keys,
                attributes
        ";
        
        $summary = DB::select($sql, [
            "md_status" => MerchantDeposit::STATUS['APPROVED'],
            "tx_type" => Transaction::TYPE["SYSTEM_TOPUP_COMMISSION"],
            "r_id" => auth()->id(),
            "tx_model" => "merchant.deposit",
            "tx_user_type" => "reseller"
        ]);

        return response()->json([
            "message" => "success",
            "data" => $summary
        ]);
    }
}
