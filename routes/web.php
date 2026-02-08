<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventQuestionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\QuestionTemplateController;
use App\Http\Controllers\Admin\EventQuestionController as AdminEventQuestionController;
use App\Http\Controllers\Admin\GradingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\AmericaSays\AmericaSaysController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $activeGroup = null;
    $recognizedUser = null;

    if (auth()->check()) {
        $user = auth()->user();
        $recognizedUser = [
            'name' => $user->name,
            'is_guest' => $user->isGuest(),
        ];

        $smartRouting = app(\App\Services\SmartRoutingService::class);
        $activeGroups = $smartRouting->getActiveGroups($user);
        if ($activeGroups->isNotEmpty()) {
            $group = $activeGroups->first();
            $activeGroup = [
                'name' => $group->name,
                'code' => $group->code,
                'event_name' => $group->event->name ?? null,
            ];
        }
    }

    return Inertia::render('Index', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'activeGroup' => $activeGroup,
        'recognizedUser' => $recognizedUser,
    ]);
});

// Design System Preview (temporary - remove after finalizing colors)
Route::get('/design-system', function () {
    return view('design-system-preview');
})->name('design-system');

// Guest routes (public - no auth required)
Route::get('/join/{token}', [GuestController::class, 'show'])->name('guest.join');
Route::post('/join/{token}', [GuestController::class, 'register'])->name('guest.register');
Route::get('/guest/{guestToken}', [GuestController::class, 'login'])->name('guest.login');

// Start fresh - clear guest cookie and logout
Route::post('/guest/forget', function () {
    if (auth()->check() && auth()->user()->isGuest()) {
        auth()->logout();
    }
    return redirect('/')->withoutCookie('propoff_guest');
})->name('guest.forget');

// Play routes (public - guest-accessible via cookie or join flow)
Route::prefix('play/{code}')->group(function () {
    Route::get('/', [\App\Http\Controllers\PlayController::class, 'hub'])->name('play.hub');
    Route::get('/join', [\App\Http\Controllers\PlayController::class, 'joinForm'])->name('play.join');
    Route::post('/join', [\App\Http\Controllers\PlayController::class, 'processJoin'])->name('play.join.process');
    Route::get('/game', [\App\Http\Controllers\PlayController::class, 'questions'])->name('play.game');
    Route::post('/save', [\App\Http\Controllers\PlayController::class, 'saveAnswers'])->name('play.save');
    Route::get('/leaderboard', [\App\Http\Controllers\PlayController::class, 'leaderboard'])->name('play.leaderboard');
});

