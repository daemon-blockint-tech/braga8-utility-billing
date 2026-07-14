@extends('layouts.app') 

@section('content') 
<div class="min-h-screen"> 
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8"> 
        <div> 
            <h1 class="title-text">Keluhan</h1> 
            <p class="subtitle-text">Braga8 Utility Billing Management</p> 
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
                    'complaint-resolved' => ['title' => 'Berhasil!', 'desc' => 'Solusi telah disimpan.', 'icon' => 'fa-circle-check'], 
                    'complaint-deleted'  => ['title' => 'Data Dihapus!', 'desc' => 'Laporan telah dihapus.', 'icon' => 'fa-trash-can'] 
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

        <div class="toolbar"> 
            <form method="GET" action="{{ route('complaints.index') }}" class="flex items-center gap-2"> 
                <div class="search-wrapper"> 
                    <input type="text" name="search" placeholder="Cari Keluhan.." value="{{ request('search') }}"> 
                    <span><i class="fa-solid fa-magnifying-glass"></i></span> 
                </div> 
            </form> 
            <div class="toolbar-action">
                @if(request('sort') === 'oldest')
                    <a href="{{ route('complaints.index', array_merge(request()->query(), ['sort' => 'latest'])) }}" 
                    class="dark-brown-button btn-small flex items-center gap-2 text-decoration-none">
                        <i class="fa-solid fa-sort-amount-up"></i>
                        <span>Terlama ke Terbaru</span>
                    </a>
                @else
                    <a href="{{ route('complaints.index', array_merge(request()->query(), ['sort' => 'oldest'])) }}" 
                    class="dark-brown-button btn-small flex items-center gap-2 text-decoration-none">
                        <i class="fa-solid fa-sort-amount-down"></i>
                        <span>Terbaru ke Terlama</span>
                    </a>
                @endif
            </div>
        </div> 

        <div class="table-wrapper">
            <div class="table-card">
                @if($complaints->count() > 0)
                    <div class="table-card-header">
                        <div class="table-card-title">
                            <span class="label">Total Keluhan:</span>
                            <span class="value">{{ $complaints->total() }}</span>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pelapor</th>
                                <th>Peran</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($complaints as $index => $complaint)
                                <tr>
                                    <td>{{ $complaints->firstItem() + $index }}</td>
                                    <td class="font-bold text-zinc-800">{{ $complaint->reported_by }}</td>
                                    <td>{{ $complaint->role == 'tenant' ? 'Penyewa' : ucfirst($complaint->role) }}</td>
                                    <td>{{ $complaint->report_date->translatedFormat('d M Y') }}</td>
                                    <td>
                                        @if($complaint->status === 'resolved')
                                            <span class="dark-green-btn text-[10px]">Selesai</span>
                                        @else
                                            <span class="amber-btn text-[10px]">Diproses</span>
                                        @endif
                                    </td>
                                    <td class="actions">
                                        <div class="flex justify-center gap-2">
                                            <button class="light-green-btn-action" data-popup="view-complaint-{{ $complaint->id }}">
                                                <i class="fa-solid fa-eye"></i>
                                                <span>Buka</span>
                                            </button>
                                            <form id="delete-form-{{ $complaint->id }}" action="{{ route('complaints.destroy', $complaint->id) }}" method="POST" class="m-0 p-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dark-brown-btn-action border-0" data-popup="delete-complaint" data-id="{{ $complaint->id }}">
                                                    <i class="fa-solid fa-trash"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <div class="popup" id="view-complaint-{{ $complaint->id }}">
                                    <div class="popup-overlay"></div>
                                    <div class="popup-card popup-lg text-left">
                                        <div class="popup-close-wrapper">
                                            <button class="popup-close" data-close="view-complaint-{{ $complaint->id }}">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                        <div class="popup-header">{{ $complaint->subject ?? 'Keluhan Umum' }}</div>
                                        <div class="popup-body is-scrollable flex flex-col gap-6 pt-2">
                                            <div class="flex flex-col lg:flex-row gap-8">
                                                <div class="flex-1 flex flex-col gap-4">
                                                    <div class="grid grid-cols-2 gap-y-5 gap-x-4">
                                                        <div class="detail-item">
                                                            <p class="text-xs text-zinc-500">Pelapor</p>
                                                            <p class="font-bold text-zinc-200">{{ $complaint->reported_by }}</p>
                                                        </div>
                                                        <div class="detail-item">
                                                            <p class="text-xs text-zinc-500">Peran</p>
                                                            <p class="text-zinc-300">{{ $complaint->role == 'tenant' ? 'Penyewa' : ucfirst($complaint->role) }}</p>
                                                        </div>
                                                        <div class="detail-item">
                                                            <p class="text-xs text-zinc-500">Tanggal</p>
                                                            <p class="text-zinc-300">{{ $complaint->report_date->translatedFormat('d F Y') }}</p>
                                                        </div>
                                                        <div class="detail-item">
                                                            <p class="text-xs text-zinc-500">Status</p>
                                                            <div>
                                                                @if($complaint->status === 'resolved')
                                                                    <span class="dark-green-btn">Selesai</span>
                                                                @else
                                                                    <span class="red-btn">Belum Selesai</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bg-white/5 border border-zinc-700 p-5 rounded-2xl">
                                                        <label class="block text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-3">Detail Keluhan</label>
                                                        <p class="text-zinc-300 text-sm leading-relaxed">"{{ $complaint->description }}"</p>
                                                    </div>
                                                </div>

                                                @if($complaint->image)
                                                    <div class="w-full lg:w-[220px] flex flex-col gap-2">
                                                        <label class="block text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Bukti Foto</label>
                                                        <div class="aspect-square rounded-2xl overflow-hidden border border-zinc-700 bg-zinc-800">
                                                            <img src="{{ asset('storage/' . $complaint->image) }}" class="w-full h-full object-cover cursor-zoom-in" onclick="window.open(this.src, '_blank')">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="border-t border-zinc-800 pt-4">
                                                @if($complaint->status !== 'resolved')
                                                    <form action="{{ route('complaints.action', $complaint->id) }}" method="POST">
                                                        @csrf
                                                        <div class="flex flex-col gap-4 w-full m-0 p-0">
                                                            <div class="flex flex-col gap-2">
                                                                <label class="block text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Input Resolusi</label>
                                                                <textarea name="solution" class="w-full bg-white/5 border border-zinc-700 rounded-2xl px-5 py-4 text-zinc-200 text-sm focus:outline-none focus:border-[#FA8327]/50 transition-all min-h-[120px] resize-none" placeholder="Tulis langkah penyelesaian..." required>{{ old('solution', $complaint->solution) }}</textarea>
                                                            </div>
                                                            <button type="submit" class="dark-brown-button w-full py-3">
                                                                Simpan Solusi
                                                            </button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="bg-white/5 border border-zinc-700 p-5 rounded-2xl">
                                                        <label class="block text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-2">Catatan Penyelesaian</label>
                                                        <p class="text-zinc-300 text-sm leading-relaxed">{{ $complaint->solution }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="table-card p-10 text-center text-zinc-400 italic">
                        Belum ada keluhan
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-2"> 
            <div class="text-sm text-zinc-500"> 
                Menampilkan <span class="text-white">{{ $complaints->firstItem() ?? 0 }}</span> sampai <span class="text-white">{{ $complaints->lastItem() ?? 0 }}</span> dari <span class="text-white">{{ $complaints->total() }}</span> hasil 
            </div> 
            <div class="braga-pagination"> 
                {{ $complaints->links('pagination::bootstrap-4') }} 
            </div> 
        </div> 
    </div> 

    <div class="popup" id="delete-complaint"> 
        <div class="popup-overlay"></div> 
        <div class="popup-card popup-md"> 
            <div class="popup-close-wrapper"> 
                <button class="popup-close" data-close="delete-complaint"> 
                    <i class="fa-solid fa-xmark"></i> 
                </button> 
            </div> 
            <div class="popup-header text-[#602316]">Hapus Laporan Keluhan</div> 
            <div class="popup-body flex flex-col gap-6 text-center"> 
                <p class="text-sm text-zinc-600">Apakah Anda yakin ingin menghapus laporan keluhan ini? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex gap-4 justify-center"> 
                    <button type="button" class="dark-brown-button px-10" data-close="delete-complaint">Batal</button> 
                    <button type="button" id="confirm-delete-btn" class="light-brown-btn px-10">Ya, Hapus</button> 
                </div> 
            </div> 
        </div> 
    </div> 
</div> 
@endsection