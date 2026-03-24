<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;

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
        session()->flash('message', 'User created successfully.');
    }

    public function render()
    {
        return view('livewire.admin.user-list', [
            'users' => User::latest()->get()
        ])->layout('components.app-layout');
    }
}
