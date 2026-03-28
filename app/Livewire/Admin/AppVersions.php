<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Computed;

class AppVersions extends Component
{
    #[Computed]
    public function versions()
    {
        $path = resource_path('data/versions.json');
        if (!file_exists($path)) return collect();

        $data = json_decode(file_get_contents($path), true);
        
        return collect($data)->map(function($v) {
            $v['released_at'] = \Carbon\Carbon::parse($v['released_at']);
            return $v;
        });
    }

    public function render()
    {
        return view('livewire.admin.app-versions')
            ->layout('layouts.app');
    }
}
