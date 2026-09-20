<?php

namespace App\Livewire\Home;

use App\Models\Chore;
use App\Models\Expense;
use App\Models\Income;
use App\Models\PredefinedChore;
use App\Models\SavingsBalance;
use App\Models\Setting;
use App\Models\ShoppingItem;
use App\Models\TodoItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    // Quick Assign Properties
    public $showQuickAssignModal = false;

    public $quickAssignUserId = null;

    public $quickAssignUserName = '';

    public $quickAssignCompleteImmediately = false;

    public function openQuickAssignModal($userId)
    {
        abort_if(auth()->user()?->is_child, 403);
        $child = User::whereKey($userId)->where('is_child', true)->first();
        if (! $child) {
            return;
        }

        $this->quickAssignUserId = $userId;
        $this->quickAssignUserName = $child->name;
        $this->quickAssignCompleteImmediately = false;
        $this->showQuickAssignModal = true;
    }

    public function quickAssignFromTemplate($templateId)
    {
        abort_if(auth()->user()?->is_child, 403);
        $template = PredefinedChore::find($templateId);
        $child = User::whereKey($this->quickAssignUserId)->where('is_child', true)->first();
        if (! $template || ! $child) {
            return;
        }

        DB::transaction(function () use ($template, $child) {
            Chore::create([
                'title' => $template->title,
                'description' => $template->description,
                'score' => $template->score,
                'user_id' => $child->id,
                'needs_approval' => $template->needs_approval,
                'is_completed' => $this->quickAssignCompleteImmediately,
                'completed_at' => $this->quickAssignCompleteImmediately ? now() : null,
            ]);

            if ($this->quickAssignCompleteImmediately) {
                $child->increment('accumulated_score', $template->score);
            }
        });

        $this->showQuickAssignModal = false;
        session()->flash('message', __("Chore ':title' assigned :status to :name!", [
            'title' => $template->title,
            'status' => $this->quickAssignCompleteImmediately ? __('and completed ') : '',
            'name' => $this->quickAssignUserName,
        ]));
    }

    public function render()
    {
        $modules = Setting::getMany([
            'module_economy_enabled' => true,
            'module_shopping_enabled' => true,
            'module_todo_enabled' => true,
            'module_kids_enabled' => true,
        ]);
        $economyEnabled = filter_var($modules['module_economy_enabled'], FILTER_VALIDATE_BOOLEAN);
        $shoppingEnabled = filter_var($modules['module_shopping_enabled'], FILTER_VALIDATE_BOOLEAN);
        $todoEnabled = filter_var($modules['module_todo_enabled'], FILTER_VALIDATE_BOOLEAN);
        $kidsEnabled = filter_var($modules['module_kids_enabled'], FILTER_VALIDATE_BOOLEAN);

        $totalIncome = $economyEnabled ? Income::sum('amount') : 0;
        $totalSavings = $economyEnabled ? SavingsBalance::sum('amount') : 0;
        $totalExpenses = $economyEnabled ? Expense::sum('amount') : 0;
        $shoppingItemsCount = $shoppingEnabled ? ShoppingItem::where('is_checked', false)->count() : 0;

        $todoStats = $todoEnabled ? TodoItem::where('is_done', false)
            ->selectRaw('COUNT(*) as waiting, SUM(CASE WHEN due_date < ? THEN 1 ELSE 0 END) as overdue', [now()->toDateString()])
            ->toBase()->first() : null;
        $todoItemsWaiting = (int) ($todoStats->waiting ?? 0);
        $todoItemsOverdue = (int) ($todoStats->overdue ?? 0);

        $productivityStart = now()->startOfWeek(Carbon::MONDAY)->subWeeks(7);
        $productivityEnd = now()->startOfDay()->addDay();

        $todoCompletions = $todoEnabled
            ? $this->weeklyCompletions(TodoItem::where('is_done', true), $productivityStart, $productivityEnd)
            : [];
        $choreCompletions = $kidsEnabled
            ? $this->weeklyCompletions(Chore::where('is_completed', true), $productivityStart, $productivityEnd)
            : [];

        $productivityWeeks = collect(range(0, 7))->map(function (int $offset) use ($productivityStart, $todoCompletions, $choreCompletions) {
            $weekStart = $productivityStart->copy()->addWeeks($offset);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            $todoCount = (int) ($todoCompletions['week_'.$offset] ?? 0);
            $choreCount = (int) ($choreCompletions['week_'.$offset] ?? 0);

            return [
                'week_start' => $weekStart->toDateString(),
                'label' => $weekStart->month === $weekEnd->month
                    ? $weekStart->format('j').'–'.$weekEnd->translatedFormat('j M')
                    : $weekStart->translatedFormat('j M').'–'.$weekEnd->translatedFormat('j M'),
                'todo_count' => $todoCount,
                'chore_count' => $choreCount,
                'total' => $todoCount + $choreCount,
                'is_current_week' => $weekStart->toDateString() === now()->startOfWeek(Carbon::MONDAY)->toDateString(),
            ];
        });

        $maxWeeklyCompleted = max(1, $productivityWeeks->max('total'));
        $productivityWeeks = $productivityWeeks->map(function (array $week) use ($maxWeeklyCompleted) {
            $week['todo_width'] = ($week['todo_count'] / $maxWeeklyCompleted) * 100;
            $week['chore_width'] = ($week['chore_count'] / $maxWeeklyCompleted) * 100;

            return $week;
        });

        $thisWeekCompleted = $productivityWeeks->last()['total'];
        $lastWeekCompleted = $productivityWeeks->get(6)['total'];
        $productivityDelta = $thisWeekCompleted - $lastWeekCompleted;
        $openTodos = $todoEnabled ? $todoItemsWaiting : 0;
        $pendingChores = $kidsEnabled ? Chore::where('is_completed', false)->count() : 0;

        $completionWindowStart = now()->subDays(29)->startOfDay();
        $recentTodos = $todoEnabled ? TodoItem::where('created_at', '>=', $completionWindowStart)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_done = ? THEN 1 ELSE 0 END) as completed', [true])
            ->toBase()->first() : null;
        $recentChores = $kidsEnabled ? Chore::where('created_at', '>=', $completionWindowStart)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_completed = ? THEN 1 ELSE 0 END) as completed', [true])
            ->toBase()->first() : null;
        $recentCompletedCount = (int) ($recentTodos->completed ?? 0) + (int) ($recentChores->completed ?? 0);
        $recentTaskCount = (int) ($recentTodos->total ?? 0) + (int) ($recentChores->total ?? 0);
        $completionRate = $recentTaskCount > 0
            ? (int) round(($recentCompletedCount / $recentTaskCount) * 100)
            : 0;

        return view('livewire.home.dashboard', [
            'totalIncome' => $totalIncome,
            'totalSavings' => $totalSavings,
            'totalExpenses' => $totalExpenses,
            'shoppingItemsCount' => $shoppingItemsCount,
            'todoItemsWaiting' => $todoItemsWaiting,
            'todoItemsOverdue' => $todoItemsOverdue,
            'productivityWeeks' => $productivityWeeks,
            'maxWeeklyCompleted' => $maxWeeklyCompleted,
            'thisWeekCompleted' => $thisWeekCompleted,
            'lastWeekCompleted' => $lastWeekCompleted,
            'productivityDelta' => $productivityDelta,
            'completionRate' => $completionRate,
            'openTodos' => $openTodos,
            'pendingChores' => $pendingChores,
            'openWorkload' => $openTodos + $pendingChores,
            'economyEnabled' => $economyEnabled,
            'shoppingEnabled' => $shoppingEnabled,
            'todoEnabled' => $todoEnabled,
            'kidsEnabled' => $kidsEnabled,
            'children' => $kidsEnabled ? User::where('is_child', true)->withMonthlyChoreStats()->orderByDesc('accumulated_score')->get() : collect(),
            'templates' => $kidsEnabled && $this->showQuickAssignModal ? PredefinedChore::all() : collect(),
        ]);
    }

    private function weeklyCompletions(Builder $query, Carbon $start, Carbon $end): array
    {
        // Return eight counters instead of transferring and repeatedly scanning every completion.
        for ($week = 0; $week < 8; $week++) {
            $weekStart = $start->copy()->addWeeks($week);
            $query->selectRaw(
                "SUM(CASE WHEN completed_at >= ? AND completed_at < ? THEN 1 ELSE 0 END) as week_{$week}",
                [$weekStart, $weekStart->copy()->addWeek()],
            );
        }

        return (array) $query->where('completed_at', '>=', $start)
            ->where('completed_at', '<', $end)
            ->toBase()->first();
    }
}
