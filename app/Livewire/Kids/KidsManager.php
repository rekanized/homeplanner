<?php

namespace App\Livewire\Kids;

use Livewire\Component;
use App\Models\User;
use App\Models\Chore;
use App\Models\Redemption;
use App\Models\PredefinedChore;
use Illuminate\Support\Facades\Auth;

class KidsManager extends Component
{
    public $showAddChoreModal = false;
    public $title = '';
    public $description = '';
    public $score = 10;
    public $assigned_to = [];
    public $complete_immediately = false;

    // Point Adjustment Properties
    public $showAdjustPointsModal = false;
    public $adjustUserId = null;
    public $adjustUserName = '';
    public $adjustAmount = 0;
    public $adjustType = 'add'; // 'add' or 'remove'
    public $adjustReason = '';

    // Point Redemption Properties
    public $showUsePointsModal = false;
    public $redemptionUserName = '';
    public $redemptionDescription = '';
    public $redemptionPoints = 0;
    public $redemptionUserId = null;

    // Template Management Properties
    public $showManageTemplatesModal = false;
    public $templateTitle = '';
    public $templateDescription = '';
    public $templateScore = 10;
    public $editingTemplateId = null;

    public function mount()
    {
        // Default to the first child found if any
        $firstChild = User::where('is_child', true)->first();
        if ($firstChild) {
            $this->assigned_to = [$firstChild->id];
        }
    }

    public function openAddChoreModal()
    {
        $this->reset(['title', 'description', 'score', 'complete_immediately']);
        $this->showAddChoreModal = true;
    }

    public function addChore()
    {
        $this->validate([
            'title' => 'required|min:3',
            'score' => 'required|integer|min:1',
            'assigned_to' => 'required|array|min:1',
            'assigned_to.*' => 'exists:users,id',
        ]);

        foreach ($this->assigned_to as $userId) {
            $chore = Chore::create([
                'title' => $this->title,
                'description' => $this->description,
                'score' => $this->score,
                'user_id' => $userId,
                'is_completed' => $this->complete_immediately,
                'completed_at' => $this->complete_immediately ? now() : null,
            ]);

            if ($this->complete_immediately) {
                $child = User::find($userId);
                $child->accumulated_score += $this->score;
                $child->save();
            }
        }

        $this->showAddChoreModal = false;
        session()->flash('message', count($this->assigned_to) . ' chore(s) assigned successfully!');
    }

    public function completeChore($id)
    {
        $chore = Chore::find($id);
        if (!$chore || $chore->is_completed) return;

        $chore->is_completed = true;
        $chore->completed_at = now();
        $chore->save();

        // Award points to the child
        $child = $chore->user;
        $child->accumulated_score += $chore->score;
        $child->save();

        session()->flash('message', "Great job! {$chore->score} points awarded to {$child->name}.");
    }

    public function revertChore($id)
    {
        $chore = Chore::find($id);
        if (!$chore || !$chore->is_completed) return;

        $chore->is_completed = false;
        $chore->completed_at = null;
        $chore->save();

        // Deduct points from the child
        $child = $chore->user;
        $child->accumulated_score -= $chore->score;
        // Ensure score doesn't go below 0
        if ($child->accumulated_score < 0) {
            $child->accumulated_score = 0;
        }
        $child->save();

        session()->flash('message', "Chore '{$chore->title}' has been moved back to pending. Points have been deducted.");
    }

    public function deleteChore($id)
    {
        $chore = Chore::find($id);
        if (!$chore) return;
        
        // Safety: Only admin or parent can delete? (Currently all users are parents except children)
        if (Auth::user()->is_child) return;

        $chore->delete();
        session()->flash('message', 'Chore removed.');
    }

    public function openAdjustPointsModal($userId)
    {
        $child = User::find($userId);
        if (!$child) return;

        $this->adjustUserId = $userId;
        $this->adjustUserName = $child->name;
        $this->adjustAmount = 0;
        $this->adjustType = 'add';
        $this->adjustReason = '';
        $this->showAdjustPointsModal = true;
    }

    public function adjustPoints()
    {
        $this->validate([
            'adjustAmount' => 'required|integer|min:1',
            'adjustUserId' => 'required|exists:users,id',
            'adjustType' => 'required|in:add,remove',
        ]);

        $child = User::find($this->adjustUserId);
        if (!$child) return;

        if ($this->adjustType === 'add') {
            $child->accumulated_score += $this->adjustAmount;
            $msg = "Added {$this->adjustAmount} points to {$child->name}.";
        } else {
            $child->accumulated_score -= $this->adjustAmount;
            if ($child->accumulated_score < 0) $child->accumulated_score = 0;
            $msg = "Removed {$this->adjustAmount} points from {$child->name}.";
        }

        $child->save();
        $this->showAdjustPointsModal = false;
        session()->flash('message', $msg);
    }

