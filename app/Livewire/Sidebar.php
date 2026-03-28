<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Session;

class Sidebar extends Component
{
    #[Session]
    public $darkMode = false;

    protected $listeners = ['modules-updated' => '$refresh'];

    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        // No longer dispatching from here to prevent flickering.
        // The client handles the immediate UI change.
    }

    public function stopImpersonating()
    {
        if (!session()->has('impersonator_id')) return;
        
        $adminId = session('impersonator_id');
        session()->forget('impersonator_id');
        \Illuminate\Support\Facades\Auth::loginUsingId($adminId);

        return redirect()->to('/admin/users');
    }

    public function render()
    {
        $versions = json_decode(file_get_contents(resource_path('data/versions.json')), true);
        $currentVersion = $versions[0]['version'] ?? 'v1.2.0';

        return view('livewire.sidebar', [
            'economyEnabled' => filter_var(\App\Models\Setting::get('module_economy_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'shoppingEnabled' => filter_var(\App\Models\Setting::get('module_shopping_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'todoEnabled' => filter_var(\App\Models\Setting::get('module_todo_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'kidsEnabled' => filter_var(\App\Models\Setting::get('module_kids_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'appVersion' => $currentVersion,
        ]);
    }
}
