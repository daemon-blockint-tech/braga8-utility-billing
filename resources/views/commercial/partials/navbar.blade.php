{{-- Navbar --}}
<header id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div id="navbar-inner" class="flex items-center justify-between mt-4 rounded-full bg-white/70 backdrop-blur-xl border border-black/5 shadow-softer px-5 py-3 transition-all duration-300">

            {{-- Logo --}}
            <a href="{{ url('/') }}#home" class="flex items-center gap-2 shrink-0">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-accent to-accent-light flex items-center justify-center text-white font-display font-semibold text-lg">8</span>
                <span class="font-display text-lg font-semibold tracking-tight text-ink">Braga<span class="text-accent">8</span></span>
            </a>

            {{-- Desktop Menu --}}
            <nav class="hidden lg:flex items-center gap-1">
                @foreach ([
                    ['label' => 'Home', 'href' => '#home'],
                    ['label' => 'About', 'href' => '#about'],
                    ['label' => 'Facilities', 'href' => '#facilities'],
                    ['label' => 'Directory', 'href' => '#directory'],
                    ['label' => 'Leasing', 'href' => '#leasing'],
                    ['label' => 'Events', 'href' => '#events'],
                    ['label' => 'Gallery', 'href' => '#gallery'],
                    ['label' => 'Contact', 'href' => '#contact'],
                ] as $item)
                    <a href="{{ $item['href'] }}" class="nav-link px-4 py-2 text-sm font-medium text-ink-soft hover:text-accent rounded-full transition-colors duration-200">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- CTA --}}
            <div class="hidden lg:block">
                <a href="#leasing" class="btn-gradient inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white shadow-softer">
                    Become a Tenant
                </a>
            </div>

            {{-- Mobile Toggle --}}
            <button id="mobile-menu-btn" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-full hover:bg-warmgray transition" aria-label="Buka menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="lg:hidden max-h-0 overflow-hidden transition-all duration-300 ease-in-out mt-2">
            <div class="rounded-3xl bg-white shadow-soft border border-black/5 p-5 flex flex-col gap-1">
                @foreach ([
                    ['label' => 'Home', 'href' => '#home'],
                    ['label' => 'About', 'href' => '#about'],
                    ['label' => 'Facilities', 'href' => '#facilities'],
                    ['label' => 'Directory', 'href' => '#directory'],
                    ['label' => 'Leasing', 'href' => '#leasing'],
                    ['label' => 'Events', 'href' => '#events'],
                    ['label' => 'Gallery', 'href' => '#gallery'],
                    ['label' => 'Contact', 'href' => '#contact'],
                ] as $item)
                    <a href="{{ $item['href'] }}" class="mobile-nav-link px-4 py-3 rounded-xl text-ink-soft hover:bg-warmgray hover:text-accent font-medium transition">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="#leasing" class="mobile-nav-link mt-2 text-center btn-gradient rounded-full px-5 py-3 text-sm font-semibold text-white">
                    Become a Tenant
                </a>
            </div>
        </div>
    </div>
</header>
