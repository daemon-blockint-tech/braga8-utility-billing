{{-- Contact --}}
<section id="contact" class="py-28 lg:py-36">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-xl reveal-up">
            <span class="text-xs font-semibold tracking-widest uppercase text-accent">Contact</span>
            <h2 class="font-display text-3xl lg:text-4xl font-medium text-ink mt-3">Mari terhubung dengan kami</h2>
        </div>

        <div class="grid lg:grid-cols-2 gap-14 mt-14">
            {{-- Info --}}
            <div class="space-y-4 reveal-up">
                @foreach ([
                    ['label' => 'Phone', 'value' => '+62 22 4234 5678', 'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                    ['label' => 'Email', 'value' => 'info@braga8.com', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['label' => 'WhatsApp', 'value' => '+62 812 3456 7890', 'icon' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01M4.929 12.929a9 9 0 0114.142 0'],
                ] as $c)
                    <div class="flex items-center gap-4 p-5 rounded-2xl bg-warmgray/60">
                        <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}" /></svg>
                        </div>
                        <div>
                            <p class="text-xs text-ink-faint">{{ $c['label'] }}</p>
                            <p class="text-sm font-semibold text-ink mt-0.5">{{ $c['value'] }}</p>
                        </div>
                    </div>
                @endforeach

                <div class="flex gap-3 pt-2">
                    @foreach (['Instagram', 'Facebook', 'TikTok'] as $social)
                        <a href="#" class="w-11 h-11 rounded-full bg-ink text-white flex items-center justify-center text-xs font-semibold hover:bg-accent transition">
                            {{ substr($social, 0, 1) }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Form --}}
            <div class="reveal-up rounded-xl3 bg-white p-8 shadow-soft border border-black/5" style="transition-delay: 100ms;">
                <form class="space-y-4" onsubmit="return false;">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <input type="text" placeholder="Nama Lengkap" required class="form-input">
                        <input type="email" placeholder="Email" required class="form-input">
                    </div>
                    <input type="text" placeholder="Subjek" class="form-input">
                    <textarea placeholder="Pesan Anda" rows="4" required class="form-input resize-none"></textarea>
                    <button type="submit" class="btn-gradient w-full rounded-full py-3.5 text-sm font-semibold text-white shadow-softer">
                        Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
