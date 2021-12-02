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
        Reseller::where('online', 1)->update(['online' => '0']);
        $pusher = new \Pusher\Pusher(
          env('PUSHER_APP_KEY'),
          env('PUSHER_APP_SECRET'),
          env('PUSHER_APP_ID'),
          ['cluster' => env('PUSHER_APP_CLUSTER')]
        );
        $channels = $pusher->getChannels()->channels;
        if(!empty($channels)){
          foreach ($channels as $channel => $v) {
            if(strpos($channel, 'Reseller') !== false) {
              $arr = explode('.', $channel);
              Reseller::where('id', end($arr))->update(['online' => '1']);
            }
          }
        }
    }
}
