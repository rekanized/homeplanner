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
            $table->string('recurrence_type')->default('none'); // none, daily, weekly, monthly
            $table->string('recurrence_day')->nullable(); // Monday-Sunday or 1-31
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_generated_at')->nullable();
        });

        Schema::table('chores', function (Blueprint $table) {
            $table->foreignId('source_template_id')->nullable()->constrained('predefined_chores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predefined_chores', function (Blueprint $table) {
            $table->dropColumn(['recurrence_type', 'recurrence_day', 'assigned_user_id', 'last_generated_at']);
        });

        Schema::table('chores', function (Blueprint $table) {
            $table->dropColumn('source_template_id');
        });
    }
};
