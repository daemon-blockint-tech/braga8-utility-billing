{{-- Statistics Section --}}
<section class="relative -mt-8 z-10">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-5 reveal-up">
            @foreach ([
                ['value' => 120, 'suffix' => '+', 'label' => 'Tenants'],
                ['value' => 25000, 'suffix' => 'm²', 'label' => 'Commercial Area'],
                ['value' => 500, 'suffix' => '+', 'label' => 'Parking Spaces'],
                ['value' => 98, 'suffix' => '%', 'label' => 'Occupancy Rate'],
                ['value' => 2012, 'suffix' => '', 'label' => 'Open Since', 'static' => true],
            ] as $stat)
                <div class="rounded-2xl bg-white border border-black/5 shadow-softer px-5 py-7 text-center hover:-translate-y-1.5 transition-transform duration-300">
                    <p class="font-display text-2xl lg:text-3xl font-semibold text-ink">
                        @if(!empty($stat['static']))
                            {{ $stat['value'] }}
                        @else
                            <span class="counter" data-target="{{ $stat['value'] }}">0</span>{{ $stat['suffix'] }}
                        @endif
                    </p>
                    <p class="text-xs text-ink-faint mt-2 tracking-wide">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
