<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Permission;

class PermissionList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create permission list';

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
        $routeCollection = app('api.router')->getRoutes()[env('API_VERSION')]->getRoutes();
        foreach ($routeCollection as $route) {
            $route = $route->getName();
            $prefix =  explode('.', $route)[0] ?? null;
            if ($prefix != 'admin') {
                continue;
            }
            $allow_permission = [
                '.auth.',
                '.notifications.'
            ];
            if (preg_match('(' . implode('|', $allow_permission) . ')', $route)) {
                continue;
            }
            Permission::firstOrCreate(['name' => $route]);
        }
    }
}
