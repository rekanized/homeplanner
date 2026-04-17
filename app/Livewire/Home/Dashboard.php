<?php

namespace App\Livewire\Home;

use Livewire\Component;
use App\Models\Income;
use App\Models\Saving;
use App\Models\Expense;
use App\Models\ShoppingItem;
use App\Models\TodoItem;
use App\Models\User;
use App\Models\Chore;
use Carbon\Carbon;

class Dashboard extends Component
{
    // Quick Assign Properties
    public $showQuickAssignModal = false;
    public $quickAssignUserId = null;
    public $quickAssignUserName = '';
    public $quickAssignCompleteImmediately = false;

    public function openQuickAssignModal($userId)
    {
        $child = User::find($userId);
        if (!$child) return;

        $this->quickAssignUserId = $userId;
        $this->quickAssignUserName = $child->name;
        $this->quickAssignCompleteImmediately = false;
        $this->showQuickAssignModal = true;
    }

    public function quickAssignFromTemplate($templateId)
    {
        $template = \App\Models\PredefinedChore::find($templateId);
        if (!$template || !$this->quickAssignUserId) return;

        $chore = Chore::create([
            'title' => $template->title,
            'description' => $template->description,
            'score' => $template->score,
            'user_id' => $this->quickAssignUserId,
            'is_completed' => $this->quickAssignCompleteImmediately,
            'completed_at' => $this->quickAssignCompleteImmediately ? now() : null,
        ]);

        if ($this->quickAssignCompleteImmediately) {
            $child = User::find($this->quickAssignUserId);
            $child->accumulated_score += $template->score;
            $child->save();
        }

        $this->showQuickAssignModal = false;
        session()->flash('message', __("Chore ':title' assigned :status to :name!", [
            'title' => $template->title,
            'status' => $this->quickAssignCompleteImmediately ? __('and completed ') : '',
            'name' => $this->quickAssignUserName
        ]));
    }

    public function render()
    {
        $totalIncome = Income::sum('amount');
        $totalSavings = \App\Models\SavingsBalance::sum('amount');
        $totalExpenses = Expense::sum('amount');
        
        $shoppingItemsCount = ShoppingItem::where('is_checked', false)->count();
        $todoItemsWaiting = TodoItem::where('is_done', false)->count();
        $todoItemsOverdue = TodoItem::where('is_done', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->count();

        // Chart Data: Completed Todos last 3 months
        $startDate = now()->subMonths(2)->startOfMonth();
        $endDate = now()->endOfDay();
        
        $completedTodos = TodoItem::where('is_done', true)
            ->where('completed_at', '>=', $startDate)
            ->selectRaw('DATE(completed_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $chartPoints = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $chartPoints[] = [
                'date' => $dateStr,
                'count' => $completedTodos[$dateStr] ?? 0
            ];
            $currentDate->addDay();
        }

        $weeklyProductivity = collect($chartPoints)
            ->groupBy(function (array $point) {
                return Carbon::parse($point['date'])->startOfWeek(Carbon::MONDAY)->toDateString();
            })
            ->map(function ($points, string $weekStart) {
                $weekStartDate = Carbon::parse($weekStart);
                $count = $points->sum('count');

                return [
                    'week_start' => $weekStartDate->toDateString(),
                    'label' => ucfirst($weekStartDate->translatedFormat('d M')),
                    'short_label' => strtoupper($weekStartDate->translatedFormat('M')),
                    'count' => $count,
                    'is_current_week' => $weekStartDate->isSameWeek(now()),
                ];
            })
            ->values();

        $maxWeeklyCompleted = max(1, $weeklyProductivity->max('count'));
        $weeklyProductivity = $weeklyProductivity->map(function (array $week) use ($maxWeeklyCompleted) {
            $week['height_percent'] = $week['count'] > 0
                ? max(10, (int) round(($week['count'] / $maxWeeklyCompleted) * 100))
                : 4;

            return $week;
        });

        $averageWeeklyCompleted = (int) round($weeklyProductivity->avg('count'));

        return view('livewire.home.dashboard', [
            'totalIncome' => $totalIncome,
            'totalSavings' => $totalSavings,
            'totalExpenses' => $totalExpenses,
            'shoppingItemsCount' => $shoppingItemsCount,
            'todoItemsWaiting' => $todoItemsWaiting,
            'todoItemsOverdue' => $todoItemsOverdue,
            'chartPoints' => $chartPoints,
            'totalCompleted' => array_sum(array_column($chartPoints, 'count')),
            'weeklyProductivity' => $weeklyProductivity,
            'maxWeeklyCompleted' => $maxWeeklyCompleted,
            'averageWeeklyCompleted' => $averageWeeklyCompleted,
            'economyEnabled' => filter_var(\App\Models\Setting::get('module_economy_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'shoppingEnabled' => filter_var(\App\Models\Setting::get('module_shopping_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'todoEnabled' => filter_var(\App\Models\Setting::get('module_todo_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'kidsEnabled' => filter_var(\App\Models\Setting::get('module_kids_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'children' => User::where('is_child', true)->get()->sortByDesc('accumulated_score'),
            'templates' => \App\Models\PredefinedChore::all(),
        ]);
    }
}

