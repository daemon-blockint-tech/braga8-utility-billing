{{-- Tenant Directory --}}
@php
    $tenants = [
        ['name' => 'Kopi Braga', 'category' => 'Food & Beverage', 'floor' => 'Ground Floor', 'unit' => 'GF-12', 'hours' => '08:00 - 22:00', 'initial' => 'KB'],
        ['name' => 'Sarrasa Fashion', 'category' => 'Fashion', 'floor' => '1st Floor', 'unit' => '1F-05', 'hours' => '10:00 - 21:00', 'initial' => 'SF'],
        ['name' => 'Digitech Store', 'category' => 'Electronics', 'floor' => '2nd Floor', 'unit' => '2F-08', 'hours' => '10:00 - 21:00', 'initial' => 'DS'],
        ['name' => 'Glow Beauty Bar', 'category' => 'Beauty', 'floor' => '1st Floor', 'unit' => '1F-14', 'hours' => '10:00 - 21:00', 'initial' => 'GB'],
        ['name' => 'Cinepolis Braga8', 'category' => 'Entertainment', 'floor' => '3rd Floor', 'unit' => '3F-01', 'hours' => '11:00 - 23:00', 'initial' => 'CB'],
        ['name' => 'Braga Laundry Express', 'category' => 'Services', 'floor' => 'Ground Floor', 'unit' => 'GF-22', 'hours' => '08:00 - 20:00', 'initial' => 'BL'],
        ['name' => 'Rasa Nusantara', 'category' => 'Food & Beverage', 'floor' => 'Ground Floor', 'unit' => 'GF-03', 'hours' => '09:00 - 22:00', 'initial' => 'RN'],
        ['name' => 'Urban Denim Co.', 'category' => 'Fashion', 'floor' => '1st Floor', 'unit' => '1F-19', 'hours' => '10:00 - 21:00', 'initial' => 'UD'],
        ['name' => 'GameHub Arena', 'category' => 'Entertainment', 'floor' => '3rd Floor', 'unit' => '3F-06', 'hours' => '10:00 - 22:00', 'initial' => 'GH'],
    ];
    $categories = ['All', 'Food & Beverage', 'Fashion', 'Electronics', 'Beauty', 'Entertainment', 'Services'];
    $floors = ['All Floors', 'Ground Floor', '1st Floor', '2nd Floor', '3rd Floor'];
@endphp

<section id="directory" class="py-28 lg:py-36">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="max-w-xl reveal-up">
            <span class="text-xs font-semibold tracking-widest uppercase text-accent">Tenant Directory</span>
            <h2 class="font-display text-3xl lg:text-4xl font-medium text-ink mt-3">Temukan tenant favorit Anda</h2>
        </div>

        {{-- Search & Filters --}}
        <div class="mt-10 flex flex-col lg:flex-row gap-4 reveal-up">
            <div class="relative flex-1">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-faint" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input id="tenant-search" type="text" placeholder="Cari nama tenant..."
                       class="w-full pl-11 pr-4 py-3.5 rounded-full bg-white border border-black/10 text-sm focus:outline-none focus:border-accent focus:ring-4 focus:ring-accent-soft transition">
            </div>
            <select id="category-filter" class="rounded-full bg-white border border-black/10 text-sm px-5 py-3.5 focus:outline-none focus:border-accent">
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat === 'All' ? 'All Categories' : $cat }}</option>
                @endforeach
            </select>
            <select id="floor-filter" class="rounded-full bg-white border border-black/10 text-sm px-5 py-3.5 focus:outline-none focus:border-accent">
                @foreach ($floors as $floor)
                    <option value="{{ $floor }}">{{ $floor }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tenant Grid --}}
        <div id="tenant-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-10">
            @foreach ($tenants as $tenant)
                <div class="tenant-card rounded-2xl bg-white border border-black/5 p-6 hover:-translate-y-1.5 hover:shadow-soft transition-all duration-300"
                     data-name="{{ strtolower($tenant['name']) }}" data-category="{{ $tenant['category'] }}" data-floor="{{ $tenant['floor'] }}">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent to-accent-light flex items-center justify-center text-white font-semibold text-sm shrink-0">
                            {{ $tenant['initial'] }}
                        </div>
                        <div>
                            <p class="font-semibold text-ink text-sm">{{ $tenant['name'] }}</p>
                            <p class="text-xs text-accent-dark mt-0.5">{{ $tenant['category'] }}</p>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-black/5 grid grid-cols-2 gap-3 text-xs text-ink-faint">
                        <div>
                            <p class="uppercase tracking-wide text-[10px] mb-1">Floor</p>
                            <p class="text-ink-soft font-medium">{{ $tenant['floor'] }}</p>
                        </div>
                        <div>
                            <p class="uppercase tracking-wide text-[10px] mb-1">Unit</p>
                            <p class="text-ink-soft font-medium">{{ $tenant['unit'] }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="uppercase tracking-wide text-[10px] mb-1">Opening Hours</p>
                            <p class="text-ink-soft font-medium">{{ $tenant['hours'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <p id="no-results" class="hidden text-center text-sm text-ink-faint mt-10">Tidak ada tenant yang cocok dengan pencarian Anda.</p>
    </div>
</section>
