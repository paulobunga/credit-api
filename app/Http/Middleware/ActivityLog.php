<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class ActivityLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return $next($request);
            case 'POST':
                $event = 'created';
                break;
            case 'PUT':
            case 'PATCH':
                $event = 'updated';
                $model_id = Arr::first($request->route()[2]);
                $model = routeModel($model_id);
                break;
            case 'DELETE':
                $event = 'deleted';
                $model_id = Arr::first($request->route()[2]);
                $model = routeModel($model_id);
                break;
            default:
                return $next($request);
        }
        // dd($request->route()[2]);
        $user = auth($guard)->user();
        $response = $next($request);

        if ($guard == null) {
            return $response;
        }

        if ($response->getStatusCode() != 200) {
            return $response;
        }

        $content = json_decode($response->getContent(), true);

        if ($request->getMethod() == 'POST') {
            $model = routeModel($content['id'] ?? null);
        }

        $route_name = request()->route()[1]['as'];
        $route_arr = explode(".", $route_name);
        $description = "";

        switch ($route_arr[2]) {
            case 'update':
                $description =  "update " . str_replace("_", " ", $route_arr[1]);
                break;
            case 'store':
                $description =  "create " . str_replace("_", " ", $route_arr[1]);
                break;
            case 'destroy':
                $description =  "delete " . str_replace("_", " ", $route_arr[1]);
                break;
            default:
                $description = str_replace("_", " ", $route_arr[2]) . " " . $route_arr[1];
        }

        $log = activity($request->route()[1]['as'])
            ->by(auth($guard)->user() ?? $user)
            ->withProperties([
                'request' => [
                    'url' => $request->path(),
                    'data' => $request->except([
                        'password',
                        'password_confirmation'
                    ])
                ],
            ])
            ->event($event);
        if ($model) {
            $log->on($model);
        }
        $log->log($description);

        return $response;
    }
}
