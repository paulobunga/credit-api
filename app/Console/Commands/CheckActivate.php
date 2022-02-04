<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\ResellerActivateCode;

class CheckActivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expiration of activate code';

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
        ResellerActivateCode::where('status', ResellerActivateCode::STATUS['PENDING'])
            ->where('expired_at', '<=', Carbon::now())->update([
                'status' => ResellerActivateCode::STATUS['EXPIRED']
            ]);
    }
}
