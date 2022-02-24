<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Online;
use App\Models\Reseller;
use App\Notifications\Admin\PayInOutOff;

class CheckOnline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:online';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check agent online status';

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
        $channels = app('pusher')->getChannels()->channels;
        $reseller_ids = [
            'online' => [],
            'offline' => [],
        ];
        $resellers = Reseller::with('online')->get();
        foreach ($resellers as $r) {
            $online = isset($channels["private-App.Models.Reseller.{$r->id}"]);
            if ($online) {
                $reseller_ids['online'][] = $r->id;
            } elseif ($r->online->status) {
                $reseller_ids['offline'][] = $r->id;
            }
        }
        // massive update
        Online::where('user_type', 'reseller')
            ->whereIn('user_id', $reseller_ids['online'])
            ->update([
                'status' => 1,
                'last_seen_at' => Carbon::now()
            ]);
        Online::where('user_type', 'reseller')
            ->whereIn('user_id', $reseller_ids['offline'])
            ->update(['status' => 0]);

        $agents = Reseller::where([
            'level' => Reseller::LEVEL['AGENT'],
        ])
            ->where(function ($query) {
                $query->where('payin->status', true)
                    ->orWhere('payout->status', true);
            })
            ->whereIn('currency', ['BDT', 'INR'])
            ->whereIn('id', $reseller_ids['offline'])->get();

        foreach ($agents as $agent) {
            $agent->payin->status = false;
            $agent->payout->status = false;
            $agent->save();
            $notification = new PayInOutOff($agent);
            broadcast(new \App\Events\AdminNotification($notification->toArray($agent), $notification));
        }

        $agents = Reseller::where([
            'level' => Reseller::LEVEL['AGENT'],
        ])
            ->where(function ($query) {
                $query->where('payin->status', false)
                    ->orWhere('payout->status', false);
            })
            ->whereIn('currency', ['BDT', 'INR'])
            ->whereIn('id', $reseller_ids['online'])->get();

        foreach ($agents as $agent) {
            $agent->payin->status = true;
            $agent->payout->status = true;
            $agent->save();
        }
    }
}
