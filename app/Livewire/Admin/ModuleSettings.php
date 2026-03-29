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
    public $snapshotDay;

    public function mount()
    {
        $this->economyEnabled = filter_var(Setting::get('module_economy_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->shoppingEnabled = filter_var(Setting::get('module_shopping_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->todoEnabled = filter_var(Setting::get('module_todo_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->kidsEnabled = filter_var(Setting::get('module_kids_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $this->snapshotDay = Setting::get('economy_snapshot_day', 25);
    }

    public function save()
    {
        Setting::set('module_economy_enabled', $this->economyEnabled, 'modules');
        Setting::set('module_shopping_enabled', $this->shoppingEnabled, 'modules');
        Setting::set('module_todo_enabled', $this->todoEnabled, 'modules');
        Setting::set('module_kids_enabled', $this->kidsEnabled, 'modules');
        Setting::set('economy_snapshot_day', $this->snapshotDay, 'economy');

        session()->flash('message', __('Module settings updated successfully.'));
        
        // Dispatch event to refresh sidebar if needed
        $this->dispatch('modules-updated');
    }

    public function render()
    {
        return view('livewire.admin.module-settings');
    }
}
