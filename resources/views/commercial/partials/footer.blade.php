{{-- Footer --}}
<footer class="bg-ink pt-20 pb-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div>
                <a href="#home" class="flex items-center gap-2 mb-4">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-accent to-accent-light flex items-center justify-center text-white font-display font-semibold text-lg">8</span>
                    <span class="font-display text-lg font-semibold text-white">Braga<span class="text-accent-light">8</span></span>
                </a>
                <p class="text-white/50 text-sm leading-relaxed max-w-xs">Destinasi komersial modern di jantung kota Bandung untuk bisnis dan gaya hidup.</p>
            </div>

            <div>
                <p class="text-white text-sm font-semibold mb-4">Quick Links</p>
                <ul class="space-y-2.5 text-sm text-white/50">
                    <li><a href="#about" class="hover:text-accent-light transition">About Us</a></li>
                    <li><a href="#directory" class="hover:text-accent-light transition">Tenant Directory</a></li>
                    <li><a href="#leasing" class="hover:text-accent-light transition">Leasing</a></li>
                    <li><a href="#events" class="hover:text-accent-light transition">Events</a></li>
                    <li><a href="#gallery" class="hover:text-accent-light transition">Gallery</a></li>
                </ul>
            </div>

            <div>
                <p class="text-white text-sm font-semibold mb-4">Contact</p>
                <ul class="space-y-2.5 text-sm text-white/50">
                    <li>Jl. Braga No. 8, Bandung</li>
                    <li>+62 22 4234 5678</li>
                    <li>info@braga8.com</li>
                </ul>
            </div>

            <div>
                <p class="text-white text-sm font-semibold mb-4">Follow Us</p>
                <div class="flex gap-3">
                    @foreach (['Instagram', 'Facebook', 'TikTok'] as $social)
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 text-white flex items-center justify-center text-xs font-semibold hover:bg-accent transition">
                            {{ substr($social, 0, 1) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 mt-14 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-white/40 text-xs">&copy; {{ date('Y') }} Braga8 Commercial Center. All rights reserved.</p>
            <div class="flex gap-6 text-xs text-white/40">
                <a href="#" class="hover:text-white transition">Privacy Policy</a>
                <a href="#" class="hover:text-white transition">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
