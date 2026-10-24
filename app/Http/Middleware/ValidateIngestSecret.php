<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Authenticates server-to-server ingest requests via a shared secret header. */
class ValidateIngestSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('dashboard.ingest.secret');

        if (empty($secret) || $request->header('X-Ingest-Secret') !== $secret) {
            abort(401, 'Invalid or missing ingest secret.');
        }

        return $next($request);
    }
}
