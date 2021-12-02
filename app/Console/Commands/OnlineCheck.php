<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reseller;

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
    protected $description = 'Check Reseller and Merchant is online';

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
        $reseller_ids = [];
        foreach ($channels as $channel => $_) {
            if (preg_match('/private-App.Models.Reseller.(\d+)/', $channel, $id)) {
                $reseller_ids[] = $id[1];
            }
        }
        Reseller::whereIn('id', $reseller_ids)->update(['online' => 1]);
        Reseller::whereNotIn('id', $reseller_ids)->update(['online' => 0]);
    }
}
