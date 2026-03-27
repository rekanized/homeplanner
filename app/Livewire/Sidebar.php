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

    public function render()
    {
        return view('livewire.sidebar', [
            'economyEnabled' => filter_var(\App\Models\Setting::get('module_economy_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'shoppingEnabled' => filter_var(\App\Models\Setting::get('module_shopping_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'todoEnabled' => filter_var(\App\Models\Setting::get('module_todo_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'kidsEnabled' => filter_var(\App\Models\Setting::get('module_kids_enabled', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
