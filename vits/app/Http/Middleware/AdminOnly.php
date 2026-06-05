<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            $target = auth()->check() && auth()->user()->client_id
                ? route('portail.index')
                : route('dashboard');
            return redirect($target)->with('error', 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
