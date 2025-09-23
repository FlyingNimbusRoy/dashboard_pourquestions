<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            auth()->logout();

            return redirect()
                ->route('login')
                ->with('status', 'Account created, pending admin approval.');
        }

        return $next($request);
    }
}
