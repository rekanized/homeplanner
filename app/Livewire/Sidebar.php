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
        $this->dispatch('dark-mode-toggled', [
            'darkMode' => $this->darkMode
        ]);
    }

    public function render()
    {
        return view('livewire.sidebar');
    }
}
