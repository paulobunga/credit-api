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
        $reseller = \App\Models\Reseller::create([
            'level' => Reseller::LEVEL['AGENT_MASTER'],
            'name' => 'Test Master Agent',
            'username' => 'master@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'credit' => 0,
            'pending_limit' => 0,
            'commission_percentage' => $cs->master_agent_percentage,
            'downline_slot' => 1,
            'status' => true,
        ]);
        \App\Models\Reseller::create([
            'upline_id' => $reseller->id,
            'level' => Reseller::LEVEL['RESELLER'],
            'name' => 'Test Reseller',
            'username' => 'reseller@gmail.com',
            'password' => 'P@ssw0rd',
            'phone' => '+8865721455',
            'credit' => 20000,
            'pending_limit' => $rs->default_pending_limit,
            'commission_percentage' => $cs->reseller_percentage,
            'downline_slot' => 0,
            'status' => true,
        ]);
        foreach (Reseller::LEVEL as $level) {
            $reseller = Reseller::factory()->create([
                'username' => "reseller{$level}@gmail.com",
                'upline_id' => $reseller ? $reseller->id : 0,
                'level' => $level,
                'credit' => $level < Reseller::LEVEL['RESELLER'] ? 0 : 20000,
                'pending_limit' => $rs->getDefaultPendingLimit($level),
                'commission_percentage' => $cs->getDefaultPercentage($level),
                'downline_slot' => $as->getDefaultDownLineSlot($level),
                'status' => $level ==  Reseller::LEVEL['RESELLER'] ?
                    Reseller::STATUS['INACTIVE'] :
                    Reseller::STATUS['ACTIVE']
            ]);
        }
    }
}
