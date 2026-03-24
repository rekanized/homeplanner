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
        Schema::table('expenses', function (Blueprint $table) {
            $table->integer('sort_order')->default(0);
        });
        Schema::table('incomes', function (Blueprint $table) {
            $table->integer('sort_order')->default(0);
        });
        Schema::table('savings', function (Blueprint $table) {
            $table->integer('sort_order')->default(0);
        });
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->integer('sort_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
