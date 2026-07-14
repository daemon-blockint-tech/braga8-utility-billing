{{-- Facilities --}}
<section id="facilities" class="py-28 lg:py-36 bg-warmgray/40">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-xl mx-auto text-center reveal-up">
            <span class="text-xs font-semibold tracking-widest uppercase text-accent">Facilities</span>
            <h2 class="font-display text-3xl lg:text-4xl font-medium text-ink mt-3">Fasilitas lengkap untuk kenyamanan Anda</h2>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-5 mt-14">
            @foreach ([
                ['label' => 'Parking', 'icon' => 'M8 17V9a2 2 0 012-2h4a2 2 0 012 2v8m-8 0h8m-8 0H6a2 2 0 01-2-2v-1a2 2 0 012-2h.5m11.5 5h1a2 2 0 002-2v-1a2 2 0 00-2-2h-.5'],
                ['label' => '24H Security', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['label' => 'CCTV Coverage', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
                ['label' => 'Musholla', 'icon' => 'M12 2l3 6 6 1-4.5 4.5L18 20l-6-3-6 3 1.5-6.5L3 9l6-1 3-6z'],
                ['label' => 'ATM Center', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['label' => 'Free WiFi', 'icon' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01M4.929 12.929a9 9 0 0114.142 0'],
                ['label' => 'Escalator', 'icon' => 'M3 21h18M6 21V9l6-6 6 6v12M9 21v-6h6v6'],
                ['label' => 'Elevator', 'icon' => 'M8 7l4-4 4 4M8 17l4 4 4-4M12 3v18'],
                ['label' => 'Generator Backup', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['label' => 'Public Toilets', 'icon' => 'M5 3v18M19 3v18M5 12h14M9 7h2v2H9V7zm4 10h2v2h-2v-2z'],
            ] as $facility)
                <div class="facility-card group rounded-2xl bg-white border border-black/5 p-6 text-center hover:-translate-y-1.5 hover:shadow-soft transition-all duration-300 cursor-default">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-accent-soft flex items-center justify-center group-hover:bg-accent transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-accent group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $facility['icon'] }}" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-ink mt-4">{{ $facility['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
