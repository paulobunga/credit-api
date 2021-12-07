<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Online;
use App\Models\Reseller;
use Carbon\Carbon;

class OnlineCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'online:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Reseller is online';

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
    }
}
