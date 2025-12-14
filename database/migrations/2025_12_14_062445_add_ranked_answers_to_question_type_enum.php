<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'ranked_answers' to the question_type enum in question_templates table
        DB::statement("ALTER TABLE question_templates MODIFY COLUMN question_type ENUM('multiple_choice', 'yes_no', 'numeric', 'text', 'ranked_answers') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'ranked_answers' from the question_type enum
        DB::statement("ALTER TABLE question_templates MODIFY COLUMN question_type ENUM('multiple_choice', 'yes_no', 'numeric', 'text') NOT NULL");
    }
};
