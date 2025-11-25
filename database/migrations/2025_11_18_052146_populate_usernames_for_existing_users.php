<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Generate usernames for existing users who don't have one
        $users = DB::table('users')->whereNull('username')->get();
        
        foreach ($users as $user) {
            $baseUsername = Str::slug($user->name);
            $username = $baseUsername;
            $counter = 1;
            
            // Ensure uniqueness
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $username]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration doesn't need to be reversed
        // Usernames can remain in the database
    }
};
