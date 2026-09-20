<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Keep these additive so existing installations retain their household data.
    private const INDEXES = [
        'audit_logs' => [
            'audit_logs_created_id_index' => ['created_at', 'id'],
        ],
        'shopping_items' => [
            'shopping_items_list_sort_index' => ['shopping_list_id', 'sort_order'],
            'shopping_items_checked_list_index' => ['is_checked', 'shopping_list_id'],
        ],
        'todo_items' => [
            'todo_items_list_sort_index' => ['todo_id', 'sort_order'],
            'todo_items_done_due_index' => ['is_done', 'due_date'],
            'todo_items_done_completed_index' => ['is_done', 'completed_at'],
            'todo_items_created_done_index' => ['created_at', 'is_done'],
        ],
        'todo_item_tag' => [
            // The primary key already covers lookups starting with todo_item_id.
            'todo_item_tag_tag_item_index' => ['todo_tag_id', 'todo_item_id'],
        ],
        'chores' => [
            'chores_user_completed_date_index' => ['user_id', 'is_completed', 'completed_at'],
            'chores_user_completed_created_index' => ['user_id', 'is_completed', 'created_at'],
            'chores_completed_date_index' => ['is_completed', 'completed_at'],
            'chores_completed_created_index' => ['is_completed', 'created_at'],
            'chores_created_completed_index' => ['created_at', 'is_completed'],
            'chores_template_user_created_index' => ['source_template_id', 'user_id', 'created_at'],
            'chores_pending_approval_index' => ['is_pending_approval'],
        ],
        'redemptions' => [
            'redemptions_user_created_index' => ['user_id', 'created_at'],
            'redemptions_created_index' => ['created_at'],
        ],
        'economy_snapshots' => [
            'economy_snapshots_year_month_id_index' => ['year', 'month', 'id'],
        ],
        'savings_snapshots' => [
            'savings_snapshots_created_id_index' => ['created_at', 'id'],
            'savings_snapshots_year_month_index' => ['year', 'month'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($indexes) {
                foreach ($indexes as $name => $columns) {
                    $table->index($columns, $name);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::INDEXES) as $tableName => $indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($indexes) {
                foreach ($indexes as $name => $columns) {
                    $table->dropIndex($name);
                }
            });
        }
    }
};
