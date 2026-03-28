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
        Schema::create('todos', function (Blueprint $row) {
            $row->id();
            $row->string('name');
            $row->string('color')->default('#4f46e5');
            $row->integer('sort_order')->default(0);
            $row->timestamps();
        });

        Schema::create('todo_items', function (Blueprint $row) {
            $row->id();
            $row->foreignId('todo_id')->constrained()->onDelete('cascade');
            $row->string('name');
            $row->boolean('is_done')->default(false);
            $row->timestamp('completed_at')->nullable();
            $row->date('due_date')->nullable();
            $row->string('category')->nullable();
            $row->integer('sort_order')->default(0);
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todo_items');
        Schema::dropIfExists('todos');
    }
};
