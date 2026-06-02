<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTheme
{
    public function handle(Request $request, Closure $next, string $theme): mixed
    {
        if (!auth()->check() || !auth()->user()->hasTheme($theme)) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        return $next($request);
    }
}