// Legacy route - redirects to smart routing
Route::get('/my-home', fn () => redirect()->route('home'))->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Smart redirect to Home/Play Hub (uses SmartRoutingService)
    Route::get('/home', function () {
        $smartRouting = app(\App\Services\SmartRoutingService::class);
        return redirect($smartRouting->getRedirectForUser(auth()->user()));
    })->name('home');

    // Group selector (for users with multiple active groups)
    Route::get('/selector', [\App\Http\Controllers\GroupPickerController::class, 'index'])->name('selector');

    // History page
    Route::get('/history', [\App\Http\Controllers\HistoryController::class, 'index'])->name('history');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Group routes
    Route::resource('groups', GroupController::class)->except(['show']);
    Route::get('/groups/{group}/questions', [GroupController::class, 'show'])->name('groups.questions');
    Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');

    // Captain Group Management (requires captain of specific group)
    Route::middleware(\App\Http\Middleware\EnsureIsCaptainOfGroup::class)->group(function () {
        // Question Management
        Route::post('/groups/{group}/questions', [\App\Http\Controllers\Captain\GroupQuestionController::class, 'store'])->name('groups.questions.store');
        Route::patch('/groups/{group}/questions/{groupQuestion}', [\App\Http\Controllers\Captain\GroupQuestionController::class, 'update'])->name('groups.questions.update');
        Route::delete('/groups/{group}/questions/{groupQuestion}', [\App\Http\Controllers\Captain\GroupQuestionController::class, 'destroy'])->name('groups.questions.destroy');
        Route::post('/groups/{group}/questions/{groupQuestion}/toggle-active', [\App\Http\Controllers\Captain\GroupQuestionController::class, 'toggleActive'])->name('groups.questions.toggleActive');
        Route::post('/groups/{group}/questions/{groupQuestion}/duplicate', [\App\Http\Controllers\Captain\GroupQuestionController::class, 'duplicate'])->name('groups.questions.duplicate');
        Route::post('/groups/{group}/questions/reorder', [\App\Http\Controllers\Captain\GroupQuestionController::class, 'reorder'])->name('groups.questions.reorder');

        // Grading
        Route::post('/groups/{group}/questions/{groupQuestion}/set-answer', [\App\Http\Controllers\Captain\GradingController::class, 'setAnswer'])->name('groups.grading.setAnswer');
        Route::post('/groups/{group}/questions/{groupQuestion}/toggle-void', [\App\Http\Controllers\Captain\GradingController::class, 'toggleVoid'])->name('groups.grading.toggleVoid');

        // Member Management
        Route::get('/groups/{group}/members', [\App\Http\Controllers\Captain\MemberController::class, 'index'])->name('groups.members.index');
        Route::post('/groups/{group}/members/{user}/promote', [\App\Http\Controllers\Captain\MemberController::class, 'promoteToCaptain'])->name('groups.members.promote');
        Route::post('/groups/{group}/members/{user}/demote', [\App\Http\Controllers\Captain\MemberController::class, 'demoteFromCaptain'])->name('groups.members.demote');
        Route::delete('/groups/{group}/members/{user}', [\App\Http\Controllers\Captain\MemberController::class, 'remove'])->name('groups.members.remove');
        Route::post('/groups/{group}/regenerate-join-code', [\App\Http\Controllers\Captain\MemberController::class, 'regenerateJoinCode'])->name('groups.members.regenerateJoinCode');
        Route::post('/groups/{group}/members/add-guest', [\App\Http\Controllers\Captain\MemberController::class, 'addGuest'])->name('groups.members.addGuest');

        // Invitation Management
        Route::get('/groups/{group}/invitation', [\App\Http\Controllers\Captain\InvitationController::class, 'show'])->name('groups.invitation');
        Route::post('/groups/{group}/invitation/regenerate', [\App\Http\Controllers\Captain\InvitationController::class, 'regenerate'])->name('groups.invitation.regenerate');
        Route::post('/groups/{group}/invitation/toggle', [\App\Http\Controllers\Captain\InvitationController::class, 'toggle'])->name('groups.invitation.toggle');
        Route::patch('/groups/{group}/invitation', [\App\Http\Controllers\Captain\InvitationController::class, 'update'])->name('groups.invitation.update');

        // Lock/Unlock Group
        Route::post('/groups/{group}/toggle-lock', [GroupController::class, 'toggleLock'])->name('groups.toggle-lock');
    });
});

// Captain Invitation routes (guest accessible)
Route::prefix('captain')->name('captain.')->group(function () {
    // Join via Captain Invitation Token (guest accessible - redirects to event-specific page)
    Route::get('/join/{token}', [\App\Http\Controllers\Captain\GroupController::class, 'join'])->name('join');

    // Create Group from Captain Invitation (guest accessible - prompts login/register if needed)
    Route::get('/events/{event}/create-group/{token}', [\App\Http\Controllers\Captain\GroupController::class, 'create'])->name('groups.create');
    Route::post('/events/{event}/create-group/{token}', [\App\Http\Controllers\Captain\GroupController::class, 'store'])->name('groups.store');
});


