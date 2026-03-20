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
            $table->string('handling')->nullable()->after('amount');
        });

        Schema::table('savings', function (Blueprint $table) {
            $table->string('handling')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('handling');
        });

        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn('handling');
        });
    }

};
