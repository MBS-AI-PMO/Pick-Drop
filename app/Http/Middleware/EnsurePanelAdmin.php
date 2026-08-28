<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isPanelAdmin()) {
            abort(403, 'Only Admin can manage institutions.');
        }

        return $next($request);
    }
}
