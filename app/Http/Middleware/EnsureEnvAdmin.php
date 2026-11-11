<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEnvAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin/login') || $request->is('admin/login/*')) {
            return $next($request);
        }

        if (! $request->session()->get('env_admin')) {
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
