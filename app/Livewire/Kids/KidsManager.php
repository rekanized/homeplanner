<?php

namespace App\Livewire\Kids;

use App\Models\Chore;
use App\Models\PredefinedChore;
use App\Models\Redemption;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

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
        Artisan::call('kids:generate-recurring');

        $this->assigned_to = [];
    }

    public function openAddChoreModal()
    {
        $this->ensureParent();
        $this->reset(['title', 'description', 'score', 'assigned_to', 'complete_immediately', 'needs_approval']);
        $this->showAddChoreModal = true;
    }

    public function addChore()
    {
        $this->ensureParent();

        $this->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:10000',
            'score' => 'required|integer|min:1|max:1000000',
            'assigned_to' => 'required|array|min:1',
            'assigned_to.*' => 'distinct|exists:users,id,is_child,1',
        ]);

        DB::transaction(function () {
            foreach ($this->assigned_to as $userId) {
                Chore::create([
                    'title' => trim($this->title),
                    'description' => trim($this->description),
                    'score' => $this->score,
                    'user_id' => $userId,
                    'needs_approval' => $this->needs_approval,
                    'is_completed' => $this->complete_immediately,
                    'completed_at' => $this->complete_immediately ? now() : null,
                ]);

                if ($this->complete_immediately) {
                    User::whereKey($userId)->increment('accumulated_score', $this->score);
                }
            }
        });

        $this->showAddChoreModal = false;
        session()->flash('message', __(':count chore(s) assigned successfully!', ['count' => count($this->assigned_to)]));
    }

    public function completeChore($id)
    {
        $chore = $this->accessibleChore($id);
        if (! $chore || $chore->is_completed || $chore->is_pending_approval) {
            return;
        }

        if ($chore->needs_approval) {
            $this->selectedChoreId = $id;
            $this->proofImage = null;
            $this->showProofUploadModal = true;

            return;
        }

        $completed = DB::transaction(function () use ($chore) {
            $updated = Chore::whereKey($chore->id)
                ->where('is_completed', false)
                ->where('is_pending_approval', false)
                ->update(['is_completed' => true, 'completed_at' => now()]);

            if ($updated !== 1) {
                return false;
            }

            User::whereKey($chore->user_id)->increment('accumulated_score', $chore->score);

            return true;
        });

        if (! $completed) {
            return;
        }

        $child = $chore->user;

        session()->flash('message', __('Great job! :score points awarded to :name.', ['score' => $chore->score, 'name' => $child->name]));
    }

    public function revertChore($id)
    {
        $this->ensureParent();
        $chore = Chore::find($id);
        if (! $chore || ! $chore->is_completed) {
            return;
        }

        $reverted = DB::transaction(function () use ($chore) {
            $updated = Chore::whereKey($chore->id)
                ->where('is_completed', true)
                ->update(['is_completed' => false, 'completed_at' => null]);

            if ($updated !== 1) {
                return false;
            }

            $child = User::whereKey($chore->user_id)->lockForUpdate()->first();
            $child?->update(['accumulated_score' => max(0, $child->accumulated_score - $chore->score)]);

            return true;
        });

        if (! $reverted) {
            return;
        }

        session()->flash('message', __("Chore ':title' has been moved back to pending. Points have been deducted.", ['title' => $chore->title]));
    }

    public function deleteChore($id)
    {
        $this->ensureParent();
        $chore = Chore::find($id);
        if (! $chore) {
            return;
        }

        $chore->delete();
        session()->flash('message', __('Chore removed.'));
    }

    public function openAdjustPointsModal($userId)
    {
        $this->ensureParent();
        $child = $this->child($userId);
        if (! $child) {
            return;
        }

        $this->adjustUserId = $userId;
        $this->adjustUserName = $child->name;
        $this->adjustAmount = 0;
        $this->adjustType = 'add';
        $this->adjustReason = '';
        $this->showAdjustPointsModal = true;
    }

    public function adjustPoints()
    {
        $this->ensureParent();
        $this->validate([
            'adjustAmount' => 'required|integer|min:1|max:1000000',
            'adjustUserId' => 'required|exists:users,id,is_child,1',
            'adjustType' => 'required|in:add,remove',
        ]);

        $child = $this->child($this->adjustUserId);
        if (! $child) {
            return;
        }

        if ($this->adjustType === 'add') {
            $child->accumulated_score += $this->adjustAmount;
            $msg = 'Added :amount points to :name.';
        } else {
            $child->accumulated_score -= $this->adjustAmount;
            if ($child->accumulated_score < 0) {
                $child->accumulated_score = 0;
            }
            $msg = 'Removed :amount points from :name.';
        }

        $child->save();
        $this->showAdjustPointsModal = false;
        session()->flash('message', __($msg, [
            'amount' => $this->adjustAmount,
            'name' => $child->name,
        ]));
    }

    public function openUsePointsModal($userId)
    {
        $child = $this->child($userId);
        if (! $child) {
            return;
        }

        abort_if(Auth::user()->is_child && Auth::id() !== $child->id, 403);

        $this->redemptionUserId = $userId;
        $this->redemptionUserName = $child->name;
        $this->redemptionDescription = '';
        $this->redemptionPoints = 10;
        $this->showUsePointsModal = true;
    }

    public function usePoints()
    {
        $this->validate([
            'redemptionUserId' => 'required|exists:users,id,is_child,1',
            'redemptionDescription' => 'required|string|min:3|max:255',
            'redemptionPoints' => 'required|integer|min:1|max:1000000',
        ]);

        abort_if(Auth::user()->is_child && Auth::id() !== (int) $this->redemptionUserId, 403);

        $redeemed = DB::transaction(function () {
            $child = User::whereKey($this->redemptionUserId)->lockForUpdate()->first();
            if (! $child || $child->accumulated_score < $this->redemptionPoints) {
                return false;
            }

            $child->decrement('accumulated_score', $this->redemptionPoints);

            Redemption::create([
                'user_id' => $child->id,
                'description' => trim($this->redemptionDescription),
                'score' => $this->redemptionPoints,
            ]);

            return true;
        });

        if (! $redeemed) {
            $this->addError('redemptionPoints', __('Not enough points available.'));

            return;
        }

        $this->showUsePointsModal = false;
        $this->reset(['redemptionDescription', 'redemptionPoints', 'redemptionUserId']);
        session()->flash('message', __('Points redeemed successfully!'));
    }

    // Template Methods
    public function openManageTemplatesModal()
    {
        $this->ensureParent();
        $this->reset(['templateTitle', 'templateDescription', 'templateScore', 'editingTemplateId', 'templateNeedsApproval']);
        $this->showManageTemplatesModal = true;
    }

    public function saveTemplate()
    {
        $this->ensureParent();
        $rules = [
            'templateTitle' => 'required|string|min:3|max:255',
            'templateDescription' => 'nullable|string|max:10000',
            'templateScore' => 'required|integer|min:1|max:1000000',
            'templateRecurrenceType' => 'required|in:none,daily,weekly,monthly',
            'templateAssignedUserIds' => 'required|array|min:1',
            'templateAssignedUserIds.*' => 'distinct|exists:users,id,is_child,1',
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
            if (! $template) {
                return;
            }
            $template->update($data);
        } else {
            PredefinedChore::create($data);
        }

        $this->reset(['templateTitle', 'templateDescription', 'templateScore', 'templateRecurrenceType', 'templateRecurrenceDay', 'templateAssignedUserIds', 'editingTemplateId', 'templateNeedsApproval']);
        $this->templateRecurrenceDay = [];
        $this->templateAssignedUserIds = [];
        session()->flash('message', __('Template saved successfully!'));
    }

    public function editTemplate($id)
    {
        $this->ensureParent();
        $template = PredefinedChore::find($id);
        if (! $template) {
            return;
        }
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
        $this->ensureParent();
        abort_unless(in_array((string) $day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], true) || (ctype_digit((string) $day) && (int) $day >= 1 && (int) $day <= 31), 422);
        if (! is_array($this->templateRecurrenceDay)) {
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
        $this->ensureParent();
        abort_unless($this->child($userId), 422);
        if (! is_array($this->templateAssignedUserIds)) {
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
        $this->ensureParent();
        PredefinedChore::destroy($id);
        if ($this->editingTemplateId == $id) {
            $this->reset(['templateTitle', 'templateDescription', 'templateScore', 'editingTemplateId']);
        }
        session()->flash('message', __('Template deleted successfully!'));
    }

    // Quick Assign Methods
    public function openQuickAssignModal($userId)
    {
        $this->ensureParent();
        $child = $this->child($userId);
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
        $this->ensureParent();
        $template = PredefinedChore::find($templateId);
        $child = $this->child($this->quickAssignUserId);
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

    public function applyTemplate($id)
    {
        $this->ensureParent();
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
        $this->ensureParent();
        $redemption = Redemption::find($id);
        if (! $redemption) {
            return;
        }

        $refunded = DB::transaction(function () use ($redemption) {
            $deleted = Redemption::whereKey($redemption->id)->delete();
            if ($deleted !== 1) {
                return false;
            }

            User::whereKey($redemption->user_id)->increment('accumulated_score', $redemption->score);

            return true;
        });

        if (! $refunded) {
            return;
        }
        session()->flash('message', __('Redemption removed and points refunded.'));
    }

    public function submitChoreProof()
    {
        $this->validate([
            'proofImage' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'selectedChoreId' => 'required|exists:chores,id',
        ]);

        $chore = $this->accessibleChore($this->selectedChoreId);
        abort_unless($chore && $chore->needs_approval && ! $chore->is_completed && ! $chore->is_pending_approval, 422);

        $path = $this->proofImage->store('chores/proofs', 'local');

        $updated = Chore::whereKey($chore->id)
            ->where('is_completed', false)
            ->where('is_pending_approval', false)
            ->update([
                'is_pending_approval' => true,
                'proof_image_path' => $path,
            ]);

        if ($updated !== 1) {
            Storage::disk('local')->delete($path);
            abort(409, __('This chore was already submitted or completed.'));
        }

        $this->showProofUploadModal = false;
        $this->reset(['proofImage', 'selectedChoreId']);
        session()->flash('message', __('Chore submitted for approval! Waiting for parent review.'));
    }

    public function approveChore($id)
    {
        $this->ensureParent();

        $chore = Chore::find($id);
        if (! $chore || ! $chore->is_pending_approval) {
            return;
        }

        $approved = DB::transaction(function () use ($chore) {
            $updated = Chore::whereKey($chore->id)
                ->where('is_completed', false)
                ->where('is_pending_approval', true)
                ->update([
                    'is_completed' => true,
                    'is_pending_approval' => false,
                    'completed_at' => now(),
                ]);

            if ($updated !== 1) {
                return false;
            }

            User::whereKey($chore->user_id)->increment('accumulated_score', $chore->score);

            return true;
        });

        if (! $approved) {
            return;
        }

        $child = $chore->user;

        session()->flash('message', __('Chore approved! :score points awarded to :name.', ['score' => $chore->score, 'name' => $child->name]));
    }

    public function rejectChore($id)
    {
        $this->ensureParent();

        $chore = Chore::find($id);
        if (! $chore || ! $chore->is_pending_approval) {
            return;
        }

        // Reset to open state so child can try again
        $chore->update([
            'is_pending_approval' => false,
            // We keep the image path for reference but it won't be "pending" anymore
        ]);

        session()->flash('message', __('Chore rejected. The child can try again.'));
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
            'children' => User::where('is_child', true)->withMonthlyChoreStats()->get()->sortByDesc('accumulated_score'),
        ])->layout('components.app-layout');
    }

    private function ensureParent(): void
    {
        abort_if(Auth::user()?->is_child, 403);
    }

    private function child($id): ?User
    {
        return User::whereKey($id)->where('is_child', true)->first();
    }

    private function accessibleChore($id): ?Chore
    {
        return Chore::whereKey($id)
            ->when(Auth::user()?->is_child, fn ($query) => $query->where('user_id', Auth::id()))
            ->first();
    }
}
