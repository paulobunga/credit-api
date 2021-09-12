<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Maatwebsite\Excel\Facades\Excel;

class ImportFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:file 
                            {method}
                            {filename} 
                            {args?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import file to database';

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
        $filename = $this->argument('filename');
        $args = $this->argument('args');
        try {
            $this->$method($filename, ...$args);
            $this->info('success');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Import file to bank table
     *
     */
    protected function banks($filename)
    {
        Excel::import(new \App\Imports\BankImport, $filename);
    }
}
