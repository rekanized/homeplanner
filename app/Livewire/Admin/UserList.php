<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

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
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email:rfc|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255',
        ]);

        User::create([
            'name' => trim($this->name),
            'email' => Str::lower(trim($this->email)),
            'password' => Hash::make($this->password),
        ]);

        $this->showCreateModal = false;
        session()->flash('message', __('User created successfully.'));
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if (! $user) {
            return;
        }

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
        if (! auth()->user()->isMaster()) {
            session()->flash('error', __('Only the System Master can assign child status.'));

            return;
        }

        $user = User::find($id);
        if (! $user) {
            return;
        }

        // Cannot make master a child
        if ($user->isMaster()) {
            session()->flash('error', __('The Master User cannot be tagged as a child.'));

            return;
        }

        $user->is_child = ! $user->is_child;
        $user->save();

        $status = $user->is_child ? __('tagged as a child') : __('removed from children');
        session()->flash('message', __('User :name has been :status.', ['name' => $user->name, 'status' => $status]));
    }

    public function updateMonthlyGoal($id, $goal)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->isMaster()) {
            return;
        }

        $validated = validator(
            ['id' => $id, 'goal' => $goal],
            ['id' => 'required|integer', 'goal' => 'required|integer|min:0|max:1000000']
        )->validate();

        $user = User::whereKey($validated['id'])->where('is_child', true)->first();
        if (! $user) {
            return;
        }

        $user->update(['monthly_points_goal' => $validated['goal']]);
        session()->flash('message', __('Monthly goal for :name updated to :goal points.', ['name' => $user->name, 'goal' => $validated['goal']]));
    }

    public function impersonate($id)
    {
        if (! auth()->user()->isAdmin() && ! auth()->user()->isMaster()) {
            session()->flash('error', __('Only administrators can impersonate.'));

            return;
        }

        $user = User::find($id);
        if (! $user) {
            return;
        }

        if ($user->isMaster() && ! auth()->user()->isMaster()) {
            session()->flash('error', __('Only the System Master can impersonate the Master User.'));

            return;
        }

        // Safety: Cannot impersonate self
        if ($user->id === auth()->id()) {
            return;
        }

        session(['impersonator_id' => auth()->id()]);
        Auth::login($user);
        session()->regenerate();

        return redirect()->to('/');
    }

    public function render()
    {
        return view('livewire.admin.user-list', [
            'users' => User::latest()->get(),
        ])->layout('components.app-layout');
    }
}
