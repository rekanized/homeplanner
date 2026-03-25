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
        $userCount = \App\Models\User::count();

        // If users already exist, don't allow access to setup routes
        if ($userCount > 0 && $request->is('setup*')) {
            return redirect('/');
        }

        // If no users exist, force redirect to setup (except for setup routes and essential paths)
        if ($userCount === 0 && !$request->is('setup*') && !$request->is('livewire*') && !$request->is('auth/google*')) {
            return redirect('/setup');
        }

        return $next($request);
    }
}
