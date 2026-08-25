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
        $totalIncome = Income::sum('amount');
        $totalSavings = SavingsBalance::sum('amount');
        $totalExpenses = Expense::sum('amount');

        $economyEnabled = filter_var(Setting::get('module_economy_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $shoppingEnabled = filter_var(Setting::get('module_shopping_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $todoEnabled = filter_var(Setting::get('module_todo_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $kidsEnabled = filter_var(Setting::get('module_kids_enabled', true), FILTER_VALIDATE_BOOLEAN);

        $shoppingItemsCount = ShoppingItem::where('is_checked', false)->count();
        $todoItemsWaiting = TodoItem::where('is_done', false)->count();
        $todoItemsOverdue = TodoItem::where('is_done', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->count();

        $productivityStart = now()->startOfWeek(Carbon::MONDAY)->subWeeks(7);
        $productivityEnd = now()->endOfDay();

        $completedTodoDates = $todoEnabled
            ? TodoItem::where('is_done', true)
                ->whereBetween('completed_at', [$productivityStart, $productivityEnd])
                ->pluck('completed_at')
                ->map(fn ($date) => Carbon::parse($date))
            : collect();

        $completedChoreDates = $kidsEnabled
            ? Chore::where('is_completed', true)
                ->whereBetween('completed_at', [$productivityStart, $productivityEnd])
                ->pluck('completed_at')
                ->map(fn ($date) => Carbon::parse($date))
            : collect();

        $productivityWeeks = collect(range(0, 7))->map(function (int $offset) use ($productivityStart, $completedTodoDates, $completedChoreDates) {
            $weekStart = $productivityStart->copy()->addWeeks($offset);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            $todoCount = $completedTodoDates->filter(fn (Carbon $date) => $date->betweenIncluded($weekStart, $weekEnd))->count();
            $choreCount = $completedChoreDates->filter(fn (Carbon $date) => $date->betweenIncluded($weekStart, $weekEnd))->count();

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
        $recentTodoCount = $todoEnabled ? TodoItem::where('created_at', '>=', $completionWindowStart)->count() : 0;
        $recentChoreCount = $kidsEnabled ? Chore::where('created_at', '>=', $completionWindowStart)->count() : 0;
        $recentCompletedCount = ($todoEnabled ? TodoItem::where('created_at', '>=', $completionWindowStart)->where('is_done', true)->count() : 0)
            + ($kidsEnabled ? Chore::where('created_at', '>=', $completionWindowStart)->where('is_completed', true)->count() : 0);
        $recentTaskCount = $recentTodoCount + $recentChoreCount;
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
            'children' => User::where('is_child', true)->withMonthlyChoreStats()->get()->sortByDesc('accumulated_score'),
            'templates' => PredefinedChore::all(),
        ]);
    }
}
