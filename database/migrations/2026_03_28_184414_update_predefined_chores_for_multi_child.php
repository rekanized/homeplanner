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
        Schema::table('predefined_chores', function (Blueprint $table) {
            $table->json('assigned_user_ids')->nullable()->after('recurrence_day');
        });

        // Migrate existing data if any
        \Illuminate\Support\Facades\DB::table('predefined_chores')->get()->each(function ($chore) {
            if ($chore->assigned_user_id) {
                \Illuminate\Support\Facades\DB::table('predefined_chores')
                    ->where('id', $chore->id)
                    ->update(['assigned_user_ids' => json_encode([$chore->assigned_user_id])]);
            }
        });

        Schema::table('predefined_chores', function (Blueprint $table) {
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn('assigned_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predefined_chores', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dropColumn('assigned_user_ids');
        });
    }
};