    public function openUsePointsModal($userId)
    {
        $child = User::find($userId);
        if (!$child) return;

        $this->redemptionUserId = $userId;
        $this->redemptionUserName = $child->name;
        $this->redemptionDescription = '';
        $this->redemptionPoints = 10;
        $this->showUsePointsModal = true;
    }

    public function usePoints()
    {
        $this->validate([
            'redemptionDescription' => 'required|min:3',
            'redemptionPoints' => 'required|numeric|min:1',
        ]);

        $child = User::find($this->redemptionUserId);

        if ($child->accumulated_score < $this->redemptionPoints) {
            $this->addError('redemptionPoints', 'Not enough points available.');
            return;
        }

        $child->accumulated_score -= $this->redemptionPoints;
        $child->save();

        Redemption::create([
            'user_id' => $this->redemptionUserId,
            'description' => $this->redemptionDescription,
            'score' => $this->redemptionPoints,
        ]);

        $this->showUsePointsModal = false;
        $this->reset(['redemptionDescription', 'redemptionPoints', 'redemptionUserId']);
        session()->flash('message', 'Points redeemed successfully!');
    }

    // Template Methods
    public function openManageTemplatesModal()
    {
        $this->reset(['templateTitle', 'templateDescription', 'templateScore', 'editingTemplateId']);
        $this->showManageTemplatesModal = true;
    }

    public function saveTemplate()
    {
        $this->validate([
            'templateTitle' => 'required|min:3',
            'templateScore' => 'required|numeric|min:1',
        ]);

        if ($this->editingTemplateId) {
            $template = PredefinedChore::find($this->editingTemplateId);
            $template->update([
                'title' => $this->templateTitle,
                'description' => $this->templateDescription,
                'score' => $this->templateScore,
            ]);
        } else {
            PredefinedChore::create([
                'title' => $this->templateTitle,
                'description' => $this->templateDescription,
                'score' => $this->templateScore,
            ]);
        }

        $this->reset(['templateTitle', 'templateDescription', 'templateScore', 'editingTemplateId']);
        session()->flash('message', 'Template saved successfully!');
    }

    public function editTemplate($id)
    {
        $template = PredefinedChore::find($id);
        $this->editingTemplateId = $template->id;
        $this->templateTitle = $template->title;
        $this->templateDescription = $template->description;
        $this->templateScore = $template->score;
    }

    public function deleteTemplate($id)
    {
        PredefinedChore::destroy($id);
        if ($this->editingTemplateId == $id) {
            $this->reset(['templateTitle', 'templateDescription', 'templateScore', 'editingTemplateId']);
        }
        session()->flash('message', 'Template deleted successfully!');
    }

    public function applyTemplate($id)
    {
        $template = PredefinedChore::find($id);
        if ($template) {
            $this->title = $template->title;
            $this->description = $template->description;
            $this->score = $template->score;
        }
    }

    public function deleteRedemption($id)
    {
        $redemption = Redemption::find($id);
        if (!$redemption) return;
        
        if (Auth::user()->is_child) return;

        // Refund points?
        $child = $redemption->user;
        $child->accumulated_score += $redemption->score;
        $child->save();

        $redemption->delete();
        session()->flash('message', 'Redemption removed and points refunded.');
    }

    public function render()
    {
        $user = Auth::user();
        
        if ($user->is_child) {
            // Children only see their own stuff
            $chores = Chore::where('user_id', $user->id)
                ->where('is_completed', false)
                ->latest()
                ->get();
            $completedChores = Chore::where('user_id', $user->id)
                ->where('is_completed', true)
                ->latest()
                ->take(10)
                ->get();
            $redemptions = Redemption::where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get();
        } else {
            // Admins/Parents see all
            $chores = Chore::where('is_completed', false)
                ->with('user')
                ->latest()
                ->get();
            $completedChores = Chore::where('is_completed', true)
                ->with('user')
                ->latest()
                ->take(20)
                ->get();
            $redemptions = Redemption::with('user')
                ->latest()
                ->take(20)
                ->get();
        }

        return view('livewire.kids.kids-manager', [
            'chores' => $chores,
            'completedChores' => $completedChores,
            'redemptions' => $redemptions,
            'templates' => PredefinedChore::all(),
            'children' => User::where('is_child', true)->get()->map(function($child) {
                $child->monthly_points = Chore::where('user_id', $child->id)
                    ->where('is_completed', true)
                    ->whereMonth('completed_at', now()->month)
                    ->whereYear('completed_at', now()->year)
                    ->sum('score');
                $child->total_finished_tasks = Chore::where('user_id', $child->id)
                    ->where('is_completed', true)
                    ->count();
                return $child;
            }),
        ])->layout('components.app-layout');
    }
}
