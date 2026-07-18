<?php

namespace Modules\Identity\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CentralRoute
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
