<?php

namespace App\Console\Commands;

use App\Models\Chore;
use App\Models\PredefinedChore;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('kids:generate-recurring')]
#[Description('Generate due chores from recurring templates without creating duplicates for the same child and day')]
class GenerateRecurringChores extends Command
{
    public function handle()
    {
        $templates = PredefinedChore::where('recurrence_type', '!=', 'none')
            ->whereNotNull('assigned_user_ids')
            ->get();

        $count = 0;
        $todayStr = now()->toDateString();
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

                foreach ($assignedIds as $userId) {
                    if (! User::whereKey($userId)->where('is_child', true)->exists()) {
                        continue;
                    }

                    // Check if already generated today for THIS child
                    $exists = Chore::where('source_template_id', $template->id)
                        ->where('user_id', $userId)
                        ->whereDate('created_at', $todayStr)
                        ->exists();

                    if (! $exists) {
                        Chore::create([
                            'title' => $template->title,
                            'description' => $template->description,
                            'score' => $template->score,
                            'user_id' => $userId,
                            'source_template_id' => $template->id,
                            'needs_approval' => $template->needs_approval,
                        ]);

                        $template->update(['last_generated_at' => now()]);
                        $count++;
                    }
                }
            }
        }

        $this->info("Generated {$count} recurring chores.");
    }
}
