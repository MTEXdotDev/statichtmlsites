<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Placeholder middleware required by bootstrap/app.php.
 * Add theme / appearance handling here (e.g. dark-mode cookie).
 */
class HandleAppearance
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
