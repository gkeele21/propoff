<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Services\SmartRoutingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): Response
    {
        // Store the intended URL if redirect parameter is provided
        if ($request->has('redirect')) {
            $request->session()->put('url.intended', $request->query('redirect'));
        }

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, SmartRoutingService $smartRouting): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Check if there's an intended URL (from login redirect)
        $intended = $request->session()->pull('url.intended');
        if ($intended) {
            return redirect($intended);
        }

        // Use smart routing based on user's active groups
        return redirect($smartRouting->getRedirectForUser($request->user()));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
