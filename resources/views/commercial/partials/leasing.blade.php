{{-- Leasing --}}
<section id="leasing" class="py-28 lg:py-36 bg-ink relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[28rem] h-[28rem] bg-accent/20 rounded-full blur-3xl animate-blob"></div>

    <div class="relative max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-xl reveal-up">
            <span class="text-xs font-semibold tracking-widest uppercase text-accent-light">Leasing</span>
            <h2 class="font-display text-3xl lg:text-4xl font-medium text-white mt-3">Mengapa memilih Braga8?</h2>
            <p class="text-white/60 mt-4 leading-relaxed">Bergabunglah dengan 120+ bisnis yang telah mempercayai Braga8 sebagai rumah bagi usaha mereka.</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-14 mt-14">
            {{-- Benefits + Available Spaces --}}
            <div class="space-y-5 reveal-up">
                @foreach ([
                    ['title' => 'Strategic Location', 'desc' => 'Berada di jantung kawasan Braga, Bandung, dengan akses mudah dari segala arah.'],
                    ['title' => 'High Visitor Traffic', 'desc' => 'Rata-rata 15.000+ pengunjung setiap akhir pekan.'],
                    ['title' => 'Modern Facilities', 'desc' => 'Infrastruktur premium dengan standar keamanan dan kenyamanan tinggi.'],
                    ['title' => 'Professional Management', 'desc' => 'Dikelola oleh tim berpengalaman lebih dari 12 tahun di bidang properti komersial.'],
                ] as $benefit)
                    <div class="flex gap-4 p-5 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/[0.08] transition-colors duration-300">
                        <div class="w-10 h-10 shrink-0 rounded-xl bg-accent/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-accent-light" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">{{ $benefit['title'] }}</p>
                            <p class="text-white/50 text-xs mt-1 leading-relaxed">{{ $benefit['desc'] }}</p>
                        </div>
                    </div>
                @endforeach

                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="#" class="rounded-full bg-white/10 border border-white/20 text-white text-sm font-medium px-6 py-3 hover:bg-white/20 transition inline-flex items-center gap-2">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" /></svg>
                        Download Brochure
                    </a>
                    <a href="https://wa.me/6281234567890" class="rounded-full bg-accent text-white text-sm font-medium px-6 py-3 hover:bg-accent-dark transition inline-flex items-center gap-2">
                        Contact Leasing Manager
                    </a>
                </div>
            </div>

            {{-- Inquiry Form --}}
            <div class="reveal-up rounded-xl3 bg-white p-8 shadow-lift" style="transition-delay: 100ms;">
                <h3 class="font-display text-xl font-semibold text-ink mb-1">Ajukan Pertanyaan Sewa</h3>
                <p class="text-xs text-ink-faint mb-6">Tim leasing kami akan menghubungi Anda dalam 1x24 jam.</p>
                <form class="space-y-4" onsubmit="return false;">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <input type="text" placeholder="Nama Lengkap" required class="form-input">
                        <input type="tel" placeholder="Nomor Telepon" required class="form-input">
                    </div>
                    <input type="email" placeholder="Email" required class="form-input">
                    <input type="text" placeholder="Nama Bisnis" class="form-input">
                    <select class="form-input text-ink-soft">
                        <option>Tipe unit yang diminati</option>
                        <option>Kios Ground Floor</option>
                        <option>Ruang Retail Lantai 1-2</option>
                        <option>Ruang Perkantoran</option>
                        <option>Anchor Tenant Space</option>
                    </select>
                    <textarea placeholder="Pesan tambahan" rows="3" class="form-input resize-none"></textarea>
                    <button type="submit" class="btn-gradient w-full rounded-full py-3.5 text-sm font-semibold text-white shadow-softer">
                        Kirim Pengajuan
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
