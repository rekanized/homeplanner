<?php

namespace App\Livewire\Admin;

use App\Models\AppVersion;
use Livewire\Component;
use Livewire\Attributes\Computed;

class AppVersions extends Component
{
    #[Computed]
    public function versions()
    {
        return AppVersion::orderBy('released_at', 'desc')->get();
    }

    public function render()
    {
        return view('livewire.admin.app-versions')
            ->layout('layouts.app');
    }
}
