<?php

namespace App\Http\Controllers\AmericaSays;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\EventAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AmericaSaysController extends Controller
{
    /**
     * Get all questions and answers for an event
     */
    public function getQuestions($eventId)
    {
        $questions = EventQuestion::where('event_id', $eventId)
            ->with(['eventAnswers' => function ($query) {
                $query->orderBy('display_order');
            }])
            ->get();

        return response()->json($questions);
    }

    /**
     * Get current game state
     */
    public function getGameState($eventId)
    {
        $gameState = DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->first();

        if (!$gameState) {
            // Initialize game state if it doesn't exist
            $firstQuestion = EventQuestion::where('event_id', $eventId)
                ->orderBy('id')
                ->first();

            $gameState = [
                'event_id' => $eventId,
                'current_question_id' => $firstQuestion?->id,
                'timer_started_at' => null,
                'timer_paused_at' => null,
                'timer_duration' => 30,
                'revealed_answer_ids' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('america_says_game_states')->insert($gameState);
            $gameState = (object) $gameState;
        }

        // Get current question with answers
        $currentQuestion = null;
        if ($gameState->current_question_id) {
            $currentQuestion = EventQuestion::with(['eventAnswers' => function ($query) {
                $query->orderBy('display_order');
            }])->find($gameState->current_question_id);
        }

        $revealedAnswerIds = json_decode($gameState->revealed_answer_ids ?? '[]', true);

        // Return timestamps as Unix epoch (seconds) for easier client-side calculation
        return response()->json([
            'current_question' => $currentQuestion,
            'timer_started_at' => $gameState->timer_started_at ? strtotime($gameState->timer_started_at) : null,
            'timer_paused_at' => $gameState->timer_paused_at ? strtotime($gameState->timer_paused_at) : null,
            'timer_duration' => $gameState->timer_duration,
            'revealed_answer_ids' => $revealedAnswerIds,
        ]);
    }

    /**
     * Start the timer
     */
    public function startTimer(Request $request, $eventId)
    {
        DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->update([
                'timer_started_at' => now(),
                'timer_paused_at' => null,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Pause the timer
     */
    public function pauseTimer(Request $request, $eventId)
    {
        DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->update([
                'timer_paused_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reset the timer
     */
    public function resetTimer(Request $request, $eventId)
    {
        DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->update([
                'timer_started_at' => null,
                'timer_paused_at' => null,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reveal an answer
     */
    public function revealAnswer(Request $request, $eventId)
    {
        $answerId = $request->input('answer_id');

        $gameState = DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->first();

        $revealedAnswerIds = json_decode($gameState->revealed_answer_ids ?? '[]', true);

        if (!in_array($answerId, $revealedAnswerIds)) {
            $revealedAnswerIds[] = $answerId;
        }

        DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->update([
                'revealed_answer_ids' => json_encode($revealedAnswerIds),
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Unreveal an answer (uncheck)
     */
    public function unrevealAnswer(Request $request, $eventId)
    {
        $answerId = $request->input('answer_id');

        $gameState = DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->first();

        $revealedAnswerIds = json_decode($gameState->revealed_answer_ids ?? '[]', true);
        $revealedAnswerIds = array_values(array_diff($revealedAnswerIds, [$answerId]));

        DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->update([
                'revealed_answer_ids' => json_encode($revealedAnswerIds),
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Navigate to next question
     */
    public function nextQuestion(Request $request, $eventId)
    {
        $gameState = DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->first();

        $nextQuestion = EventQuestion::where('event_id', $eventId)
            ->where('id', '>', $gameState->current_question_id)
            ->orderBy('id')
            ->first();

        if ($nextQuestion) {
            DB::table('america_says_game_states')
                ->where('event_id', $eventId)
                ->update([
                    'current_question_id' => $nextQuestion->id,
                    'timer_started_at' => null,
                    'timer_paused_at' => null,
                    'revealed_answer_ids' => json_encode([]),
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Navigate to previous question
     */
    public function previousQuestion(Request $request, $eventId)
    {
        $gameState = DB::table('america_says_game_states')
            ->where('event_id', $eventId)
            ->first();

        $previousQuestion = EventQuestion::where('event_id', $eventId)
            ->where('id', '<', $gameState->current_question_id)
            ->orderBy('id', 'desc')
            ->first();

        if ($previousQuestion) {
            DB::table('america_says_game_states')
                ->where('event_id', $eventId)
                ->update([
                    'current_question_id' => $previousQuestion->id,
                    'timer_started_at' => null,
                    'timer_paused_at' => null,
                    'revealed_answer_ids' => json_encode([]),
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['success' => true]);
    }
}
