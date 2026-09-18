@props(['data', 'selectedId' => null, 'title'])

@php
    $snapshots = $data['snapshots'];
    $series = $data['series'];
    $singleSeries = count($series) === 1;
    $values = collect($series)->pluck('values')->flatten()->filter(fn ($value) => $value !== null);
    $minimum = min(0, $values->min() ?? 0);
    $maximum = max(0, $values->max() ?? 0);
    $roughStep = max(1, ($maximum - $minimum) / 4);
    $magnitude = pow(10, floor(log10($roughStep)));
    $step = collect([1, 2, 5, 10])->first(fn ($factor) => $factor * $magnitude >= $roughStep) * $magnitude;
    $bottom = floor($minimum / $step) * $step;
    $top = max($bottom + $step, ceil($maximum / $step) * $step);
    $left = max(64, strlen(number_format(max(abs($bottom), abs($top)), 0, ',', ' ')) * 7 + 14);
    $width = 760;
    $x = fn ($index) => $snapshots->count() > 1 ? $left + 12 + $index * ($width - $left - 36) / ($snapshots->count() - 1) : ($left + $width - 12) / 2;
    $y = fn ($value) => 240 - ($value - $bottom) / ($top - $bottom) * 208;
    $gradientId = 'history-fill-'.md5($title);
@endphp

