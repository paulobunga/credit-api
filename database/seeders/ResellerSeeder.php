<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reseller;
use App\Settings\AgentSetting;
use App\Settings\CommissionSetting;
use App\Settings\ResellerSetting;

class ResellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(
        ResellerSetting $rs,
        CommissionSetting $cs,
        AgentSetting $as
    ) {
        \App\Models\Reseller::create([
            'level' => Reseller::LEVEL['reseller'],
            'name' => 'Test Reseller',
            'username' => 'reseller@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'credit' => 2000000,
            'coin' => 0,
            'pending_limit' => $rs->default_pending_limit,
            'commission_percentage' => $cs->reseller_percentage,
            'downline_slot' => 0,
            'status' => true,
        ]);
        $reseller = null;
        foreach (Reseller::LEVEL as $level) {
            $reseller = Reseller::factory()->create([
                'username' => "reseller{$level}@gmail.com",
                'upline_id' => $reseller ? $reseller->id : 0,
                'level' => $level,
                'pending_limit' => $rs->getDefaultPendingLimit($level),
                'commission_percentage' => $cs->getDefaultPercentage($level),
                'downline_slot' => $as->getDefaultDownLineSlot($level),
            ]);
        }
    }
}
