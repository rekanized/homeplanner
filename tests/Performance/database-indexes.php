<?php

// Run with: php tests/Performance/database-indexes.php
// Always uses an isolated in-memory database and synthetic records.
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => ':memory:',
    'database.connections.sqlite.url' => null,
    'cache.default' => 'array',
]);
DB::purge('sqlite');
Artisan::call('migrate', ['--force' => true]);
$migration = require database_path('migrations/2026_09_20_161100_add_query_indexes.php');
$migration->down();

DB::transaction(function () {
    for ($i = 1; $i <= 100; $i++) {
        DB::table('users')->insert(['id' => $i, 'name' => "Child {$i}", 'email' => "child{$i}@example.test", 'password' => 'unused', 'is_child' => true]);
    }
    for ($i = 1; $i <= 10; $i++) {
        DB::table('predefined_chores')->insert(['id' => $i, 'title' => "Template {$i}"]);
    }
    for ($batch = 0; $batch < 100; $batch++) {
        $chores = $logs = [];
        for ($offset = 0; $offset < 500; $offset++) {
            $i = $batch * 500 + $offset;
            $date = gmdate('Y-m-d H:i:s', strtotime('2025-01-01 UTC') + ($i % 630) * 86400 + ($i % 86400));
            $chores[] = [
                'title' => "Chore {$i}", 'user_id' => $i % 100 + 1, 'score' => 10,
                'source_template_id' => intdiv($i, 100) % 10 + 1,
                'is_completed' => $i % 7 !== 0, 'is_pending_approval' => false,
                'created_at' => $date, 'completed_at' => $i % 7 !== 0 ? $date : null,
            ];
            $logs[] = [
                'event' => 'created', 'auditable_type' => 'App\\Models\\Chore', 'auditable_id' => $i + 1,
                'created_at' => $date, 'new_values' => json_encode(['title' => "Chore {$i}", 'score' => 10]),
            ];
        }
        DB::table('chores')->insert($chores);
        DB::table('audit_logs')->insert($logs);
    }
});

$queries = [
    'audit_page' => 'select * from audit_logs order by created_at desc, id desc limit 20',
    'child_history' => 'select * from chores where user_id = 2 and is_completed = 1 order by created_at desc, id desc limit 10',
    'monthly_points' => "select sum(score) from chores where user_id = 2 and is_completed = 1 and completed_at >= '2026-09-01' and completed_at < '2026-10-01'",
    'recurring_check' => "select user_id from chores where source_template_id = 5 and user_id in (1, 2, 3, 4) and created_at >= '2026-09-20' and created_at < '2026-09-21'",
    'productivity_count' => "select count(*) from chores where is_completed = 1 and completed_at >= '2026-08-01' and completed_at < '2026-09-21'",
];
$results = [];
$baseline = [];
foreach (['before', 'after'] as $phase) {
    if ($phase === 'after') {
        $migration->up();
    }
    foreach ($queries as $name => $sql) {
        $plan = array_column(DB::select('EXPLAIN QUERY PLAN '.$sql), 'detail');
        $rows = DB::select($sql);
        if ($phase === 'before') {
            $baseline[$name] = $rows;
        } elseif ($rows != $baseline[$name]) {
            throw new RuntimeException("Result changed for {$name}");
        }
        $samples = [];
        for ($repeat = 0; $repeat < 15; $repeat++) {
            $start = hrtime(true);
            DB::select($sql);
            $samples[] = (hrtime(true) - $start) / 1e6;
        }
        sort($samples);
        $results[$name][$phase] = ['median_ms' => round($samples[7], 4), 'plan' => $plan];
    }
}
echo json_encode([
    'sqlite_version' => DB::selectOne('select sqlite_version() as version')->version,
    'synthetic_rows' => ['chores' => 50000, 'audit_logs' => 50000, 'users' => 100],
    'iterations_per_query' => 15,
    'queries' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
