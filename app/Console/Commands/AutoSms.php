<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ResellerSms;
use App\Models\PaymentChannel;
use App\Models\MerchantDeposit;

class AutoSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto approval payin orders via sms';

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
        $sms = ResellerSms::with('reseller')->where('status', ResellerSms::STATUS['PENDING'])->get();
        $deposits = MerchantDeposit::with('reseller')->whereIn('status', [
            MerchantDeposit::STATUS['PENDING'],
            MerchantDeposit::STATUS['EXPIRED'],
        ])->orderByDesc('id')->get();
        $channels = PaymentChannel::all();
        foreach ($sms as $s) {
            $filtered_channels = $channels->filter(function ($m) use ($s) {
                return $m->currency == $s->reseller->currency;
            });
            $filtered_deposits = $deposits->filter(function ($m) use ($s) {
                return $m->reseller->id == $s->reseller_id;
            });
            $match_deposits = [];
            foreach ($filtered_channels as $ch) {
                $data = $ch->extractSMS($s->body);
                if (empty($data)) {
                    continue;
                }
                if (!$data['reference_id']) {
                    continue;
                }
                $this->info($ch->name . ' ' . json_encode($data));
                foreach ($filtered_deposits as $d) {
                    if ($data['amount'] == $d->amount) {
                        $match_deposits[] = [
                            'deposit' => $d,
                            'data' => $data
                        ];
                    }
                }
            }
            if (count($match_deposits) == 1) {
                $deposit = $match_deposits[0]['deposit'];
                $data = $match_deposits[0]['data'];
                $deposit->update([
                    'status' => MerchantDeposit::STATUS['APPROVED'],
                    'extra' => $deposit->extra + ['reference_id' => $data['reference_id']]
                ]);
                $s->update([
                    'model_id' => $deposit->id,
                    'model_name' => 'merchant.deposit',
                    'status' => ResellerSms::STATUS['MATCH']
                ]);
            } else {
                $s->update([
                    'status' => ResellerSms::STATUS['UNMATCH']
                ]);
            }
        }
    }
}
