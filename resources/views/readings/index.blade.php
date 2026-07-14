@extends('layouts.app') 

@section('content') 
<div class="min-h-screen"> 
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8"> 
        <div> 
            <h1 class="title-text">Catatan Meteran</h1> 
            <p class="subtitle-text">Manajemen Penagihan Utilitas Braga8</p> 
        </div> 
        <div class="header-user"> 
            <div class="icon-wrapper" data-popup="notif-popup"> 
                <i class="fa-solid fa-bell"></i> 
                @if(auth()->user()->customNotifications()->whereNull('read_at')->exists()) 
                    <span class="notif-dot"></span> 
                @endif 
            </div> 
            <div class="profile-container" data-popup="detail-profile-popup"> 
                <div class="profile-icon"> 
                    <i class="fa-solid fa-user text-2xl text-[#a04d30]"></i> 
                </div> 
            </div> 
        </div> 
    </div> 

    <div class="flex flex-col gap-6"> 
        @if (session('status')) 
            @php 
                $alerts = [ 
                    'reading-confirmed' => [ 'title' => 'Status Diperbarui!', 'desc' => 'Data meteran telah berhasil dikonfirmasi.', 'icon' => 'fa-square-check' ], 
                    'reading-stored' => [ 'title' => 'Berhasil!', 'desc' => 'Data meteran baru telah disimpan.', 'icon' => 'fa-circle-check' ], 
                ]; 
                $statusKey = session('status'); 
                $current = $alerts[$statusKey] ?? null; 
            @endphp 
            @if ($current) 
                <div id="universal-alert" class="fixed top-6 right-6 z-[9999] flex items-center justify-between p-5 min-w-[380px] text-white border border-white/20 rounded-2xl backdrop-blur-md shadow-[0_10px_40px_rgba(0,0,0,0.5)] transition-all duration-500" style="background-color: rgba(96, 35, 22, 0.6);"> 
                    <div class="flex items-center gap-4"> 
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/10 border border-white/10 shadow-inner"> 
                            <i class="fa-solid {{ $current['icon'] }} text-[#FA8327] text-lg"></i> 
                        </div> 
                        <div class="flex flex-col gap-0.5"> 
                            <p class="text-sm font-bold tracking-wide">{{ $current['title'] }}</p> 
                            <p class="text-xs text-white/50 font-light">{{ $current['desc'] }}</p> 
                        </div> 
                    </div> 
                    <button type="button" onclick="closeUniversalAlert()" class="p-2 text-white/20 hover:text-[#FA8327] transition-colors"> 
                        <i class="fa-solid fa-xmark text-lg"></i> 
                    </button> 
                </div> 
                <script> 
                    function closeUniversalAlert() { 
                        const alert = document.getElementById('universal-alert'); 
                        if (alert) { 
                            alert.style.opacity = '0'; 
                            alert.style.transform = 'translateX(30px)'; 
                            setTimeout(() => alert.remove(), 500); 
                        } 
                    } 
                    setTimeout(closeUniversalAlert, 4500); 
                </script> 
            @endif 
        @endif 

        <div class="toolbar mb-6"> 
            <form action="{{ route('meter-readings.index') }}" method="GET" class="search-wrapper"> 
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Penghuni / Unit..."> 
                <i class="fa-solid fa-magnifying-glass"></i> 
            </form> 
        </div> 

        <div class="table-wrapper"> 
            @forelse($tenants as $tenant) 
                <div class="table-card mb-8"> 
                    <div class="table-card-header"> 
                        <div class="table-card-title"> 
                            <span class="label">Penghuni:</span> 
                            <span class="value">{{ $tenant->tenant_name }}</span> 
                        </div> 
                        <div class="table-card-meta"> 
                            {{ $tenant->units->count() }} Unit 
                        </div> 
                    </div> 
                    <table class="table"> 
                        <thead> 
                            <tr> 
                                <th>Unit</th> 
                                <th>No. Meteran</th> 
                                <th>Tipe</th> 
                                <th>Nilai Catat</th> 
                                <th>Deskripsi</th>
                                <th>Petugas</th> 
                                <th class="text-center">Konfirmasi</th> 
                                <th>Tanggal</th> 
                                <th class="text-center">Aksi</th> 
                            </tr> 
                        </thead> 
                        <tbody> 
                            @php $anyReading = false; @endphp 
                            @foreach($tenant->units as $unit) 
                                @foreach($unit->meters as $meter) 
                                    @forelse($meter->readings as $reading) 
                                        @php $anyReading = true; @endphp 
                                        <tr> 
                                            <td>{{ $unit->unit_number }}</td> 
                                            <td>{{ $meter->meter_number }}</td> 
                                            <td> 
                                                @if($meter->meter_type == 'electricity') 
                                                    <span class="amber-btn">Listrik</span> 
                                                @else 
                                                    <span class="blue-btn">Air</span> 
                                                @endif 
                                            </td> 
                                            <td>{{ number_format($reading->reading_value, 2) }}</td>
                                            <td class="text-xs text-zinc-400 max-w-[160px]">
                                                {{ $reading->description ?? '-' }}
                                            </td> 
                                            <td>{{ $reading->user->name }}</td> 
                                            <td class="text-center"> 
                                                <form action="{{ route('meter-readings.update-status', $reading->id) }}" method="POST"> 
                                                    @csrf 
                                                    @method('PATCH') 
                                                    <button type="submit" style="background: none; border: none; cursor: pointer;"> 
                                                        @if($reading->status === 'checked') 
                                                            <i class="fa-solid fa-square-check text-emerald-500 text-xl"></i> 
                                                        @else 
                                                            <i class="fa-regular fa-square text-zinc-500 text-xl"></i> 
                                                        @endif 
                                                    </button> 
                                                </form> 
                                            </td> 
                                            <td>{{ \Carbon\Carbon::parse($reading->recorded_at)->format('d M Y') }}</td> 
                                            <td> 
                                                <div class="flex justify-center"> 
                                                    @if($reading->photo_path) 
                                                        <button class="light-green-btn-action" data-popup="photoModal" onclick="showImage( '{{ asset('storage/'.$reading->photo_path) }}', '{{ $unit->unit_number }}', '{{ $meter->meter_type }}', '{{ number_format($reading->reading_value, 2) }}', '{{ \Carbon\Carbon::parse($reading->recorded_at)->format('d M Y, H:i') }}', '{{ $reading->status }}', '{{ $meter->meter_number }}', '{{ $reading->location_address ?? 'Lokasi tidak tercatat' }}', '{{ $reading->latitude }}', '{{ $reading->longitude }}' )"> 
                                                            <i class="fa-regular fa-eye"></i> Lihat Detail 
                                                        </button> 
                                                    @else 
                                                        <span class="subtitle-text text-xs">Tanpa Foto</span> 
                                                    @endif 
                                                </div> 
                                            </td> 
                                        </tr> 
                                    @empty 
                                    @endforelse 
                                @endforeach 
                            @endforeach 
                            @if(!$anyReading) 
                                <tr> 
                                    <td colspan="8" class="text-center py-8"> 
                                        <div class="flex flex-col items-center opacity-50"> 
                                            <i class="fa-solid fa-folder-open text-2xl mb-2"></i> 
                                            <p class="subtitle-text">Belum ada data meter untuk penghuni ini.</p> 
                                        </div> 
                                    </td> 
                                </tr> 
                            @endif 
                        </tbody> 
                    </table> 
                </div> 
            @empty 
                <div class="flex flex-col items-center justify-center py-20 bg-white/5 border border-white/10 rounded-3xl"> 
                    <i class="fa-solid fa-gauge-high text-4xl text-zinc-600 mb-4"></i> 
                    <h3 class="text-zinc-600 font-semibold">Data Tidak Ditemukan</h3> 
                </div> 
            @endforelse 
        </div> 

        {{-- PAGINATION LAYOUT DIUBAH MENJADI IDENTIK DENGAN LOG AUDIT --}}
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-2"> 
            <div class="text-sm text-zinc-500"> 
                Menampilkan <span class="text-white">{{ $tenants->firstItem() ?? 0 }}</span> sampai <span class="text-white">{{ $tenants->lastItem() ?? 0 }}</span> dari <span class="text-white">{{ $tenants->total() }}</span> hasil 
            </div> 
            <div class="braga-pagination"> 
                {{ $tenants->links('pagination::bootstrap-4') }} 
            </div> 
        </div> 
    </div> 
