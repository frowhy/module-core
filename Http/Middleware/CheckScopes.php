<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Tymon\JWTAuth\Facades\JWTAuth;
use Modules\Core\Supports\Response;

class CheckScopes
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param array $scopes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $canScopes = data_get(JWTAuth::user()->getJWTCustomClaims(), 'scopes');

        foreach ($scopes as $scope) {
            if (!Arr::has($canScopes, $scope)) {
                return Response::handleForbidden();
            }
        }

        return $next($request);
    }
}
