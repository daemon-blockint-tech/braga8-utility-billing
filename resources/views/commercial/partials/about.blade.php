{{-- About Us --}}
<section id="about" class="py-28 lg:py-36">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">

        <div class="grid lg:grid-cols-2 gap-16 items-center">
            {{-- Left Image --}}
            <div class="relative reveal-up order-2 lg:order-1">
                <div class="rounded-xl3 overflow-hidden shadow-soft aspect-[4/5]">
                    <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=1200&auto=format&fit=crop"
                         alt="Fasad gedung Braga8 Commercial Center" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="absolute -bottom-8 -right-8 hidden sm:block bg-white rounded-2xl shadow-soft border border-black/5 p-5 w-48">
                    <p class="font-display text-lg font-semibold text-ink">Since 2012</p>
                    <p class="text-xs text-ink-faint mt-1">Melayani jantung kota Bandung selama lebih dari satu dekade.</p>
                </div>
            </div>

            {{-- Right Content --}}
            <div class="order-1 lg:order-2 reveal-up" style="transition-delay: 100ms;">
                <span class="text-xs font-semibold tracking-widest uppercase text-accent">About Braga8</span>
                <h2 class="font-display text-3xl lg:text-4xl font-medium text-ink mt-3 leading-tight">
                    Ruang komersial yang dirancang untuk bertumbuh bersama komunitas
                </h2>
                <p class="text-ink-soft mt-5 leading-relaxed">
                    Braga8 Commercial Center adalah kawasan perbelanjaan dan perkantoran terpadu di kawasan Braga, Bandung. Kami menghubungkan pelaku usaha dengan komunitas yang aktif dan loyal melalui ruang yang nyaman, modern, dan strategis.
                </p>

                <div class="grid sm:grid-cols-3 gap-4 mt-8">
                    <div class="rounded-2xl bg-warmgray/60 p-5">
                        <p class="font-semibold text-ink text-sm mb-1.5">Vision</p>
                        <p class="text-xs text-ink-soft leading-relaxed">Menjadi destinasi komersial paling dipercaya di Jawa Barat.</p>
                    </div>
                    <div class="rounded-2xl bg-warmgray/60 p-5">
                        <p class="font-semibold text-ink text-sm mb-1.5">Mission</p>
                        <p class="text-xs text-ink-soft leading-relaxed">Menghadirkan ruang usaha berkualitas dengan pengelolaan profesional.</p>
                    </div>
                    <div class="rounded-2xl bg-warmgray/60 p-5">
                        <p class="font-semibold text-ink text-sm mb-1.5">Core Values</p>
                        <p class="text-xs text-ink-soft leading-relaxed">Integritas, kolaborasi, dan kenyamanan bagi setiap pengunjung.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="mt-24 reveal-up">
            <h3 class="font-display text-2xl font-medium text-ink text-center mb-14">Perjalanan Kami</h3>
            <div class="relative">
                <div class="hidden lg:block absolute top-6 left-0 right-0 h-px bg-black/10"></div>
                <div class="grid lg:grid-cols-4 gap-10 lg:gap-6">
                    @foreach ([
                        ['year' => '2012', 'title' => 'Pembukaan Braga8', 'desc' => 'Braga8 resmi dibuka dengan 40 tenant pertama.'],
                        ['year' => '2016', 'title' => 'Ekspansi Fase 2', 'desc' => 'Penambahan area komersial seluas 8.000m².'],
                        ['year' => '2020', 'title' => 'Renovasi Premium', 'desc' => 'Pembaruan fasilitas dan konsep ruang modern.'],
                        ['year' => '2024', 'title' => '120+ Tenant Aktif', 'desc' => 'Okupansi mencapai 98% dengan tenant unggulan.'],
                    ] as $milestone)
                        <div class="relative text-center lg:text-left">
                            <div class="hidden lg:flex w-3 h-3 rounded-full bg-accent border-4 border-white shadow-softer mb-4"></div>
                            <p class="font-display text-xl font-semibold text-accent">{{ $milestone['year'] }}</p>
                            <p class="font-semibold text-ink text-sm mt-2">{{ $milestone['title'] }}</p>
                            <p class="text-xs text-ink-faint mt-1.5 leading-relaxed">{{ $milestone['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
