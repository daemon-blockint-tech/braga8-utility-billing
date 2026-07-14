{{-- Hero Section --}}
<section id="home" class="relative overflow-hidden pt-40 pb-24 lg:pt-48 lg:pb-32">

    {{-- Abstract Blobs --}}
    <div class="absolute -top-20 -right-32 w-[32rem] h-[32rem] bg-accent/20 rounded-full blur-3xl animate-blob"></div>
    <div class="absolute top-40 -left-40 w-96 h-96 bg-accent-light/20 rounded-full blur-3xl animate-blob" style="animation-delay: -6s;"></div>

    <div class="relative max-w-7xl mx-auto px-6 lg:px-10 grid lg:grid-cols-2 gap-16 items-center">

        {{-- Left --}}
        <div class="reveal-up">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-accent-soft text-accent-dark text-xs font-semibold tracking-wide uppercase mb-6">
                Braga, Bandung
            </span>
            <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.4rem] leading-[1.08] font-medium text-ink tracking-tight">
                Modern Commercial Destination for
                <span class="relative inline-block text-accent">Business
                    <svg class="absolute left-0 -bottom-2 w-full" height="10" viewBox="0 0 200 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 8C40 2 160 2 198 8" stroke="#E56A00" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </span>
                &amp; Lifestyle
            </h1>
            <p class="mt-6 text-ink-soft text-lg leading-relaxed max-w-lg">
                Braga8 menghadirkan pengalaman berbelanja, bekerja, dan berkumpul dalam satu kawasan komersial premium — dirancang untuk mempertemukan bisnis terbaik dengan pengunjung yang tepat.
            </p>
            <div class="mt-9 flex flex-wrap items-center gap-4">
                <a href="#facilities" class="btn-gradient rounded-full px-7 py-3.5 text-sm font-semibold text-white shadow-lift inline-flex items-center gap-2">
                    Explore Mall
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
                <a href="#leasing" class="rounded-full px-7 py-3.5 text-sm font-semibold text-ink border border-black/10 bg-white hover:border-accent hover:text-accent transition-colors duration-200">
                    Leasing Information
                </a>
            </div>

            <div class="mt-12 flex items-center gap-8">
                <div>
                    <p class="font-display text-2xl font-semibold text-ink">120+</p>
                    <p class="text-xs text-ink-faint mt-1">Tenant Terpercaya</p>
                </div>
                <div class="w-px h-10 bg-black/10"></div>
                <div>
                    <p class="font-display text-2xl font-semibold text-ink">98%</p>
                    <p class="text-xs text-ink-faint mt-1">Tingkat Okupansi</p>
                </div>
                <div class="w-px h-10 bg-black/10"></div>
                <div>
                    <p class="font-display text-2xl font-semibold text-ink">4.8/5</p>
                    <p class="text-xs text-ink-faint mt-1">Rating Pengunjung</p>
                </div>
            </div>
        </div>

        {{-- Right --}}
        <div class="relative reveal-up" style="transition-delay: 120ms;">
            <div class="relative rounded-xl3 overflow-hidden shadow-lift aspect-[4/5] lg:aspect-[4/5] parallax-img">
                <img src="https://images.unsplash.com/photo-1555529771-122e5d9f2341?q=80&w=1200&auto=format&fit=crop"
                     alt="Suasana interior Braga8 Commercial Center"
                     class="w-full h-full object-cover scale-110" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
            </div>

            {{-- Floating Card 1 --}}
            <div class="absolute -left-8 bottom-10 bg-white/90 backdrop-blur-xl rounded-2xl shadow-soft border border-black/5 px-5 py-4 flex items-center gap-3 animate-floaty">
                <div class="w-11 h-11 rounded-xl bg-accent-soft flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">500+ Parking</p>
                    <p class="text-xs text-ink-faint">Aman & luas</p>
                </div>
            </div>

            {{-- Floating Card 2 --}}
            <div class="absolute -right-6 top-10 bg-white/90 backdrop-blur-xl rounded-2xl shadow-soft border border-black/5 px-5 py-4 animate-floaty" style="animation-delay: -2.5s;">
                <p class="text-xs text-ink-faint mb-1">Occupancy Rate</p>
                <p class="font-display text-xl font-semibold text-accent">98%</p>
            </div>
        </div>
    </div>
</section>
