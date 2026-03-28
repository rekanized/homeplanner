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
        Schema::create('economy_snapshots', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->integer('year');
            $table->json('snapshot_data');
            $table->decimal('total_income', 12, 2);
            $table->decimal('total_expenses', 12, 2);
            $table->decimal('total_savings', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('economy_snapshots');
    }
};
