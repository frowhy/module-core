<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\Core\Supports\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckScopes
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param array                    $scopes
     *
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
