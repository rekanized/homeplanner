<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todo_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('normalized_name')->unique();
            $table->timestamps();
        });

        Schema::create('todo_item_tag', function (Blueprint $table) {
            $table->foreignId('todo_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('todo_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['todo_item_id', 'todo_tag_id']);
        });

        $now = now();
        $sharedTodoId = DB::table('todos')->insertGetId([
            'name' => 'Tasks',
            'color' => '#4f46e5',
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $tagIds = [];
        $tagIdFor = function (?string $name) use (&$tagIds, $now): ?int {
            $name = trim((string) $name);
            if ($name === '') {
                return null;
            }

            $key = mb_strtolower($name);
            if (isset($tagIds[$key])) {
                return $tagIds[$key];
            }

            $tagId = DB::table('todo_tags')->insertGetId([
                'name' => $name,
                'normalized_name' => $key,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $tagIds[$key] = $tagId;
        };

        $sortOrder = 0;
        $legacyTodos = DB::table('todos')
            ->where('id', '!=', $sharedTodoId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name']);

        foreach ($legacyTodos as $legacyTodo) {
            $legacyTagId = $tagIdFor($legacyTodo->name);
            $items = DB::table('todo_items')
                ->where('todo_id', $legacyTodo->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'category']);

            foreach ($items as $item) {
                DB::table('todo_items')->where('id', $item->id)->update([
                    'todo_id' => $sharedTodoId,
                    'sort_order' => $sortOrder++,
                ]);

                $tagIdsToAttach = array_filter([
                    $legacyTagId,
                    $tagIdFor($item->category),
                ]);

                foreach (array_unique($tagIdsToAttach) as $tagId) {
                    DB::table('todo_item_tag')->insert([
                        'todo_item_id' => $item->id,
                        'todo_tag_id' => $tagId,
                    ]);
                }
            }
        }

        DB::table('todos')->where('id', '!=', $sharedTodoId)->delete();

        Schema::table('todo_items', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('todo_items', function (Blueprint $table) {
            $table->string('category')->nullable();
        });

        Schema::dropIfExists('todo_item_tag');
        Schema::dropIfExists('todo_tags');
    }
};
