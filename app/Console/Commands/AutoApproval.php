<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\PaymentChannel;
use App\Models\MerchantWithdrawal;

class AutoApproval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:approval';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto approval payout orders';

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

        foreach (PaymentChannel::where('payout->auto_approval', true)->get() as $p) {
            $orders = MerchantWithdrawal::where('status', MerchantWithdrawal::STATUS['FINISHED'])
                ->where('payment_channel_id', $p->id)
                ->get();
            foreach ($orders as $o) {
                $o->update([
                    'status' => MerchantWithdrawal::STATUS['APPROVED'],
                    'extra' => [
                        'memo' => 'auto approval'
                    ]
                ]);
            }
        }
    }
}
