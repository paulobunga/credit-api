<?php

namespace App\Services\MaintenanceMode\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MaintenanceMode\MaintenanceModeService;

class MaintenanceModeMiddleware
{
    /**
     * Maintenance Mode Service.
     *
     * @var App\Services\MaintenanceMode\MaintenanceModeService
     */
    protected $maintenance;

    /**
     * MaintenanceModeMiddleware constructor.
     * @param MaintenanceModeService $maintenance
     */
    public function __construct(MaintenanceModeService $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    /**
     * Handle incoming requests.
     *
     * @param Request $request
     * @param \Closure $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \InvalidArgumentException
     */
    public function handle($request, Closure $next)
    {
        if ($this->maintenance->isDownMode() && !$this->maintenance->checkAllowedIp($this->getIp())) {
            return response()->json([
                'message' => 'The application is down for maintenance.',
                'code' => 503
            ], 503);
        }

        return $next($request);
    }

    /**
     * Get client ip
     */
    private function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
