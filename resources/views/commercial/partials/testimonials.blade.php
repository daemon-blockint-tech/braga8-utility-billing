{{-- Testimonials --}}
@php
    $testimonials = [
        ['name' => 'Dewi Anggraini', 'company' => 'Kopi Braga', 'review' => 'Sejak buka di Braga8, traffic pelanggan kami naik signifikan. Manajemen mall sangat suportif terhadap tenant.', 'initial' => 'DA'],
        ['name' => 'Rendra Wijaya', 'company' => 'Urban Denim Co.', 'review' => 'Lokasinya strategis dan fasilitasnya lengkap. Proses sewa juga sangat transparan dan profesional.', 'initial' => 'RW'],
        ['name' => 'Melissa Tanoto', 'company' => 'Glow Beauty Bar', 'review' => 'Pengelolaan gedung rapi, keamanan terjamin, dan komunitas tenant di sini sangat suportif satu sama lain.', 'initial' => 'MT'],
    ];
@endphp

<section class="py-28 lg:py-36">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-xl mx-auto text-center reveal-up">
            <span class="text-xs font-semibold tracking-widest uppercase text-accent">Testimonials</span>
            <h2 class="font-display text-3xl lg:text-4xl font-medium text-ink mt-3">Apa kata para tenant kami</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-6 mt-14">
            @foreach ($testimonials as $t)
                <div class="reveal-up rounded-2xl bg-white border border-black/5 shadow-softer p-7 hover:-translate-y-1.5 transition-transform duration-300" style="transition-delay: {{ $loop->index * 80 }}ms;">
                    <div class="flex gap-1 text-accent mb-4">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.448a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.062 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.958z"/></svg>
                        @endfor
                    </div>
                    <p class="text-sm text-ink-soft leading-relaxed">"{{ $t['review'] }}"</p>
                    <div class="flex items-center gap-3 mt-6 pt-6 border-t border-black/5">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-accent to-accent-light flex items-center justify-center text-white text-xs font-semibold">
                            {{ $t['initial'] }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ $t['name'] }}</p>
                            <p class="text-xs text-ink-faint">{{ $t['company'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
