<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting;

class ModuleSettings extends Component
{
    public $economyEnabled;
    public $shoppingEnabled;
    public $todoEnabled;
    public $kidsEnabled;

    public function mount()
    {
        $this->economyEnabled = filter_var(Setting::get('module_economy_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->shoppingEnabled = filter_var(Setting::get('module_shopping_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->todoEnabled = filter_var(Setting::get('module_todo_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->kidsEnabled = filter_var(Setting::get('module_kids_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    public function save()
    {
        Setting::set('module_economy_enabled', $this->economyEnabled, 'modules');
        Setting::set('module_shopping_enabled', $this->shoppingEnabled, 'modules');
        Setting::set('module_todo_enabled', $this->todoEnabled, 'modules');
        Setting::set('module_kids_enabled', $this->kidsEnabled, 'modules');

        session()->flash('message', 'Module settings updated successfully.');
        
        // Dispatch event to refresh sidebar if needed
        $this->dispatch('modules-updated');
    }

    public function render()
    {
        return view('livewire.admin.module-settings');
    }
}
