@props([
    'paginator',
    'perPageOptions' => [10, 25, 50, 100],
    'perPageBinding' => 'perPage',
])

<div class="pagination-toolbar">
    <div class="pagination-toolbar-meta">
        <div class="pagination-summary">
            {{ __('Showing :from-:to of :total items', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
        </div>

        <label class="pagination-size-control">
            <span>{{ __('Rows per page') }}</span>
            <select wire:model.live="{{ $perPageBinding }}" class="pagination-size-select">
                @foreach($perPageOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="pagination-toolbar-links">
        {{ $paginator->links('livewire.partials.custom-pagination') }}
    </div>
</div>