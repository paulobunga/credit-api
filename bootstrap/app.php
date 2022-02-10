<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
 */

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
 */

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
 */

$app->configure('app');
$app->configure('api');
$app->configure('cors');
$app->configure('auth');
$app->configure('excel');
$app->configure('permission');
$app->configure('query-builder');
$app->configure('view');
$app->configure('broadcasting');
$app->configure('settings');
$app->configure('activitylog');
/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
 */

$app->middleware([
    Fruitcake\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    'domain' =>  \App\Http\Middleware\CheckDomain::class,
    'whitelist' =>  \App\Http\Middleware\WhiteList::class,
    'role_or_permission' => \App\Http\Middleware\RoleOrPermissionMiddleware::class,
    'activity_log' => \App\Http\Middleware\ActivityLog::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
 */

$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Dingo\Api\Provider\LumenServiceProvider::class);
$app->register(Spatie\Permission\PermissionServiceProvider::class);
$app->register(Spatie\Activitylog\ActivitylogServiceProvider::class);
$app->register(Spatie\QueryBuilder\QueryBuilderServiceProvider::class);
$app->register(Spatie\LaravelSettings\LaravelSettingsServiceProvider::class);
$app->register(Fruitcake\Cors\CorsServiceProvider::class);
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);
$app->register(SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);
if (class_exists(\Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class)) {
    $app->register(\Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
}
if (class_exists(\Knuckles\Scribe\ScribeServiceProvider::class)) {
    $app->register(\Knuckles\Scribe\ScribeServiceProvider::class);
    $app->configure('scribe');
}
$app->register(App\Providers\BroadcastServiceProvider::class);
$app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\RouteServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

$app->alias('cache', \Illuminate\Cache\CacheManager::class);
/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
 */

$api = app('Dingo\Api\Routing\Router');
$api->version(
    'v1',
    ['middleware' => 'api.throttle', 'limit' => 1000, 'expires' => 5],
    function (\Dingo\Api\Routing\Router $api) {
        require __DIR__ . '/../routes/api.php';
        require __DIR__ . '/../routes/admin.php';
        require __DIR__ . '/../routes/merchant.php';
        require __DIR__ . '/../routes/reseller.php';
        require __DIR__ . '/../routes/web.php';
    }
);

return $app;
