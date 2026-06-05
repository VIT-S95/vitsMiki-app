<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PortailOnly
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!auth()->check() || !auth()->user()->client_id) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
