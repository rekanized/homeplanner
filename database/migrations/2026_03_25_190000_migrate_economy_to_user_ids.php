<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Income;
use App\Models\Saving;
use App\Models\Expense;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add new columns
        Schema::table('incomes', function (Blueprint $table) {
            $table->unsignedBigInteger('recipient_id')->nullable()->after('amount');
        });

        Schema::table('savings', function (Blueprint $table) {
            $table->unsignedBigInteger('saver_id')->nullable()->after('amount');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->json('payer_ids')->nullable()->after('amount');
        });

        // 2. Data Migration
        $users = DB::table('users')->pluck('id', 'name');

        DB::table('incomes')->get()->each(function ($income) use ($users) {
            if ($income->recipient && isset($users[$income->recipient])) {
                DB::table('incomes')->where('id', $income->id)->update(['recipient_id' => $users[$income->recipient]]);
            }
        });

        DB::table('savings')->get()->each(function ($saving) use ($users) {
            if ($saving->saver && isset($users[$saving->saver])) {
                DB::table('savings')->where('id', $saving->id)->update(['saver_id' => $users[$saving->saver]]);
            }
        });

        DB::table('expenses')->get()->each(function ($expense) use ($users) {
            if ($expense->payer) {
                $payer = json_decode($expense->payer, true);
                if (is_array($payer)) {
                    $ids = collect($payer)
                        ->map(fn($name) => $users[$name] ?? null)
                        ->filter()
                        ->values()
                        ->toArray();
                    DB::table('expenses')->where('id', $expense->id)->update(['payer_ids' => json_encode($ids)]);
                }
            }
        });

        // 3. Drop old columns
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('recipient');
        });

        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn('saver');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('payer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->string('recipient')->nullable()->after('amount');
        });

        Schema::table('savings', function (Blueprint $table) {
            $table->string('saver')->nullable()->after('amount');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->json('payer')->nullable()->after('amount');
        });

        // Data Migration Back (optional/limited since names might have changed)
        $users = User::all()->pluck('name', 'id');

        Income::all()->each(function ($income) use ($users) {
            if ($income->recipient_id && isset($users[$income->recipient_id])) {
                $income->update(['recipient' => $users[$income->recipient_id]]);
            }
        });

        Saving::all()->each(function ($saving) use ($users) {
            if ($saving->saver_id && isset($users[$users[$saving->saver_id]])) {
                 $saving->update(['saver' => $users[$saving->saver_id]]);
            }
        });
        
         Expense::all()->each(function ($expense) use ($users) {
            if ($expense->payer_ids && is_array($expense->payer_ids)) {
                $names = collect($expense->payer_ids)
                    ->map(fn($id) => $users[$id] ?? null)
                    ->filter()
                    ->values()
                    ->toArray();
                $expense->update(['payer' => $names]);
            }
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('recipient_id');
        });

        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn('saver_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('payer_ids');
        });
    }
};
