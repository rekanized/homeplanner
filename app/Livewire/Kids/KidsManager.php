<?php

namespace App\Livewire\Kids;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Chore;
use App\Models\Redemption;
use App\Models\PredefinedChore;
use Illuminate\Support\Facades\Auth;

class KidsManager extends Component
{
    use WithFileUploads;

    public $showAddChoreModal = false;
    public $title = '';
    public $description = '';
    public $score = 10;
    public $assigned_to = [];
    public $complete_immediately = false;
    public $needs_approval = false;

    // Proof Upload Properties
    public $showProofUploadModal = false;
    public $selectedChoreId = null;
    public $proofImage = null;

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
    public $templateRecurrenceType = 'none';
    public $templateRecurrenceDay = [];
    public $templateAssignedUserIds = [];
    public $templateNeedsApproval = false;

    // Quick Assign Properties
    public $showQuickAssignModal = false;
    public $quickAssignUserId = null;
    public $quickAssignUserName = '';
    public $quickAssignCompleteImmediately = false;

    public function mount()
    {
        // Check for recurring chores generation on load
        \Illuminate\Support\Facades\Artisan::call('kids:generate-recurring');

        $this->assigned_to = [];
    }

    public function openAddChoreModal()
    {
        $this->reset(['title', 'description', 'score', 'assigned_to', 'complete_immediately', 'needs_approval']);
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
                'needs_approval' => $this->needs_approval,
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
        if (!$chore || $chore->is_completed || $chore->is_pending_approval) return;

        if ($chore->needs_approval) {
            $this->selectedChoreId = $id;
            $this->proofImage = null;
            $this->showProofUploadModal = true;
            return;
        }

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
        $this->reset(['templateTitle', 'templateDescription', 'templateScore', 'editingTemplateId', 'templateNeedsApproval']);
        $this->showManageTemplatesModal = true;
    }

    public function saveTemplate()
    {
        $rules = [
            'templateTitle' => 'required|min:3',
            'templateScore' => 'required|numeric|min:1',
            'templateRecurrenceType' => 'required|in:none,daily,weekly,monthly',
            'templateAssignedUserIds' => 'required|array|min:1',
            'templateAssignedUserIds.*' => 'exists:users,id',
        ];
        
        $this->validate($rules);

        $recurrenceDay = $this->templateRecurrenceDay;
        // If not weekly, it should be a single value (string) in DB but handled by cast
        if ($this->templateRecurrenceType !== 'weekly' && is_array($recurrenceDay)) {
            $recurrenceDay = reset($recurrenceDay) ?: '';
        }

        $data = [
            'title' => $this->templateTitle,
            'description' => $this->templateDescription,
            'score' => $this->templateScore,
            'recurrence_type' => $this->templateRecurrenceType,
            'recurrence_day' => $recurrenceDay,
            'assigned_user_ids' => $this->templateAssignedUserIds,
            'needs_approval' => $this->templateNeedsApproval,
        ];

        if ($this->editingTemplateId) {
            $template = PredefinedChore::find($this->editingTemplateId);
            $template->update($data);
        } else {
            PredefinedChore::create($data);
        }

        $this->reset(['templateTitle', 'templateDescription', 'templateScore', 'templateRecurrenceType', 'templateRecurrenceDay', 'templateAssignedUserIds', 'editingTemplateId', 'templateNeedsApproval']);
        $this->templateRecurrenceDay = [];
        $this->templateAssignedUserIds = [];
        session()->flash('message', 'Template saved successfully!');
    }

    public function editTemplate($id)
    {
        $template = PredefinedChore::find($id);
        $this->editingTemplateId = $template->id;
        $this->templateTitle = $template->title;
        $this->templateDescription = $template->description;
        $this->templateScore = $template->score;
        $this->templateRecurrenceType = $template->recurrence_type;
        $this->templateRecurrenceDay = is_array($template->recurrence_day) ? $template->recurrence_day : ($template->recurrence_day ? [$template->recurrence_day] : []);
        $this->templateAssignedUserIds = is_array($template->assigned_user_ids) ? $template->assigned_user_ids : [];
        $this->templateNeedsApproval = $template->needs_approval;
    }

