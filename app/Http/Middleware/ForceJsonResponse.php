<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle($request, Closure $next)
{
    $response = $next($request);
    $response->header('Content-Type', 'application/json');
    return $response;
}
}
