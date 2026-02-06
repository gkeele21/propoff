<?php

namespace App\Http\Controllers;

use App\Models\EventInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GuestController extends Controller
{
    /**
     * Show the guest registration page.
     */
    public function show($token)
    {
        $invitation = EventInvitation::where('token', $token)
            ->with(['event', 'group'])
            ->firstOrFail();

        if (!$invitation->isValid()) {
            return Inertia::render('Guest/InvitationExpired', [
                'message' => 'This invitation is no longer valid.',
            ]);
        }

        return Inertia::render('Guest/Join', [
            'invitation' => [
                'token' => $invitation->token,
                'event' => [
                    'id' => $invitation->event->id,
                    'name' => $invitation->event->name,
                    'event_date' => $invitation->event->event_date,
                    'status' => $invitation->event->status,
                ],
                'group' => [
                    'id' => $invitation->group->id,
                    'name' => $invitation->group->name,
                ],
            ],
        ]);
    }

    /**
     * Register guest and auto-login.
     */
    public function register(Request $request, $token)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $invitation = EventInvitation::where('token', $token)
            ->with(['event', 'group'])
            ->firstOrFail();

        if (!$invitation->isValid()) {
            return back()->withErrors(['token' => 'This invitation is no longer valid.']);
        }

        // Create guest user
        $guestToken = Str::random(32);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email, // Store email for Phase 2
            'password' => null,
            'role' => 'guest',
            'guest_token' => $guestToken,
        ]);

        // Add user to group with proper pivot data
        $invitation->group->users()->attach($user->id, [
            'joined_at' => now(),
            'is_captain' => false,
        ]);

        // Increment invitation usage
        $invitation->incrementUsage();

        // Auto-login
        Auth::login($user);

        // Generate magic link for guest user
        $magicLink = route('guest.login', ['guestToken' => $guestToken]);

        // Send email with magic link if email provided
        // TODO: Uncomment when ready to send emails
        // if ($request->email) {
        //     Mail::to($request->email)->send(new GuestWelcome($user, $invitation->event, $magicLink));
        // }

        // Redirect to play hub with magic link
        session()->flash('success', 'Welcome! You can now play the event.');
        session()->flash('magic_link', $magicLink);
        session()->flash('show_magic_link', true);
        return \Inertia\Inertia::location(route('play.hub'));
    }

    /**
     * Auto-login guest user via guest token (magic link).
     */
    public function login($guestToken)
    {
        $user = User::where('guest_token', $guestToken)
            ->where('role', 'guest')
            ->firstOrFail();

        // Auto-login
        Auth::login($user);

        // Redirect to play hub with success message
        return redirect()->route('play.hub')
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Show the guest login page (manual token entry).
     */
    public function showLoginForm()
    {
        return Inertia::render('Guest/Login');
    }

    /**
     * Handle guest login form submission (manual token entry).
     */
    public function loginWithToken(Request $request)
    {
        $request->validate([
            'guest_token' => 'required|string|size:32',
        ]);

        $user = User::where('guest_token', $request->guest_token)
            ->where('role', 'guest')
            ->first();

        if (!$user) {
            return back()->withErrors([
                'guest_token' => 'Invalid guest token. Please check your token and try again.',
            ]);
        }

        // Auto-login
        Auth::login($user);

        // Redirect to play hub with success message
        return redirect()->route('play.hub')
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Show guest results page.
     */
    public function results($guestToken)
    {
        $user = User::where('guest_token', $guestToken)
            ->where('role', 'guest')
            ->firstOrFail();

        // Get user's entries
        $entries = $user->entries()
            ->with(['event', 'group'])
            ->latest()
            ->get()
            ->map(function ($entry) use ($user) {
                return [
                    'id' => $entry->id,
                    'event_id' => $entry->event_id,
                    'group_id' => $entry->group_id,
                    'group_code' => $entry->group->code,
                    'event_name' => $entry->event->name,
                    'group_name' => $entry->group->name,
                    'total_score' => $entry->total_score,
                    'possible_points' => $entry->possible_points,
                    'percentage' => $entry->percentage,
                    'is_complete' => $entry->is_complete,
                    'submitted_at' => $entry->submitted_at,
                    'can_edit' => $user->canEditEntry($entry),
                ];
            });

        return Inertia::render('Guest/MyResults', [
            'user' => [
                'name' => $user->name,
                'guest_token' => $user->guest_token,
            ],
            'entries' => $entries,
        ]);
    }
}
