<div class="todo-task-row {{ $item->is_done ? 'is-completed' : '' }}" wire:key="item-{{ $item->id }}" data-id="{{ $item->id }}">
    <button type="button" wire:click="toggleItem({{ $item->id }})"
        class="completion-toggle {{ $item->is_done ? 'checked' : '' }}"
        aria-label="{{ $item->is_done ? __('Mark incomplete') : __('Click to complete task') }}"
        aria-pressed="{{ $item->is_done ? 'true' : 'false' }}">
        @if($item->is_done)
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
        @endif
    </button>

    <div class="todo-task-content">
        @if($item->is_done)
            <div class="todo-task-completed-title">{{ $item->name }}</div>
        @else
            <textarea rows="1" maxlength="255" id="task-input-{{ $item->id }}" class="todo-task-title"
                aria-label="{{ __('Task Description') }}" placeholder="{{ __('Task description...') }}"
                x-data="{
                    observer: null, width: 0,
                    resize() { $el.style.height = 'auto'; $el.style.height = ($el.scrollHeight + 2) + 'px'; },
                    init() {
                        this.observer = new ResizeObserver(() => {
                            if (this.width !== $el.clientWidth || $el.scrollHeight > $el.clientHeight) {
                                this.width = $el.clientWidth;
                                this.resize();
                            }
                        });
                        this.observer.observe($el);
                        this.$nextTick(() => this.resize());
                    },
                    destroy() { this.observer.disconnect(); }
                }"
                @input="resize()"
                @blur="$wire.updateItemName({{ $item->id }}, $event.target.value)"
                @keydown.enter.prevent="$event.target.blur()">{{ $item->name }}</textarea>
        @endif

        <div class="todo-task-meta">
            @unless($item->is_done)
                <div class="eco-drag-handle todo-task-drag" title="{{ __('Drag to reorder task') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                </div>
                <input type="date" value="{{ $item->due_date?->format('Y-m-d') }}"
                    class="todo-task-date {{ $groupKey }}"
                    aria-label="{{ __('Set due date') }}" title="{{ __('Set due date') }}"
                    @change="$wire.updateItemDueDate({{ $item->id }}, $event.target.value)">
            @endunless

            @foreach($item->tags as $tag)
                <span class="todo-task-tag" wire:key="item-{{ $item->id }}-tag-{{ $tag->id }}">
                    <span>{{ $tag->name }}</span>
                    @unless($item->is_done)
                        <button type="button" class="eco-delete-btn todo-tag-remove"
                            wire:click="removeTag({{ $item->id }}, {{ $tag->id }})"
                            wire:loading.attr="disabled" wire:target="removeTag({{ $item->id }}, {{ $tag->id }})"
                            aria-label="{{ __('Remove tag') }} {{ $tag->name }}" title="{{ __('Remove tag') }}">
                            @include('livewire.todo.trash-icon')
                        </button>
                    @endunless
                </span>
            @endforeach

            @unless($item->is_done)
                <div class="todo-task-tag-picker" wire:key="tag-picker-{{ $item->id }}" :class="{ 'is-open': open }"
                    x-data="{ open: false, query: '', saving: false,
                        async add(name) {
                            if (!name.trim() || this.saving) return;
                            this.saving = true;
                            try { await $wire.attachTag({{ $item->id }}, name); this.query = ''; this.open = false; }
                            finally { this.saving = false; }
                        }
                    }"
                    @click.outside="open = false"
                    @keydown.escape.stop.prevent="open = false; $refs.tagInput.focus()"
                    @focusout="if (!$el.contains($event.relatedTarget)) open = false">
                    <div class="todo-task-tag-search">
                        <input type="text" x-ref="tagInput" x-model="query" maxlength="255" autocomplete="off"
                            @focus="open = true" @click="open = true" @input="open = true"
                            @keydown.enter.prevent="add(query)"
                            @keydown.arrow-down.prevent="$el.closest('.todo-task-tag-picker').querySelector('.todo-task-tag-options button:not([style*=\'display: none\'])')?.focus()"
                            class="todo-tag-input" aria-label="{{ __('Add tag') }}" placeholder="{{ __('+ tag') }}"
                            :aria-expanded="open" aria-controls="tag-options-{{ $item->id }}">
                        <button type="button" x-show="open" x-cloak @click="open = false; $refs.tagInput.blur()"
                            class="todo-tag-done">{{ __('Done') }}</button>
                    </div>
                    <div x-show="open" x-cloak id="tag-options-{{ $item->id }}" class="todo-task-tag-options">
                        <p>{{ __('Choose a tag or type a new one') }}</p>
                        <button type="button" x-show="query.trim()" :disabled="saving" @click="add(query)"
                            class="todo-tag-create">{{ __('Add tag') }}: <span x-text="query"></span></button>
                        @foreach($this->availableTags->whereNotIn('id', $item->tags->modelKeys()) as $tag)
                            <button type="button" wire:key="tag-option-{{ $item->id }}-{{ $tag->id }}" :disabled="saving"
                                x-show="!query || $el.dataset.tag.toLocaleLowerCase().includes(query.trim().toLocaleLowerCase())"
                                data-tag="{{ $tag->name }}" @click="add($el.dataset.tag)">{{ $tag->name }}</button>
                        @endforeach
                    </div>
                </div>
            @endunless
        </div>
        @if($item->is_done && $item->completed_at)
            <div class="todo-task-completed-date">{{ __('Completed:') }} {{ $item->completed_at->translatedFormat('M j, Y') }}</div>
        @endif
    </div>

    <button type="button" class="eco-delete-btn todo-task-delete"
        wire:click="deleteItem({{ $item->id }})"
        wire:confirm="{{ __('Are you sure you want to delete this task?') }}"
        aria-label="{{ __('Delete task') }}: {{ $item->name }}">
        @include('livewire.todo.trash-icon')
    </button>
</div>
