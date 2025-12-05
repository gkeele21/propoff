<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\Group;
use App\Models\QuestionTemplate;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {
        // Authorization is handled by the 'admin' middleware on the route
        return Inertia::render('Admin/Dashboard', [
            'adminStats' => $this->getAdminStats(),
        ]);
    }

    /**
     * Get admin statistics.
     */
    private function getAdminStats()
    {
        return [
            'total_events' => Event::count(),
            'total_templates' => QuestionTemplate::count(),
            'total_questions' => EventQuestion::count(),
            'total_users' => User::count(),
            'total_groups' => Group::count(),
            'total_entries' => Entry::count(),
            'completed_entries' => Entry::where('is_complete', true)->count(),
        ];
    }
}
