<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow setup-related and internal Livewire requests to pass through
        if ($request->is('setup*') || $request->is('livewire*') || $request->is('auth/google*')) {
            return $next($request);
        }

        $userCount = \App\Models\User::count();

        if ($userCount === 0) {
            return redirect('/setup');
        }

        return $next($request);
    }
}
