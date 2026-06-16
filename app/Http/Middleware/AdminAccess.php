<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $phones = collect(explode(',', (string) env('ADMIN_PHONES', '')))
            ->map(fn ($phone) => trim($phone))
            ->filter()
            ->values();

        if (! $request->user() || ! $phones->contains($request->user()->phone)) {
            abort(403);
        }

        return $next($request);
    }
}
