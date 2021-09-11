<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ExecuteSql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exec:sql {method} {args?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'execute SQL method';

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
        $method =  Str::camel($this->argument('method'));
        $args = $this->argument('args');
        try {
            $this->$method(...$args);
            $this->info('success');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function withdrawalAddResellerBankCardId()
    {
        if (Schema::hasColumn('reseller_withdrawals', 'reseller_bank_card_id')) {
            throw new \Exception('column exist!');
        }
        DB::statement('TRUNCATE TABLE reseller_withdrawals');
        Schema::table('reseller_withdrawals', function (Blueprint $table) {
            $table->foreignId('reseller_bank_card_id')->after('reseller_id')->constrained();
        });
    }

    protected function withdrawalAddResellerBankCard()
    {
        if (Schema::hasColumn('reseller_withdrawals', 'reseller_bank_card_id')) {
            throw new \Exception('column exist!');
        }
        DB::statement('TRUNCATE TABLE reseller_withdrawals');
        Schema::table('reseller_withdrawals', function (Blueprint $table) {
            $table->foreignId('reseller_bank_card_id')->after('reseller_id')->constrained();
        });
    }
}