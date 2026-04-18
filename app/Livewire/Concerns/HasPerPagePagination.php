<?php

namespace App\Livewire\Concerns;

trait HasPerPagePagination
{
    public int $perPage = 25;

    protected int $defaultPerPage = 25;

    protected array $perPageValues = [10, 25, 50, 100];

    protected function queryStringHasPerPagePagination(): array
    {
        return [
            'perPage' => ['except' => $this->defaultPerPage],
        ];
    }

    public function perPageOptions(): array
    {
        return $this->perPageValues;
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = $this->sanitizePerPage($value);
        $this->resetPage();
    }

    protected function sanitizePerPage($value): int
    {
        $value = (int) $value;

        return in_array($value, $this->perPageOptions(), true)
            ? $value
            : $this->defaultPerPage;
    }
}