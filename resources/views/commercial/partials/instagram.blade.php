{{-- Instagram Preview --}}
@php
    $instaImages = [
        'https://images.unsplash.com/photo-1555529771-122e5d9f2341?q=80&w=500&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1519677100203-a0e668c92439?q=80&w=500&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?q=80&w=500&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?q=80&w=500&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1519567241046-7f570eee3ce6?q=80&w=500&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=500&auto=format&fit=crop',
    ];
@endphp

<section class="py-20 lg:py-28 bg-warmgray/40">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 text-center reveal-up">
        <span class="text-xs font-semibold tracking-widest uppercase text-accent">Follow Us</span>
        <h2 class="font-display text-2xl lg:text-3xl font-medium text-ink mt-3">@braga8commercialcenter</h2>

        <div class="grid grid-cols-3 lg:grid-cols-6 gap-3 mt-10">
            @foreach ($instaImages as $img)
                <a href="https://instagram.com" target="_blank" rel="noopener" class="group relative block aspect-square rounded-2xl overflow-hidden">
                    <img src="{{ $img }}" alt="Instagram Braga8" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-300 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.332.014 7.052.072 2.695.272.273 2.69.073 7.052.014 8.332 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.332 23.986 8.741 24 12 24s3.668-.014 4.948-.072c4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.668-.072-4.948C23.73 2.7 21.308.273 16.949.073 15.668.014 15.259 0 12 0z"/><path d="M12 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8z"/><circle cx="18.406" cy="5.594" r="1.44"/></svg>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
