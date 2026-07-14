{{-- Events --}}
@php
    $events = [
        ['title' => 'New Tenant Opening: Digitech Store', 'date' => '18 Jul 2026', 'desc' => 'Grand opening tenant elektronik terbaru dengan promo spesial.', 'img' => 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?q=80&w=800&auto=format&fit=crop'],
        ['title' => 'Independence Festival', 'date' => '15-17 Aug 2026', 'desc' => 'Perayaan kemerdekaan dengan bazar UMKM dan panggung hiburan.', 'img' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?q=80&w=800&auto=format&fit=crop'],
        ['title' => 'Ramadan Bazaar', 'date' => 'Mar 2027', 'desc' => 'Bazar takjil dan produk lokal setiap sore selama bulan Ramadan.', 'img' => 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?q=80&w=800&auto=format&fit=crop'],
        ['title' => 'Weekend Live Music', 'date' => 'Setiap Sabtu', 'desc' => 'Penampilan musisi lokal di atrium utama setiap akhir pekan.', 'img' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?q=80&w=800&auto=format&fit=crop'],
    ];
@endphp

<section id="events" class="py-28 lg:py-36">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-xl reveal-up">
            <span class="text-xs font-semibold tracking-widest uppercase text-accent">Events</span>
            <h2 class="font-display text-3xl lg:text-4xl font-medium text-ink mt-3">Selalu ada aktivitas seru di Braga8</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-14">
            @foreach ($events as $event)
                <div class="event-card reveal-up rounded-2xl bg-white border border-black/5 overflow-hidden hover:-translate-y-1.5 hover:shadow-soft transition-all duration-300">
                    <div class="aspect-[4/3] overflow-hidden">
                        <img src="{{ $event['img'] }}" alt="{{ $event['title'] }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500" loading="lazy">
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-semibold text-accent">{{ $event['date'] }}</p>
                        <p class="font-semibold text-ink text-sm mt-2 leading-snug">{{ $event['title'] }}</p>
                        <p class="text-xs text-ink-faint mt-2 leading-relaxed">{{ $event['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
