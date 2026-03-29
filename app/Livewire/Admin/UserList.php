<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Setting;

class UserList extends Component
{
    public $showCreateModal = false;
    public $name = '';
    public $email = '';
    public $password = '';

    public function openCreateModal()
    {
        $this->reset(['name', 'email', 'password']);
        $this->showCreateModal = true;
    }

    public function createUser()
    {
        $this->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => \Illuminate\Support\Facades\Hash::make($this->password),
        ]);

        $this->showCreateModal = false;
        session()->flash('message', __('User created successfully.'));
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) return;

        // Safety: Cannot delete self
        if ($user->id === auth()->id()) {
            session()->flash('error', __('You cannot delete your own account.'));
            return;
        }

        // Safety: Cannot delete the Master User
        if ($user->isMaster()) {
            session()->flash('error', __('The Master User cannot be deleted.'));
            return;
        }

        $user->delete();
        session()->flash('message', __('User deleted successfully.'));
    }

    public function toggleChild($id)
    {
        // Only Master User can toggle child status
        if (!auth()->user()->isMaster()) {
            session()->flash('error', __('Only the System Master can assign child status.'));
            return;
        }

        $user = User::find($id);
        if (!$user) return;

        // Cannot make master a child
        if ($user->isMaster()) {
            session()->flash('error', __('The Master User cannot be tagged as a child.'));
            return;
        }

        $user->is_child = !$user->is_child;
        $user->save();

        $status = $user->is_child ? __('tagged as a child') : __('removed from children');
        session()->flash('message', __('User :name has been :status.', ['name' => $user->name, 'status' => $status]));
    }

    public function updateMonthlyGoal($id, $goal)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isMaster()) {
            return;
        }

        $user = User::find($id);
        if (!$user) return;

        $user->update(['monthly_points_goal' => (int)$goal]);
        session()->flash('message', __('Monthly goal for :name updated to :goal points.', ['name' => $user->name, 'goal' => $goal]));
    }

    public function impersonate($id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isMaster()) {
            session()->flash('error', __('Only administrators can impersonate.'));
            return;
        }

        $user = User::find($id);
        if (!$user) return;

        // Safety: Cannot impersonate self
        if ($user->id === auth()->id()) {
            return;
        }

        session(['impersonator_id' => auth()->id()]);
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->to('/');
    }
    public function render()
    {
        return view('livewire.admin.user-list', [
            'users' => User::latest()->get()
        ])->layout('components.app-layout');
    }
}