// Admin routes (accessible to admin and manager roles)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Redirect old dashboard to events
    Route::get('/dashboard', function () {
        return redirect()->route('admin.events.index');
    })->name('dashboard');

    // Events (admin + manager)
    Route::resource('events', AdminEventController::class);
    Route::post('/events/{event}/update-status', [AdminEventController::class, 'updateStatus'])->name('events.updateStatus');
    Route::post('/events/{event}/duplicate', [AdminEventController::class, 'duplicate'])->name('events.duplicate');
    Route::get('/events/{event}/statistics', [AdminEventController::class, 'statistics'])->name('events.statistics');

    // Event Invitations
    Route::post('/events/{event}/generate-invitation', [AdminEventController::class, 'generateInvitation'])->name('events.generateInvitation');
    Route::post('/events/{event}/invitations/{invitation}/deactivate', [AdminEventController::class, 'deactivateInvitation'])->name('events.deactivateInvitation');

    // Event Questions
    Route::get('/events/{event}/import-questions', [AdminEventQuestionController::class, 'importQuestions'])->name('events.import-questions');
    Route::get('/events/{event}/event-questions/search-templates', [AdminEventQuestionController::class, 'searchTemplates'])->name('events.event-questions.searchTemplates');
    Route::post('/events/{event}/event-questions', [AdminEventQuestionController::class, 'store'])->name('events.event-questions.store');
    Route::post('/events/{event}/event-questions/template/{template}', [AdminEventQuestionController::class, 'createFromTemplate'])->name('events.event-questions.createFromTemplate');
    Route::post('/events/{event}/event-questions/bulk-create-from-templates', [AdminEventQuestionController::class, 'bulkCreateFromTemplates'])->name('events.event-questions.bulkCreateFromTemplates');
    Route::patch('/events/{event}/event-questions/{eventQuestion}', [AdminEventQuestionController::class, 'update'])->name('events.event-questions.update');
    Route::delete('/events/{event}/event-questions/{eventQuestion}', [AdminEventQuestionController::class, 'destroy'])->name('events.event-questions.destroy');
    Route::delete('/events/{event}/event-questions', [AdminEventQuestionController::class, 'destroyAll'])->name('events.event-questions.destroyAll');
    Route::post('/events/{event}/event-questions/reorder', [AdminEventQuestionController::class, 'reorder'])->name('events.event-questions.reorder');
    Route::post('/events/{event}/event-questions/{eventQuestion}/duplicate', [AdminEventQuestionController::class, 'duplicate'])->name('events.event-questions.duplicate');
    Route::post('/events/{event}/event-questions/bulk-import', [AdminEventQuestionController::class, 'bulkImport'])->name('events.event-questions.bulkImport');
    Route::post('/events/{event}/questions/{eventQuestion}/set-answer', [AdminEventQuestionController::class, 'setAnswer'])->name('events.questions.set-answer');

    // Grading
    Route::get('/events/{event}/grading', [GradingController::class, 'index'])->name('events.grading.index');
    Route::post('/events/{event}/event-questions/{eventQuestion}/set-answer', [GradingController::class, 'setAnswer'])->name('events.grading.setAnswer');
    Route::post('/events/{event}/groups/{group}/bulk-set-answers', [GradingController::class, 'bulkSetAnswers'])->name('events.grading.bulkSetAnswers');
    Route::post('/events/{event}/event-questions/{eventQuestion}/groups/{group}/toggle-void', [GradingController::class, 'toggleVoid'])->name('events.grading.toggleVoid');
    Route::post('/events/{event}/calculate-scores', [GradingController::class, 'calculateScores'])->name('events.grading.calculateScores');
    Route::get('/events/{event}/export-csv', [GradingController::class, 'exportCSV'])->name('events.grading.exportCSV');
    Route::get('/events/{event}/export-detailed-csv', [GradingController::class, 'exportDetailedCSV'])->name('events.grading.exportDetailedCSV');
    Route::get('/events/{event}/groups/{group}/export-detailed-csv', [GradingController::class, 'exportDetailedCSV'])->name('events.grading.exportDetailedCSVByGroup');
    Route::get('/events/{event}/groups/{group}/summary', [GradingController::class, 'groupSummary'])->name('events.grading.groupSummary');

    // Captain Invitations
    Route::get('/events/{event}/captain-invitations', [\App\Http\Controllers\Admin\CaptainInvitationController::class, 'index'])->name('events.captain-invitations.index');
    Route::post('/events/{event}/captain-invitations', [\App\Http\Controllers\Admin\CaptainInvitationController::class, 'store'])->name('events.captain-invitations.store');
    Route::get('/events/{event}/captain-invitations/{invitation}', [\App\Http\Controllers\Admin\CaptainInvitationController::class, 'show'])->name('events.captain-invitations.show');
    Route::patch('/events/{event}/captain-invitations/{invitation}', [\App\Http\Controllers\Admin\CaptainInvitationController::class, 'update'])->name('events.captain-invitations.update');
    Route::delete('/events/{event}/captain-invitations/{invitation}', [\App\Http\Controllers\Admin\CaptainInvitationController::class, 'destroy'])->name('events.captain-invitations.destroy');
    Route::post('/events/{event}/captain-invitations/{invitation}/deactivate', [\App\Http\Controllers\Admin\CaptainInvitationController::class, 'deactivate'])->name('events.captain-invitations.deactivate');
    Route::post('/events/{event}/captain-invitations/{invitation}/reactivate', [\App\Http\Controllers\Admin\CaptainInvitationController::class, 'reactivate'])->name('events.captain-invitations.reactivate');
    Route::post('/events/{event}/generate-captain-invitation', [AdminEventController::class, 'generateCaptainInvitation'])->name('events.generateCaptainInvitation');
    Route::post('/events/{event}/create-my-group', [\App\Http\Controllers\Admin\CaptainInvitationController::class, 'createMyGroup'])->name('events.createMyGroup');

    // Event Answers - Void functionality
    Route::post('/events/{event}/event-questions/{eventQuestion}/toggle-event-void', [\App\Http\Controllers\Admin\EventAnswerController::class, 'toggleVoid'])->name('events.event-answers.toggleVoid');

    // America Says Admin (requires auth)
    Route::prefix('america-says')->name('america-says.')->group(function () {
        Route::get('/events/{event}/host-game', function (\App\Models\Event $event) {
            return Inertia::render('Admin/AmericaSays/HostGame', [
                'eventId' => $event->id,
                'event' => $event,
            ]);
        })->name('host-game');
    });

    // Manager-only routes (Question Templates, Users, Groups)
    Route::middleware('manager')->group(function () {
        // Question Templates
        Route::resource('question-templates', QuestionTemplateController::class);
        Route::post('/question-templates/{template}/preview', [QuestionTemplateController::class, 'preview'])->name('question-templates.preview');
        Route::post('/question-templates/{template}/duplicate', [QuestionTemplateController::class, 'duplicate'])->name('question-templates.duplicate');

        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/update-role', [AdminUserController::class, 'updateRole'])->name('users.updateRole');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/{user}/activity', [AdminUserController::class, 'activity'])->name('users.activity');
        Route::get('/users/export/csv', [AdminUserController::class, 'exportCSV'])->name('users.exportCSV');
        Route::post('/users/bulk-delete', [AdminUserController::class, 'bulkDelete'])->name('users.bulkDelete');
        Route::get('/users-statistics', [AdminUserController::class, 'statistics'])->name('users.statistics');

        // Groups
        Route::get('/groups', [AdminGroupController::class, 'index'])->name('groups.index');
        Route::get('/groups/create', [AdminGroupController::class, 'create'])->name('groups.create');
        Route::post('/groups', [AdminGroupController::class, 'store'])->name('groups.store');
        Route::get('/groups/{group}', [AdminGroupController::class, 'show'])->name('groups.show');
        Route::get('/groups/{group}/edit', [AdminGroupController::class, 'edit'])->name('groups.edit');
        Route::patch('/groups/{group}', [AdminGroupController::class, 'update'])->name('groups.update');
        Route::delete('/groups/{group}', [AdminGroupController::class, 'destroy'])->name('groups.destroy');
        Route::post('/groups/{group}/add-user', [AdminGroupController::class, 'addUser'])->name('groups.addUser');
        Route::delete('/groups/{group}/users/{user}', [AdminGroupController::class, 'removeUser'])->name('groups.removeUser');
        Route::get('/groups/export/csv', [AdminGroupController::class, 'exportCSV'])->name('groups.exportCSV');
        Route::get('/groups-statistics', [AdminGroupController::class, 'statistics'])->name('groups.statistics');
        Route::get('/groups/{group}/members', [AdminGroupController::class, 'members'])->name('groups.members');
        Route::post('/groups/bulk-delete', [AdminGroupController::class, 'bulkDelete'])->name('groups.bulkDelete');
    });
});

