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
            $table->boolean('needs_approval')->default(false)->after('score');
        });

        Schema::table('chores', function (Blueprint $table) {
            $table->boolean('needs_approval')->default(false)->after('score');
            $table->boolean('is_pending_approval')->default(false)->after('is_completed');
            $table->string('proof_image_path')->nullable()->after('is_pending_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predefined_chores', function (Blueprint $table) {
            $table->dropColumn('needs_approval');
        });

        Schema::table('chores', function (Blueprint $table) {
            $table->dropColumn(['needs_approval', 'is_pending_approval', 'proof_image_path']);
        });
    }
};
