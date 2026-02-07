<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Set password for a user who doesn't have one (guests).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Only allow if user doesn't have a password yet
        if ($user->password !== null) {
            return back()->withErrors(['password' => 'You already have a password. Use the update form instead.']);
        }

        // Require email to be set before setting password
        if (!$user->email) {
            return back()->withErrors(['email' => 'Please set your email address first.']);
        }

        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
            'guest_token' => null, // Remove guest token since they can now login normally
        ]);

        return back()->with('status', 'password-set');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
}