// America Says Game Board (public - no auth required)
Route::get('/america-says/events/{event}/game-board', function ($eventId) {
    return Inertia::render('AmericaSays/GameBoard', ['eventId' => (int)$eventId]);
})->name('america-says.game-board');

// America Says API (no throttle, uses web middleware)
Route::prefix('api/america-says')->group(function () {
    Route::get('/events/{event}', function (\App\Models\Event $event) {
        return response()->json($event);
    });
    Route::get('/events/{event}/questions', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'getQuestions']);
    Route::get('/events/{event}/game-state', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'getGameState']);
    Route::post('/events/{event}/begin-game', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'beginGame']);
    Route::post('/events/{event}/start-timer', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'startTimer']);
    Route::post('/events/{event}/pause-timer', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'pauseTimer']);
    Route::post('/events/{event}/reset-timer', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'resetTimer']);
    Route::post('/events/{event}/reveal-answer', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'revealAnswer']);
    Route::post('/events/{event}/unreveal-answer', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'unrevealAnswer']);
    Route::post('/events/{event}/next-question', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'nextQuestion']);
    Route::post('/events/{event}/previous-question', [\App\Http\Controllers\AmericaSays\AmericaSaysController::class, 'previousQuestion']);
});

require __DIR__.'/auth.php';
