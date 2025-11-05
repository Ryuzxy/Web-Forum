<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateStatusUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, $next): Response
    {
        if (auth()->check()) {
        auth()->user()->update([
            'status' => 'online',
            'last_seen_at' => now()
        ]);
    }
        return $next($request);
    }
}
