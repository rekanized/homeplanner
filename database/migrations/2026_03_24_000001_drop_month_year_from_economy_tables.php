<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['month', 'year']);
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn(['month', 'year']);
        });

        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->integer('month')->default(1);
            $table->integer('year')->default(2026);
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->integer('month')->default(1);
            $table->integer('year')->default(2026);
        });

        Schema::table('savings', function (Blueprint $table) {
            $table->integer('month')->default(1);
            $table->integer('year')->default(2026);
        });
    }
};
