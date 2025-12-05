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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->integer('total_score')->default(0);
            $table->integer('possible_points')->default(0);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->boolean('is_complete')->default(false);
            $table->timestamp('submitted_at')->nullable();

            // Captain entry fields
            $table->foreignId('submitted_by_captain_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('submitted_by_captain_at')->nullable();

            $table->timestamps();

            $table->unique(['event_id', 'user_id', 'group_id']);
            $table->index('event_id');
            $table->index('user_id');
            $table->index('group_id');
            $table->index('is_complete');
            $table->index('submitted_by_captain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
