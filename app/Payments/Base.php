<?php

namespace App\Payments;

abstract class Base
{
    public static function getDepositRandomSql($request)
    {
        return "WITH reseller_channels AS (
            SELECT
                r.id AS reseller_id,
                rbc.id AS reseller_bank_card_id,
                pc.NAME AS channel,
                rc.credit AS credit,
                r.currency AS currency,
                COUNT(md.id) AS pending,
                COALESCE(SUM(md.amount),0) AS pending_amount,
                SUM(
                    CASE
                        WHEN md.amount = {$request->amount} THEN 1
                        ELSE 0 
                    END 
                ) AS same_amount,
                r.payin->>'$.pending_limit' AS pending_limit 
            FROM
                reseller_bank_cards AS rbc
                LEFT JOIN resellers AS r ON rbc.reseller_id = r.id
                LEFT JOIN reseller_credits AS rc ON r.id = rc.reseller_id
                LEFT JOIN model_has_teams AS mht ON mht.model_id = r.id AND model_type = 'reseller'
                LEFT JOIN teams AS t ON mht.team_id = t.id
                LEFT JOIN payment_channels AS pc ON rbc.payment_channel_id = pc.id
                LEFT JOIN merchant_deposits AS md ON md.reseller_bank_card_id = rbc.id AND md.status <= :md_status 
            WHERE
                r.currency = '{$request->currency}'
                AND rc.credit >= {$request->amount}
                AND r.LEVEL = :r_level
                AND r.STATUS = :r_status
                AND r.payin->>'$.status' = :r_payin_status
                AND r.payin->>'$.min' <= {$request->amount}
                AND r.payin->>'$.max' >= {$request->amount}
                AND rbc.STATUS = :rbc_status
                AND pc.payin->>'$.status' = :pc_status
                AND pc.currency = '{$request->currency}'
                AND t.type = 'PAYIN'
                AND t.name = '{$request->get('class', 'Default')}'
            GROUP BY
                rbc.id,
                rc.id
            ),
            reseller_pending AS (
            SELECT
                reseller_id,
                SUM(pending_amount) AS total_pending_amount,
                SUM(pending) AS total_pending,
                SUM(same_amount) AS total_same_amount
                FROM
                    reseller_channels
                GROUP BY
                    reseller_id 
            ) 
            SELECT
                * 
            FROM
                reseller_channels
                JOIN reseller_pending USING ( reseller_id ) 
            WHERE total_pending < pending_limit
                AND total_pending_amount + {$request->amount} <= credit
                AND channel = '{$request->channel}' 
                AND total_same_amount = 0";
    }
}