<section class="card history-chart" aria-label="{{ $title }}"
         wire:key="history-chart-{{ md5(json_encode([$data, $selectedId, app()->getLocale()])) }}"
         x-data="{
             active: null, hidden: [], width: 760, count: {{ $snapshots->count() }}, open: false,
             tooltipX: 0, tooltipY: 0, observer: null,
             init() {
                 if (!this.$refs.frame) return;
                 this.observer = new ResizeObserver(() => {
                     this.width = this.$refs.frame.clientWidth;
                     this.open = false;
                 });
                 this.observer.observe(this.$refs.frame);
             },
             destroy() { this.observer?.disconnect(); },
             x(index) { return this.count > 1 ? {{ $left + 12 }} + index * (this.width - {{ $left + 36 }}) / (this.count - 1) : ({{ $left }} + this.width - 12) / 2; },
             path(values) {
                 return values.map((value, index) => value === null ? '' : `${index === 0 || values[index - 1] === null ? 'M' : 'L'} ${this.x(index)} ${240 - (value - {{ $bottom }}) / {{ $top - $bottom }} * 208}`).join(' ');
             },
             labelVisible(index) {
                 const interval = Math.max(1, Math.ceil(this.count / Math.max(2, Math.floor((this.width - {{ $left }}) / 85))));
                 return index === 0 || index === this.count - 1 || (index % interval === 0 && this.count - 1 - index >= interval);
             },
             inspect(index) {
                 this.active = index;
                 const tooltipWidth = Math.min(252, this.width);
                 this.tooltipX = Math.max(0, Math.min(this.width - tooltipWidth, this.x(index) + (this.x(index) > this.width / 2 ? -tooltipWidth - 16 : 16)));
                 this.tooltipY = 24;
                 this.open = true;
             },
             follow(event, tap = false) {
                 if (event.pointerType === 'touch' && !tap) return;
                 const rect = this.$refs.plot.getBoundingClientRect();
                 const index = this.count > 1 ? Math.round((event.clientX - rect.left - this.x(0)) / (this.x(this.count - 1) - this.x(0)) * (this.count - 1)) : 0;
                 this.inspect(Math.max(0, Math.min(this.count - 1, index)));
             }
         }">
    <div class="history-chart-heading">
        <div>
            <h2>{{ $title }}</h2>
            <p>{{ __('Hover or tap to explore your history.') }}</p>
        </div>
        @if($singleSeries && $snapshots->isNotEmpty())
            <div class="history-chart-total">
                <span>{{ __('Total Accumulated') }}</span>
                <strong>{{ number_format($series[0]['values'][array_key_last($series[0]['values'])], 0, ',', ' ') }} <small>{{ __('kr') }}</small></strong>
                <time>{{ $snapshots->last()->created_at->translatedFormat('j M Y') }}</time>
            </div>
        @endif
    </div>

    @if($snapshots->isEmpty())
        <div class="history-chart-empty">
            <p>{{ __('No history to graph yet.') }}</p>
            <span>{{ __('Capture a snapshot to start your timeline.') }}</span>
        </div>
    @else
        @if(!$singleSeries)
            <div class="history-chart-legend" aria-label="{{ __('Chart legend') }}">
                @foreach($series as $seriesIndex => $line)
                    <button type="button" class="history-chart-key" style="--series-color: {{ $line['color'] }}"
                            x-on:click="hidden.includes({{ $seriesIndex }}) ? hidden = hidden.filter(i => i !== {{ $seriesIndex }}) : hidden.push({{ $seriesIndex }})"
                            x-bind:aria-pressed="!hidden.includes({{ $seriesIndex }})"
                            x-bind:class="{ 'is-hidden': hidden.includes({{ $seriesIndex }}) }">
                        <span aria-hidden="true"></span>{{ $line['label'] }}
                    </button>
                @endforeach
            </div>
        @endif

        <div class="history-chart-frame" x-ref="frame" x-on:pointerleave="if ($event.pointerType !== 'touch') open = false"
             x-on:keydown.escape.stop="open = false" x-on:click.outside="open = false"
             x-on:focusout="if (!$el.contains($event.relatedTarget)) open = false">
            <svg class="history-chart-plot" viewBox="0 0 {{ $width }} 290" x-bind:view-box.camel="`0 0 ${width} 290`" x-ref="plot"
                 x-on:pointermove="follow($event)" x-on:click="follow($event, true)" role="group" aria-label="{{ __('Snapshot timeline') }}">
                <defs>
                    <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="var(--primary)" stop-opacity="0.2" />
                        <stop offset="100%" stop-color="var(--primary)" stop-opacity="0.015" />
                    </linearGradient>
                </defs>
                <text x="{{ $left - 14 }}" y="15" text-anchor="end" class="history-chart-axis">{{ __('kr') }}</text>
                @for($tick = $bottom; $tick <= $top; $tick += $step)
                    <line x1="{{ $left }}" y1="{{ $y($tick) }}" x2="{{ $width - 24 }}" x-bind:x2="width - 24" y2="{{ $y($tick) }}" class="history-chart-grid {{ $tick == 0 ? 'history-chart-zero' : '' }}" />
                    <text x="{{ $left - 14 }}" y="{{ $y($tick) + 4 }}" text-anchor="end" class="history-chart-axis">{{ number_format($tick, 0, ',', ' ') }}</text>
                @endfor

                @foreach($series as $seriesIndex => $line)
                    @php
                        $path = '';
                        $connected = false;
                        foreach ($line['values'] as $index => $value) {
                            if ($value === null) {
                                $connected = false;
                                continue;
                            }
                            $path .= ($connected ? ' L ' : ' M ').$x($index).' '.$y($value);
                            $connected = true;
                        }
                    @endphp
                    @if($singleSeries && $snapshots->count() > 1)
                        <path d="{{ $path }} L {{ $x($snapshots->count() - 1) }} 240 L {{ $x(0) }} 240 Z"
                              x-bind:d="path(@js($line['values'])) + ` L ${x(count - 1)} 240 L ${x(0)} 240 Z`" fill="url(#{{ $gradientId }})" />
                    @endif
                    <path d="{{ $path }}" x-bind:d="path(@js($line['values']))" fill="none" stroke="{{ $line['color'] }}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" x-show="!hidden.includes({{ $seriesIndex }})" />
                @endforeach

                @foreach($snapshots as $index => $snapshot)
                    @php
                        $date = $snapshot->created_at->translatedFormat('j M Y H:i');
                        $summary = collect($series)->filter(fn ($line) => $line['values'][$index] !== null)
                            ->map(fn ($line) => $line['label'].': '.number_format($line['values'][$index], 2, ',', ' ').' '.__('kr'))->implode('; ');
                    @endphp
                    <g class="history-chart-point" role="button" tabindex="0" aria-label="{{ $date }} — {{ $summary }}"
                       x-bind:transform="`translate(${x({{ $index }}) - {{ $x($index) }}}, 0)`"
                       x-on:keydown.enter.prevent="inspect({{ $index }}); $nextTick(() => $refs.tooltip.querySelector('button:not([disabled])')?.focus())"
                       x-on:keydown.space.prevent="inspect({{ $index }})"
                       x-on:focus="inspect({{ $index }})" x-on:click="inspect({{ $index }})">
                        <rect x="{{ $x($index) - 12 }}" y="23" width="24" height="225" rx="8" class="history-chart-hit" />
                        <line x1="{{ $x($index) }}" y1="28" x2="{{ $x($index) }}" y2="244" class="history-chart-guide" x-show="open && active === {{ $index }}" style="display: none" />
                        @foreach($series as $seriesIndex => $line)
                            @if($line['values'][$index] !== null)
                                <g x-show="!hidden.includes({{ $seriesIndex }})">
                                    <circle cx="{{ $x($index) }}" cy="{{ $y($line['values'][$index]) }}" r="10" fill="{{ $line['color'] }}" opacity="0.14" x-show="open && active === {{ $index }}" style="display: none" />
                                    <circle cx="{{ $x($index) }}" cy="{{ $y($line['values'][$index]) }}" r="4" x-bind:r="open && active === {{ $index }} ? 5 : 3.5" fill="{{ $line['color'] }}" stroke="var(--bg-card)" stroke-width="2" />
                                </g>
                            @endif
                        @endforeach
                        <g x-show="labelVisible({{ $index }})">
                            <text x="{{ $x($index) }}" y="268" text-anchor="middle" class="history-chart-axis">{{ $snapshot->created_at->translatedFormat('j M') }}</text>
                            <text x="{{ $x($index) }}" y="284" text-anchor="middle" class="history-chart-axis history-chart-year">{{ $snapshot->created_at->format('Y') }}</text>
                        </g>
                    </g>
                @endforeach
            </svg>

            <div class="history-chart-tooltip {{ $singleSeries ? '' : 'history-chart-tooltip-multiple' }}" x-ref="tooltip" x-show="open" style="display: none"
                 x-bind:style="{ left: tooltipX + 'px', top: tooltipY + 'px' }" aria-live="polite" aria-atomic="true">
                @foreach($snapshots as $index => $snapshot)
                    <div x-show="active === {{ $index }}" style="display: none">
                        <p class="history-chart-date">{{ $snapshot->created_at->translatedFormat('j M Y') }} <span>{{ $snapshot->created_at->format('H:i') }}</span></p>
                        <dl>
                            @foreach($series as $seriesIndex => $line)
                                <div x-show="!hidden.includes({{ $seriesIndex }})">
                                    <dt><span class="history-chart-dot" style="background: {{ $line['color'] }}" aria-hidden="true"></span>{{ $line['label'] }}</dt>
                                    <dd>{{ $line['values'][$index] === null ? '—' : number_format($line['values'][$index], 2, ',', ' ').' '.__('kr') }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <button type="button" wire:click="selectSnapshot({{ $snapshot->id }})" x-bind:disabled="active !== {{ $index }}" class="history-chart-open">{{ __('View snapshot') }} <span aria-hidden="true">→</span></button>
                    </div>
                @endforeach
            </div>
        </div>
        @if($snapshots->count() === 1)
            <p class="history-chart-note">{{ __('Capture another snapshot to see the trend over time.') }}</p>
        @endif
    @endif
</section>
