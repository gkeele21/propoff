<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuestCookieMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Check for propoff_guest cookie and auto-login guest users.
     * Also set the cookie for authenticated guest users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for propoff_guest cookie if not authenticated
        $guestToken = $request->cookie('propoff_guest');

        if ($guestToken && !$request->user()) {
            // Try to find and authenticate guest user
            $guestUser = User::where('guest_token', $guestToken)
                ->where('role', 'guest')
                ->first();

            if ($guestUser) {
                Auth::login($guestUser);
            }
        }

        $response = $next($request);

        // If user is a guest and cookie not set (or needs refresh), set it
        $user = $request->user();
        if ($user && $user->isGuest() && $user->guest_token) {
            $existingCookie = $request->cookie('propoff_guest');

            // Set cookie if not exists or different
            if (!$existingCookie || $existingCookie !== $user->guest_token) {
                $response->cookie('propoff_guest', $user->guest_token, 60 * 24 * 90); // 90 days
            }
        }

        return $response;
    }
}