</div> 

{{-- POPUP COMPLAINT IMAGE / GEOLOCATION MAP DETAIL --}}
<div class="popup" id="photoModal"> 
    <div class="popup-overlay"></div> 
    <div class="popup-card popup-lg text-left"> 
        <div class="popup-close-wrapper"> 
            <button class="popup-close" data-close="photoModal"> 
                <i class="fa-solid fa-xmark"></i> 
            </button> 
        </div> 
        <div class="popup-header pb-4 mb-4"> 
            <h2 id="modalUnitTitle" class="text-xl font-bold text-white">Unit -</h2> 
            <p class="subtitle-text text-xs">Detail Hasil Catat & Bukti Fisik</p> 
        </div> 
        <div class="popup-body grid grid-cols-1 md:grid-cols-2 gap-6 items-start mt-4"> 
            <a id="modalImageLink" href="#" target="_blank" class="w-full group relative cursor-zoom-in"> 
                <div class="w-full bg-zinc-900 rounded-2xl border border-white/10 overflow-hidden flex justify-center items-center h-[220px]"> 
                    <img id="modalImage" src="" alt="Meter Reading" class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover:scale-[1.02]"> 
                </div> 
                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/20 rounded-2xl"> 
                    <i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i> 
                </div> 
            </a> 
            <div class="flex flex-col gap-4 h-full justify-between"> 
                <div class="flex flex-col gap-4"> 
                    <div class="flex gap-2 items-center"> 
                        <span id="modalTypeBadge"></span> 
                        <span id="modalStatusBadge"></span> 
                    </div> 
                    <div> 
                        <p class="text-[10px] tracking-wider text-zinc-400 font-bold uppercase">Nilai Catat</p> 
                        <p class="text-3xl font-black tracking-tight text-white mt-1"> 
                            <span id="modalValueText" class="text-[#FA8327]">0.00</span> 
                            <span class="text-xl text-zinc-400 font-medium" id="modalUnitLabel">m³</span> 
                        </p> 
                    </div> 
                    <div class="flex flex-col gap-3 border-t border-white/5 pt-3"> 
                        <div class="flex flex-col"> 
                            <div class="flex items-center gap-2"> 
                                <i class="fa-regular fa-clock text-zinc-500 text-xs shrink-0"></i> 
                                <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-500">Waktu Rekam</span> 
                            </div> 
                            <span id="modalDateText" class="text-sm text-zinc-200 mt-1 pl-5">-</span> 
                        </div> 
                        <div class="flex flex-col"> 
                            <div class="flex items-center gap-2"> 
                                <i class="fa-solid fa-gauge-high text-zinc-500 text-xs shrink-0"></i> 
                                <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-500">No. Meteran</span> 
                            </div> 
                            <span id="modalNumberText" class="text-sm text-zinc-200 mt-1 pl-5">-</span> 
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
        <div class="mt-5 flex flex-col gap-4"> 
            <div class="flex flex-col gap-1.5"> 
                <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-500">Lokasi Pengambilan</span> 
                <div class="flex items-center justify-between gap-4 bg-white/5 p-3 rounded-xl border border-white/5"> 
                    <div class="flex items-start gap-2.5"> 
                        <i class="fa-solid fa-location-dot text-[#FA8327] mt-0.5 text-sm"></i> 
                        <span id="modalAddressText" class="text-xs text-zinc-300 leading-relaxed">-</span> 
                    </div> 
                    <a id="modalMapsLink" href="#" target="_blank" class="text-[11px] text-sky-400 hover:underline shrink-0 flex items-center gap-1.5 bg-sky-500/10 px-3 py-1.5 rounded-lg border border-sky-500/20 transition-colors hover:bg-sky-500/20"> 
                        <i class="fa-solid fa-map-location-dot"></i> Maps 
                    </a> 
                </div> 
            </div> 
        </div> 
    </div> 
