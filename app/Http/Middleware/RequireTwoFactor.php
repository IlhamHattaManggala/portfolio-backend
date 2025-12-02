<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Fortify\Features;

class RequireTwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if 2FA feature is enabled
        if (!Features::enabled(Features::twoFactorAuthentication())) {
            return $next($request);
        }

        // Check if user has enabled 2FA
        if ($user && !$user->hasEnabledTwoFactorAuthentication()) {
            // If it's an API request, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-Factor Authentication (2FA) is required to access admin features. Please enable 2FA first.',
                    'error' => '2FA_REQUIRED'
                ], 403);
            }

            // For web requests, redirect to 2FA settings page with warning message
            return redirect()
                ->route('two-factor.show')
                ->with('error', 'Two-Factor Authentication (2FA) is required to access the admin dashboard. Please enable 2FA first.');
        }

        return $next($request);
    }
}
