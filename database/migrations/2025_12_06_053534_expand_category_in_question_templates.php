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
        Schema::table('question_templates', function (Blueprint $table) {
            // Expand category field from 100 to 255 chars to support comma-separated categories
            $table->string('category', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            // Revert back to original size
            $table->string('category', 100)->change();
        });
    }
};
