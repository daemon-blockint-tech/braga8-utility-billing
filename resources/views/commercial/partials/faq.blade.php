{{-- FAQ Accordion --}}
@php
    $faqs = [
        ['q' => 'Bagaimana cara mengajukan sewa unit di Braga8?', 'a' => 'Isi formulir pada bagian Leasing, atau hubungi Leasing Manager kami langsung melalui WhatsApp. Tim kami akan menghubungi Anda dalam 1x24 jam untuk penjadwalan survei lokasi.'],
        ['q' => 'Apa saja tipe unit yang tersedia?', 'a' => 'Tersedia kios ground floor, ruang retail lantai 1-2, ruang perkantoran, hingga anchor tenant space dengan berbagai ukuran mulai dari 12m².'],
        ['q' => 'Apakah tersedia area parkir yang memadai?', 'a' => 'Ya, Braga8 memiliki lebih dari 500 slot parkir mobil dan motor dengan sistem keamanan CCTV 24 jam.'],
        ['q' => 'Bagaimana jam operasional mall?', 'a' => 'Braga8 buka setiap hari pukul 10:00 - 22:00 WIB, termasuk hari libur nasional.'],
    ];
@endphp

<section class="py-28 lg:py-36">
    <div class="max-w-3xl mx-auto px-6 lg:px-10">
        <div class="text-center reveal-up">
            <span class="text-xs font-semibold tracking-widest uppercase text-accent">FAQ</span>
            <h2 class="font-display text-3xl lg:text-4xl font-medium text-ink mt-3">Pertanyaan yang sering diajukan</h2>
        </div>

        <div class="mt-12 space-y-3 reveal-up">
            @foreach ($faqs as $faq)
                <div class="faq-item rounded-2xl bg-white border border-black/5 overflow-hidden">
                    <button class="faq-toggle w-full flex items-center justify-between gap-4 px-6 py-5 text-left">
                        <span class="font-medium text-sm text-ink">{{ $faq['q'] }}</span>
                        <svg class="faq-icon w-5 h-5 text-accent shrink-0 transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                        <p class="px-6 pb-5 text-sm text-ink-faint leading-relaxed">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