    public function toggleRecurrenceDay($day)
    {
        if (!is_array($this->templateRecurrenceDay)) {
            $this->templateRecurrenceDay = [];
        }

        if (in_array($day, $this->templateRecurrenceDay)) {
            $this->templateRecurrenceDay = array_diff($this->templateRecurrenceDay, [$day]);
        } else {
            $this->templateRecurrenceDay[] = $day;
        }
    }

    public function toggleChildSelection($userId)
    {
        if (!is_array($this->templateAssignedUserIds)) {
            $this->templateAssignedUserIds = [];
        }

        if (in_array($userId, $this->templateAssignedUserIds)) {
            $this->templateAssignedUserIds = array_diff($this->templateAssignedUserIds, [$userId]);
        } else {
            $this->templateAssignedUserIds[] = $userId;
        }
    }

    public function deleteTemplate($id)
    {
        PredefinedChore::destroy($id);
        if ($this->editingTemplateId == $id) {
            $this->reset(['templateTitle', 'templateDescription', 'templateScore', 'editingTemplateId']);
        }
        session()->flash('message', 'Template deleted successfully!');
    }

    // Quick Assign Methods
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
        $template = PredefinedChore::find($templateId);
        if (!$template || !$this->quickAssignUserId) return;

        $chore = Chore::create([
            'title' => $template->title,
            'description' => $template->description,
            'score' => $template->score,
            'user_id' => $this->quickAssignUserId,
            'needs_approval' => $template->needs_approval,
            'is_completed' => $this->quickAssignCompleteImmediately,
            'completed_at' => $this->quickAssignCompleteImmediately ? now() : null,
        ]);

        if ($this->quickAssignCompleteImmediately) {
            $child = User::find($this->quickAssignUserId);
            $child->accumulated_score += $template->score;
            $child->save();
        }

        $this->showQuickAssignModal = false;
        session()->flash('message', "Chore '{$template->title}' assigned " . ($this->quickAssignCompleteImmediately ? "and completed " : "") . "to {$this->quickAssignUserName}!");
    }

    public function applyTemplate($id)
    {
        $template = PredefinedChore::find($id);
        if ($template) {
            $this->title = $template->title;
            $this->description = $template->description;
            $this->score = $template->score;
            $this->needs_approval = $template->needs_approval;
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

    public function submitChoreProof()
    {
        $this->validate([
            'proofImage' => 'required|image|max:10240', // 10MB max
            'selectedChoreId' => 'required|exists:chores,id',
        ]);

        $chore = Chore::find($this->selectedChoreId);
        
        $path = $this->proofImage->store('chores/proofs', 'public');
        
        $chore->update([
            'is_pending_approval' => true,
            'proof_image_path' => $path,
        ]);

        $this->showProofUploadModal = false;
        $this->reset(['proofImage', 'selectedChoreId']);
        session()->flash('message', 'Chore submitted for approval! Waiting for parent review.');
    }

    public function approveChore($id)
    {
        if (Auth::user()->is_child) return;

        $chore = Chore::find($id);
        if (!$chore || !$chore->is_pending_approval) return;

        $chore->update([
            'is_completed' => true,
            'is_pending_approval' => false,
            'completed_at' => now(),
        ]);

        // Award points
        $child = $chore->user;
        $child->accumulated_score += $chore->score;
        $child->save();

        session()->flash('message', "Chore approved! {$chore->score} points awarded to {$child->name}.");
    }

    public function rejectChore($id)
    {
        if (Auth::user()->is_child) return;

        $chore = Chore::find($id);
        if (!$chore || !$chore->is_pending_approval) return;

        // Reset to open state so child can try again
        $chore->update([
            'is_pending_approval' => false,
            // We keep the image path for reference but it won't be "pending" anymore
        ]);

        session()->flash('message', "Chore rejected. The child can try again.");
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
