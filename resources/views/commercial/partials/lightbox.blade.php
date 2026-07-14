{{-- Lightbox --}}
<div id="lightbox" class="fixed inset-0 z-[200] hidden items-center justify-center p-6 bg-black/80 backdrop-blur-sm opacity-0 transition-opacity duration-300">
    <button id="lightbox-close" aria-label="Tutup" class="absolute top-6 right-6 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>
    <img id="lightbox-img" src="" alt="Gallery preview" class="max-h-[85vh] max-w-[90vw] rounded-2xl shadow-2xl scale-95 transition-transform duration-300">
</div>
