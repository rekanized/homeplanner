<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'livewire.partials.custom-pagination';

    public function paginationView()
    {
        return 'livewire.partials.custom-pagination';
    }

    public function render()
    {
        return view('livewire.admin.audit-log-list', [
            'logs' => AuditLog::with('user')->latest()->paginate(25)
        ])->layout('components.app-layout');
    }
}
