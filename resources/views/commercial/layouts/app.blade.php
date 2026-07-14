<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Braga8 Commercial Center — Modern Commercial Destination')</title>
    <meta name="description" content="@yield('meta_description', 'Braga8 Commercial Center — destinasi bisnis dan gaya hidup modern. Temukan tenant, fasilitas premium, dan peluang sewa terbaik di Braga8.')">

    {{-- SEO --}}
    <meta property="og:title" content="Braga8 Commercial Center">
    <meta property="og:description" content="Destinasi komersial modern untuk bisnis & gaya hidup.">
    <meta property="og:type" content="website">
    <link rel="icon" href="{{ asset('assets/favicon.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&display=swap" rel="stylesheet">

    {{-- Tailwind (CDN for zero-build setup — swap for Vite + tailwind.config.js in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: {
                            DEFAULT: '#E56A00',
                            light: '#FF8A2E',
                            soft: '#FDEDE0',
                            dark: '#B85400',
                        },
                        ink: {
                            DEFAULT: '#141414',
                            soft: '#4B4B4B',
                            faint: '#8A8A8A',
                        },
                        warmgray: '#F2EFEA',
                        surface: '#FAFAFA',
                    },
                    fontFamily: {
                        display: ['Fraunces', 'serif'],
                        sans: ['Manrope', 'sans-serif'],
                    },
                    borderRadius: {
                        'xl2': '1.75rem',
                        'xl3': '2rem',
                    },
                    boxShadow: {
                        'soft': '0 10px 40px -12px rgba(20,20,20,0.10)',
                        'softer': '0 4px 20px -6px rgba(20,20,20,0.08)',
                        'lift': '0 24px 60px -16px rgba(229,106,0,0.25)',
                    },
                    keyframes: {
                        blob: {
                            '0%, 100%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -40px) scale(1.08)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.95)' },
                        },
                        floaty: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-14px)' },
                        }
                    },
                    animation: {
                        blob: 'blob 14s infinite ease-in-out',
                        floaty: 'floaty 5s infinite ease-in-out',
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="{{ asset('css/commercial-styling.css') }}">
</head>
<body class="bg-surface text-ink font-sans antialiased">

    {{-- Loading Screen --}}
    <div id="loading-screen" class="fixed inset-0 z-[999] bg-surface flex items-center justify-center">
        <div class="flex flex-col items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-accent to-accent-light flex items-center justify-center animate-floaty shadow-lift">
                <span class="text-white font-display text-2xl font-semibold">8</span>
            </div>
            <div class="w-32 h-1 rounded-full bg-warmgray overflow-hidden">
                <div id="loading-bar" class="h-full w-0 bg-accent rounded-full transition-all duration-700 ease-out"></div>
            </div>
        </div>
    </div>

    {{-- Scroll Progress Indicator --}}
    <div id="scroll-progress" class="fixed top-0 left-0 h-[3px] bg-gradient-to-r from-accent to-accent-light z-[100] w-0"></div>

@include('commercial.partials.navbar')

    <main>
        @yield('content')
    </main>

@include('commercial.partials.footer')

    {{-- Back to Top --}}
    <button id="back-to-top" aria-label="Kembali ke atas" class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full bg-ink text-white shadow-lift flex items-center justify-center opacity-0 pointer-events-none translate-y-4 transition-all duration-300 hover:bg-accent">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    </button>

    {{-- Lightbox --}}
@include('commercial.partials.lightbox')

    <script src="{{ asset('js/commercial-script.js') }}"></script>
    @stack('scripts')
</body>
</html>
