<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_lists', function (Blueprint $header) {
            $header->id();
            $header->string('name');
            $header->integer('sort_order')->default(0);
            $header->timestamps();
        });

        Schema::create('shopping_items', function (Blueprint $item) {
            $item->id();
            $item->foreignId('shopping_list_id')->constrained()->onDelete('cascade');
            $item->string('name');
            $item->integer('quantity')->default(1);
            $item->boolean('is_checked')->default(false);
            $item->integer('sort_order')->default(0);
            $item->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_items');
        Schema::dropIfExists('shopping_lists');
    }
};