</div> 

<script> 
    function showImage(src, unit, type, value, date, status, number, address, lat, lon) { 
        document.getElementById('modalImage').src = src; 
        document.getElementById('modalImageLink').href = src; 
        document.getElementById('modalUnitTitle').innerText = "Unit " + unit; 
        document.getElementById('modalValueText').innerText = value; 
        document.getElementById('modalDateText').innerText = date; 
        document.getElementById('modalNumberText').innerText = number; 
        document.getElementById('modalAddressText').innerText = address; 
        
        const mapsLink = document.getElementById('modalMapsLink'); 
        if (lat && lon && lat != 0 && lon != 0) { 
            mapsLink.href = `https://www.google.com/maps/search/?api=1&query=${lat},${lon}`; 
            mapsLink.style.display = "inline-flex"; 
        } else { 
            mapsLink.style.display = "none"; 
        } 
        
        const typeBadge = document.getElementById('modalTypeBadge'); 
        const unitLabel = document.getElementById('modalUnitLabel'); 
        if (type === 'electricity') { 
            typeBadge.className = "amber-btn"; 
            typeBadge.innerHTML = 'Listrik'; 
            unitLabel.innerText = "kWh"; 
        } else { 
            typeBadge.className = "blue-btn"; 
            typeBadge.innerHTML = 'Air'; 
            unitLabel.innerText = "m³"; 
        } 
        
        const statusBadge = document.getElementById('modalStatusBadge'); 
        if (status === 'checked') { 
            statusBadge.className = "dark-green-btn"; 
            statusBadge.innerHTML = '<i class="fa-solid fa-circle-check mr-1"></i> Terkonfirmasi'; 
        } else { 
            statusBadge.className = "red-btn"; 
            statusBadge.innerHTML = '<i class="fa-solid fa-hourglass-half mr-1"></i> Menunggu'; 
        } 
    } 
</script> 
@endsection