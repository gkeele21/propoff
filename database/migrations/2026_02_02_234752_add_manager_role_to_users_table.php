<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'manager' to the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('manager', 'admin', 'user', 'guest') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'manager' from the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'guest') DEFAULT 'user'");
    }
};
