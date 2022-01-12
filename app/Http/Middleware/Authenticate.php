<?php

namespace App\Http\Middleware;

use Closure;
use Dingo\Api\Routing\Router;
use Dingo\Api\Auth\Auth as Authentication;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Authenticate
{
    /**
     * Router instance.
     *
     * @var \Dingo\Api\Routing\Router
     */
    protected $router;

    /**
     * Authenticator instance.
     *
     * @var \Dingo\Api\Auth\Auth
     */
    protected $auth;

    /**
     * Create a new auth middleware instance.
     *
     * @param \Dingo\Api\Routing\Router $router
     * @param \Dingo\Api\Auth\Auth      $auth
     *
     * @return void
     */
    public function __construct(Router $router, Authentication $auth)
    {
        $this->router = $router;
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($guard != null) {
            app('auth')->shouldUse($guard);
        }

        $route = $this->router->getCurrentRoute();

        if (!$this->auth->check(false)) {
            $this->auth->authenticate($route->getAuthenticationProviders());
        }        

        if ($this->checkAccountStatus($guard)) {
            auth($guard)->logout();
            throw new UnauthorizedHttpException('AccountStatus', 'Unauthorized: Account Disable');
        }

        return $next($request);
    }
    
    /**
     * Check if Account Status has been disabled.
     *
     * @param  mixed $guard
     * @return boolean
     */
    protected function checkAccountStatus ($guard) {
        switch ($guard) {
            case 'reseller':
                return (auth('reseller')->user()->status === \App\Models\Reseller::STATUS['DISABLED']);
            case 'admin':
                return (auth('admin')->user()->status === \App\Models\Admin::STATUS['DISABLED']);
            case 'merchant':
                return (auth('merchant')->user()->status === \App\Models\Merchant::STATUS['DISABLED']);
            default:
                return false;
        }
    }
}
