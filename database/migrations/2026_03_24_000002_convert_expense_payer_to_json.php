<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, convert existing single-payer strings to JSON arrays
        $expenses = DB::table('expenses')->get();
        foreach ($expenses as $expense) {
            $payer = $expense->payer;
            // Only convert if it's not already a JSON array
            if (!str_starts_with($payer, '[')) {
                DB::table('expenses')
                    ->where('id', $expense->id)
                    ->update(['payer' => json_encode([$payer])]);
            }
        }

        // Change the column type to json
        Schema::table('expenses', function (Blueprint $table) {
            $table->json('payer')->change();
        });
    }

    public function down(): void
    {
        // Convert JSON arrays back to single strings (take the first payer)
        $expenses = DB::table('expenses')->get();
        foreach ($expenses as $expense) {
            $payers = json_decode($expense->payer, true);
            $singlePayer = is_array($payers) ? ($payers[0] ?? '') : $expense->payer;
            DB::table('expenses')
                ->where('id', $expense->id)
                ->update(['payer' => $singlePayer]);
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->string('payer')->change();
        });
    }
};
