<?php

namespace App\Console\Commands;

use App\Models\Chore;
use App\Models\PredefinedChore;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('kids:generate-recurring')]
#[Description('Generate due chores from recurring templates without creating duplicates for the same child and day')]
class GenerateRecurringChores extends Command
{
    public function handle()
    {
        $templates = PredefinedChore::where('recurrence_type', '!=', 'none')
            ->whereNotNull('assigned_user_ids')
            ->lazyById(100);

        $count = 0;
        $today = now()->startOfDay();
        $tomorrow = $today->copy()->addDay();
        $childIds = null;
        $todayDayName = now()->format('l');
        $todayDayNum = now()->day;

        foreach ($templates as $template) {
            $shouldGenerate = false;

            if ($template->recurrence_type === 'daily') {
                $shouldGenerate = true;
            } elseif ($template->recurrence_type === 'weekly') {
                if (is_array($template->recurrence_day) && in_array($todayDayName, $template->recurrence_day)) {
                    $shouldGenerate = true;
                }
            } elseif ($template->recurrence_type === 'monthly') {
                $configuredDay = (int) $template->recurrence_day;
                if ($configuredDay > 0 && min($configuredDay, now()->daysInMonth) === $todayDayNum) {
                    $shouldGenerate = true;
                }
            }

            if ($shouldGenerate) {
                $assignedIds = is_array($template->assigned_user_ids) ? $template->assigned_user_ids : [];

                $childIds ??= User::where('is_child', true)->pluck('id')->all();
                $assignedIds = array_values(array_intersect(array_unique($assignedIds), $childIds));
                if ($assignedIds === []) {
                    continue;
                }

                $count += DB::transaction(function () use ($template, $assignedIds, $today, $tomorrow) {
                    $generatedIds = Chore::where('source_template_id', $template->id)
                        ->whereIn('user_id', $assignedIds)
                        ->where('created_at', '>=', $today)
                        ->where('created_at', '<', $tomorrow)
                        ->pluck('user_id')->all();
                    $missingIds = array_diff($assignedIds, $generatedIds);

                    foreach ($missingIds as $userId) {
                        Chore::create([
                            'title' => $template->title,
                            'description' => $template->description,
                            'score' => $template->score,
                            'user_id' => $userId,
                            'source_template_id' => $template->id,
                            'needs_approval' => $template->needs_approval,
                        ]);
                    }

                    if ($missingIds !== []) {
                        $template->update(['last_generated_at' => now()]);
                    }

                    return count($missingIds);
                });
            }
        }

        $this->info("Generated {$count} recurring chores.");
    }
}
