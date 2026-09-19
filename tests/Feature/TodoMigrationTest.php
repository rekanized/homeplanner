<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TodoMigrationTest extends TestCase
{
    public function test_legacy_lists_are_consolidated_and_preserved_as_tags(): void
    {
        $originalConnection = DB::getDefaultConnection();
        $connection = config('database.connections.sqlite');
        $connection['database'] = ':memory:';
        $connection['foreign_key_constraints'] = true;
        config(['database.connections.legacy_todo_migration' => $connection]);
        DB::purge('legacy_todo_migration');
        DB::setDefaultConnection('legacy_todo_migration');

        try {
            Schema::create('todos', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('color')->default('#4f46e5');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
            Schema::create('todo_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('todo_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->boolean('is_done')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->date('due_date')->nullable();
                $table->string('category')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            $now = now();
            $home = DB::table('todos')->insertGetId(['name' => 'Home', 'color' => '#4f46e5', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now]);
            $work = DB::table('todos')->insertGetId(['name' => 'Work', 'color' => '#4f46e5', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $workTask = DB::table('todo_items')->insertGetId(['todo_id' => $work, 'name' => 'Submit report', 'is_done' => true, 'completed_at' => $now, 'due_date' => '2026-09-21', 'category' => 'Urgent', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now]);
            $homeTask = DB::table('todo_items')->insertGetId(['todo_id' => $home, 'name' => 'Vacuum', 'is_done' => false, 'completed_at' => null, 'due_date' => null, 'category' => 'urgent', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now]);

            $migration = require database_path('migrations/2026_09_20_000000_consolidate_todos_by_tags.php');
            $migration->up();

            $this->assertSame(1, DB::table('todos')->count());
            $this->assertFalse(Schema::hasColumn('todo_items', 'category'));
            $this->assertSame([$workTask, $homeTask], DB::table('todo_items')->orderBy('sort_order')->pluck('id')->all());
            $this->assertSame([0, 1], DB::table('todo_items')->orderBy('sort_order')->pluck('sort_order')->all());
            $this->assertSame($now->format('Y-m-d H:i:s'), (string) DB::table('todo_items')->where('id', $workTask)->value('completed_at'));
            $this->assertSame('2026-09-21', DB::table('todo_items')->where('id', $workTask)->value('due_date'));
            $this->assertSame(['Urgent', 'Work'], $this->tagNamesFor($workTask));
            $this->assertSame(['Home', 'Urgent'], $this->tagNamesFor($homeTask));
            $this->assertSame(3, DB::table('todo_tags')->count());
        } finally {
            DB::purge('legacy_todo_migration');
            DB::setDefaultConnection($originalConnection);
        }
    }

    private function tagNamesFor(int $itemId): array
    {
        return DB::table('todo_item_tag')
            ->join('todo_tags', 'todo_tags.id', '=', 'todo_item_tag.todo_tag_id')
            ->where('todo_item_tag.todo_item_id', $itemId)
            ->orderBy('todo_tags.name')
            ->pluck('todo_tags.name')
            ->all();
    }
}
