<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Session;

class Sidebar extends Component
{
    #[Session]
    public $darkMode = false;

    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        // No longer dispatching from here to prevent flickering.
        // The client handles the immediate UI change.
    }

    public function render()
    {
        return view('livewire.sidebar');
    }
}
