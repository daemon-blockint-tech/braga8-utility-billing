{{-- Gallery --}}
@php
    $galleryImages = [
        ['src' => 'https://images.unsplash.com/photo-1555529771-122e5d9f2341?q=80&w=800&auto=format&fit=crop', 'cat' => 'Interior', 'h' => 'h-64'],
        ['src' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=800&auto=format&fit=crop', 'cat' => 'Exterior', 'h' => 'h-80'],
        ['src' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?q=80&w=800&auto=format&fit=crop', 'cat' => 'Events', 'h' => 'h-56'],
        ['src' => 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?q=80&w=800&auto=format&fit=crop', 'cat' => 'Tenants', 'h' => 'h-72'],
        ['src' => 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?q=80&w=800&auto=format&fit=crop', 'cat' => 'Facilities', 'h' => 'h-60'],
        ['src' => 'https://images.unsplash.com/photo-1519567241046-7f570eee3ce6?q=80&w=800&auto=format&fit=crop', 'cat' => 'Interior', 'h' => 'h-72'],
        ['src' => 'https://images.unsplash.com/photo-1481437156560-3205f6a55735?q=80&w=800&auto=format&fit=crop', 'cat' => 'Exterior', 'h' => 'h-56'],
        ['src' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?q=80&w=800&auto=format&fit=crop', 'cat' => 'Events', 'h' => 'h-80'],
    ];
    $galleryCats = ['All', 'Exterior', 'Interior', 'Events', 'Tenants', 'Facilities'];
@endphp

<section id="gallery" class="py-28 lg:py-36 bg-warmgray/40">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 reveal-up">
            <div class="max-w-xl">
                <span class="text-xs font-semibold tracking-widest uppercase text-accent">Gallery</span>
                <h2 class="font-display text-3xl lg:text-4xl font-medium text-ink mt-3">Momen di Braga8</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($galleryCats as $cat)
                    <button class="gallery-filter-btn px-4 py-2 rounded-full text-xs font-medium border border-black/10 bg-white hover:border-accent hover:text-accent transition {{ $loop->first ? 'is-active bg-accent text-white border-accent' : 'text-ink-soft' }}"
                            data-cat="{{ $cat }}">{{ $cat }}</button>
                @endforeach
            </div>
        </div>

        <div id="gallery-grid" class="columns-2 sm:columns-3 lg:columns-4 gap-4 mt-12 [column-fill:_balance]">
            @foreach ($galleryImages as $img)
                <button class="gallery-item block w-full mb-4 rounded-2xl overflow-hidden break-inside-avoid group relative" data-cat="{{ $img['cat'] }}" data-src="{{ $img['src'] }}">
                    <img src="{{ $img['src'] }}" alt="{{ $img['cat'] }} Braga8" class="w-full {{ $img['h'] }} object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-300 flex items-end p-4 opacity-0 group-hover:opacity-100">
                        <span class="text-white text-xs font-semibold">{{ $img['cat'] }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</section>
