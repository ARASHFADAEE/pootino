<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdentityVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isIdentityVerified()) {
            return $next($request);
        }

        if ($user) {
            return redirect()->guest(route('auth.otp.verification-required'));
        }

        return redirect()->guest(route('auth.otp.phone'));
    }
}
