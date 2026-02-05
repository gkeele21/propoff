<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Bert Keele',
            'email' => 'bertkeele@gmail.com',
            'password' => Hash::make('myPROPOFF#1'),
            'role' => 'manager',
        ]);

        // Seed question templates
        $this->call(QuestionTemplateSeeder::class);

        // Seed Super Bowl LX event, group, and questions
        //$this->call(SuperBowlLXSeeder::class);
    }
}
