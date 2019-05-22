<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Providers\Auth\Illuminate;

class Authenticate extends BaseAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @param $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard)
    {
        app()->singleton('tymon.jwt.auth', function () use ($guard) {
            /** @var GuardContract $auth */
            $auth = auth($guard);
            return new JWTAuth(app('tymon.jwt.manager'), new Illuminate($auth), app('tymon.jwt.parser'));
        });

        $this->auth = app('tymon.jwt.auth');

        $this->authenticate($request);

        return $next($request);
    }
}
