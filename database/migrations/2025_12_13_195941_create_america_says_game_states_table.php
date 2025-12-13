<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('america_says_game_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('current_question_id')->nullable()->constrained('event_questions')->onDelete('set null');
            $table->timestamp('timer_started_at')->nullable();
            $table->timestamp('timer_paused_at')->nullable();
            $table->integer('timer_duration')->default(30);
            $table->json('revealed_answer_ids')->nullable();
            $table->timestamps();

            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('america_says_game_states');
    }
};
