@extends('layouts.app') 

@section('content') 
<div class="min-h-screen"> 
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8"> 
        <div> 
            <h1 class="title-text">Meteran Utilitas</h1> 
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
                    'meter-stored' => [ 
                        'title' => 'Meteran Ditambahkan!', 
                        'desc'  => 'Meteran baru berhasil didaftarkan ke sistem.', 
                        'icon'  => 'fa-circle-check' 
                    ], 
                    'meter-updated' => [ 
                        'title' => 'Data Diperbarui!', 
                        'desc'  => 'Perubahan data meteran telah berhasil disimpan.', 
                        'icon'  => 'fa-pen' 
                    ], 
                    'meter-deleted' => [ 
                        'title' => 'Meteran Dihapus!', 
                        'desc'  => 'Data meteran telah berhasil dihapus dari sistem.', 
                        'icon'  => 'fa-trash-can' 
                    ] 
                ]; 
                $current = $alerts[session('status')] ?? null; 
            @endphp 
            @if ($current) 
                <div id="universal-alert" class="fixed top-6 right-6 z-[9999] flex items-center justify-between p-5 min-w-[380px] text-white border border-white/20 rounded-2xl backdrop-blur-md shadow-[0_10px_40px_rgba(0,0,0,0.5)] transition-all duration-500" style="background-color: rgba(96, 35, 22, 0.6);" > 
                    <div class="flex items-center gap-4"> 
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/10 border border-white/10 shadow-inner"> 
                            <i class="fa-solid {{ $current['icon'] }} text-[#FA8327] text-lg"></i> 
                        </div> 
                        <div class="flex flex-col gap-0.5"> 
                            <p class="text-sm font-bold tracking-wide"> {{ $current['title'] }} </p> 
                            <p class="text-xs text-white/50 font-light"> {{ $current['desc'] }} </p> 
                        </div> 
                    </div> 
                    <button type="button" onclick="closeUniversalAlert()" class="p-2 text-white/20 hover:text-[#FA8327] transition-colors" > 
                        <i class="fa-solid fa-xmark text-lg"></i> 
                    </button> 
                </div> 
                <script> 
                    function closeUniversalAlert() { 
                        const alert = document.getElementById('universal-alert'); 
                        if (alert) { 
                            alert.style.opacity = '0'; 
                            alert.style.transform = 'translateX(30px)'; 
                            setTimeout(() => { alert.remove(); }, 500); 
                        } 
                    } 
                    setTimeout(closeUniversalAlert, 4500); 
                </script> 
            @endif 
        @endif 

        <div class="toolbar mb-6"> 
            <form action="{{ route('utility-meters.index') }}" method="GET" class="search-wrapper"> 
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Meter atau Unit.."> 
                <span><i class="fa-solid fa-magnifying-glass"></i></span> 
            </form> 
            <div class="toolbar-action"> 
                <button type="button" class="light-brown-btn btn-small" data-popup="addMeter"> 
                    <span><i class="fa-solid fa-plus"></i></span> 
                    <span>Tambah Meteran Baru</span> 
                </button> 
            </div> 
        </div> 

        <div class="table-wrapper"> 
            @forelse($meters->groupBy('unit.unit_number') as $unitNumber => $groupedMeters) 
            <div class="table-card mb-6"> 
                <div class="table-card-header"> 
                    <div class="table-card-title"> 
                        <span class="label">Unit:</span> 
                        <span class="value">
                            {{ $unitNumber ?? 'N/A' }}
                       </span> 
                    </div> 
                    
                    <div class="table-card-meta"> 
                        {{ $groupedMeters->first()->unit->tenant->tenant_name ?? 'Tanpa Penghuni' }}
                    </div> 
                </div> 

                <table class="table"> 
                    <thead> 
                        <tr> 
                            <th>Nomor Meter</th> 
                            <th>Tipe</th> 
                            <th>Kapasitas</th> 
                            <th>Gol. Tarif</th> 
                            <th>Kategori</th> 
                            <th class="text-center">Tindakan</th> 
                        </tr> 
                    </thead> 
                    <tbody> 
                        @foreach($groupedMeters as $meter) 
                        <tr> 
                            <td class="font-medium text-zinc-300">{{ $meter->meter_number }}</td> 
                            <td> 
                                @if($meter->meter_type == 'electricity') 
                                    <span class="amber-btn">Listrik</span> 
                                @else 
                                    <span class="blue-btn">Air</span> 
                                @endif 
                            </td> 
                            <td>{{ $meter->power_capacity ?? '-' }}</td> 
                            <td>{{ $meter->tariff->name ?? '-' }}</td> 
                            <td> 
                                <span class="{{ $meter->meter_category == 'postpaid' ? 'amber-btn' : 'blue-btn' }} !w-fit px-3"> 
                                    {{ $meter->meter_category == 'postpaid' ? 'Pascabayar' : 'Prabayar' }} 
                                </span> 
                            </td> 
                            <td> 
                                <div class="flex justify-center gap-2"> 
                                    <button class="light-green-btn-action" data-popup="viewMeter{{ $meter->id }}"> 
                                        <i class="fa-solid fa-eye text-xs"></i> Lihat 
                                    </button> 
                                    <button class="light-brown-btn-action" data-popup="editMeter{{ $meter->id }}"> 
                                        <i class="fa-solid fa-pen text-xs"></i> Ubah 
                                    </button> 
                                    <form id="delete-form-{{ $meter->id }}" action="{{ route('utility-meters.destroy', $meter->id) }}" method="POST" class="m-0 p-0"> 
                                        @csrf 
                                        @method('DELETE') 
                                        <button type="button" class="dark-brown-btn-action" data-popup="delete-tariff" data-id="{{ $meter->id }}"> 
                                            <i class="fa-solid fa-trash text-xs"></i> Hapus 
                                        </button> 
                                    </form> 
                                </div> 
                            </td> 
                        </tr> 
                        @endforeach 
                    </tbody> 
                </table> 

                @foreach($groupedMeters as $meter) 
                        <div class="popup" id="editMeter{{ $meter->id }}"> 
                            <div class="popup-overlay"></div> 
                            <div class="popup-card popup-md text-left"> 
                                <div class="popup-close-wrapper"> 
                                    <button class="popup-close" data-close="editMeter{{ $meter->id }}"> 
                                        <i class="fa-solid fa-xmark"></i> 
                                    </button> 
                                </div> 
                                <h1 class="popup-header">Ubah Meteran: {{ $meter->meter_number }}</h1> 
                                <div class="popup-body"> 
                                    <form action="{{ route('utility-meters.update', $meter->id) }}" method="POST"> 
                                        @csrf 
                                        @method('PUT') 
                                        <div class="flex flex-col gap-6"> 
                                            <div> 
                                                <div class="text-field"> 
                                                    <label class="text-field-label">Pilih Unit <span class="text-[#FA8327]">*</span></label> 
                                                    <div class="custom-dropdown"> 
                                                        <div class="dropdown-selected"> 
                                                            <span class="placeholder">{{ $meter->unit->unit_number }} - Lantai {{ $meter->unit->floor ?? '-' }}</span> 
                                                            <i class="fa-solid fa-angle-down"></i> 
                                                        </div> 
                                                        <div class="dropdown-options"> 
                                                            @foreach($units as $unit) 
                                                                <div class="option" data-value="{{ $unit->id }}"> 
                                                                    {{ $unit->unit_number }} - Lantai {{ $unit->floor ?? '' }} 
                                                                </div> 
                                                            @endforeach 
                                                        </div> 
                                                        <input type="hidden" name="unit_id" value="{{ $meter->unit_id }}" required> 
                                                    </div> 
                                                </div> 
                                                <div class="grid grid-cols-2 gap-4"> 
                                                    <div class="text-field">
                                                        <label class="text-field-label">Tipe Meteran <span class="text-[#FA8327]">*</span></label>
                                                        <div class="custom-dropdown relative">
                                                            <div class="dropdown-selected">
                                                                <span class="placeholder">{{ $meter->meter_type == 'electricity' ? 'Listrik' : 'Air' }}</span>
                                                                <i class="fa-solid fa-angle-down"></i>
                                                            </div>
                                                            <div class="dropdown-options">
                                                                <div class="option" data-value="electricity">Listrik</div>
                                                                <div class="option" data-value="water">Air</div>
                                                            </div>
                                                            <input type="hidden" name="meter_type" id="edit_meter_type_{{ $meter->id }}" value="{{ $meter->meter_type }}">
                                                            
                                                            <input type="text" class="absolute bottom-0 left-1/2 w-0 h-0 opacity-0 pointer-events-none" value="{{ $meter->meter_type }}" required data-dropdown-fake="edit_meter_type_{{ $meter->id }}">
                                                        </div>
                                                    </div>
                                                    <div class="text-field">
                                                    <label class="text-field-label"> Kategori</label>
                                                    <div class="custom-dropdown pointer-events-none opacity-80">
                                                        <div class="dropdown-selected border border-zinc-700 bg-zinc-800">
                                                            <span class="font-medium text-zinc-400">Pascabayar</span>
                                                            <i class="fa-solid fa-lock text-zinc-600 text-xs"></i>
                                                        </div>
                                                        
                                                        <input type="hidden" name="meter_category" value="postpaid" required>
                                                    </div>
                                                </div>
                                                </div> 
                                                <div class="grid grid-cols-2 gap-4"> 
                                                    <div class="text-field"> 
                                                        <label class="text-field-label">Nomor Meteran <span class="text-[#FA8327]">*</span></label> 
                                                        <input type="text" name="meter_number" class="text-field-input" value="{{ $meter->meter_number }}" required> 
                                                    </div> 
                                                    <div class="text-field"> 
                                                        <label class="text-field-label">Kapasitas (Opsional)</label> 
                                                        <input type="text" name="power_capacity" class="text-field-input" value="{{ $meter->power_capacity }}" placeholder="Contoh: 2200 VA"> 
                                                    </div> 
                                                </div> 
                                                <div class="text-field"> 
                                                    <label class="text-field-label">Faktor Kali (Multiplier)</label> 
                                                    <div class="lock-field-group"> 
                                                        <input type="number" step="0.01" name="multiplier" id="multiplierInput{{ $meter->id }}" class="lock-field-input" value="{{ $meter->multiplier ?? '1.00' }}" readonly> 
                                                        <button type="button" class="lock-btn-small" data-meter-id="{{ $meter->id }}" id="btnLock{{ $meter->id }}"> 
                                                            <i class="fa-solid fa-lock" id="lockIcon{{ $meter->id }}"></i> 
                                                        </button> 
                                                    </div> 
                                                </div> 
                                                <div class="text-field">
                                                    <label class="text-field-label">Golongan Tarif <span class="text-[#FA8327]">*</span></label>
                                                    <div class="custom-dropdown relative">
                                                        <div class="dropdown-selected">
                                                            <span class="placeholder">{{ $meter->tariff->name ?? '-- Pilih Tarif --' }}</span>
                                                            <i class="fa-solid fa-angle-down"></i>
                                                        </div>
                                                        <div class="dropdown-options">
                                                            @foreach($tariffs as $tariff)
                                                                <div class="option" data-value="{{ $tariff->id }}">
                                                                    {{ $tariff->name }} (Rp {{ number_format($tariff->electric_price) }})
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <input type="hidden" name="tariff_id" id="edit_tariff_id_{{ $meter->id }}" value="{{ $meter->tariff_id }}">
                                                        
                                                        <input type="text" class="absolute bottom-0 left-1/2 w-0 h-0 opacity-0 pointer-events-none" value="{{ $meter->tariff_id }}" required data-dropdown-fake="edit_tariff_id_{{ $meter->id }}">
                                                    </div>
                                                </div>
                                            </div> 
                                            <button type="submit" class="light-brown-btn mt-4 py-3">Simpan Perubahan</button> 
                                        </div> 
                                    </form> 
                                </div> 
                            </div> 
                        </div> 

                        <div class="popup" id="viewMeter{{ $meter->id }}"> 
                            <div class="popup-overlay"></div> 
                            <div class="popup-card popup-md text-left"> 
                                <div class="popup-close-wrapper"> 
                                    <button class="popup-close" data-close="viewMeter{{ $meter->id }}"> 
                                        <i class="fa-solid fa-xmark"></i> 
                                    </button> 
                                </div> 
                                <h1 class="popup-header">Informasi Meteran</h1> 
                                <div class="popup-body"> 
                                    <div class="flex flex-col gap-8"> 
                                        <div class="p-4 rounded-2xl bg-zinc-800 border border-zinc-700 flex justify-between items-center"> 
                                            <div class="flex flex-col gap-2"> 
                                                <p class="text-xs tracking-wider text-zinc-400 font-semibold uppercase">Nomor Unit</p> 
                                                <p class="text-3xl font-bold text-zinc-100">{{ $meter->unit->unit_number ?? '-' }}</p> 
                                            </div> 
                                            <div class="flex flex-col gap-2 items-end"> 
                                                <p class="text-right text-xs tracking-wider text-zinc-400 font-semibold uppercase">Tipe Meteran</p> 
                                                @if($meter->meter_type == 'electricity') 
                                                    <span class="amber-btn !w-fit px-4 py-1.5 text-xs">LISTRIK</span> 
                                                @else 
                                                    <span class="blue-btn !w-fit px-4 py-1.5 text-xs">AIR</span> 
                                                @endif 
                                            </div> 
                                        </div> 
                                        <div class="grid grid-cols-2 gap-y-6 gap-x-6 px-2"> 
                                            <div class="detail-item"> 
                                                <p>Nomor Meteran</p> 
                                                <p>{{ $meter->meter_number }}</p> 
                                            </div> 
                                            <div class="detail-item"> 
                                                <p>Kapasitas Daya</p> 
                                                <p>{{ $meter->power_capacity ?? '-' }}</p> 
                                            </div> 
                                            <div class="detail-item"> 
                                                <p>Golongan Tarif</p> 
                                                <p>{{ $meter->tariff->name ?? '-' }}</p> 
                                            </div> 
                                            <div class="detail-item"> 
                                                <p>Kategori</p> 
                                                <span class="font-bold text-base {{ $meter->meter_category == 'postpaid' ? 'text-amber-500' : 'text-blue-500' }}"> 
                                                    {{ $meter->meter_category == 'postpaid' ? 'PASCABAYAR' : 'PRABAYAR' }} 
                                                </span> 
                                            </div> 
                                            <div class="detail-item"> 
                                                <p>Faktor Kali</p> 
                                                <p>{{ $meter->multiplier ?? '1.00' }}x</p> 
                                            </div> 
                                            <div class="detail-item"> 
                                                <p>Terakhir Diperbarui</p> 
                                                <p>{{ $meter->updated_at->format('d M Y, H:i') }} WIB</p> 
                                            </div> 
                                        </div> 
                                    </div> 
                                </div> 
                            </div> 
                        </div> 
                @endforeach 
            </div> 
            @empty 
            <div class="table-card p-10 text-center text-zinc-400 italic"> Data meter tidak ditemukan </div> 
            @endforelse 
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-2"> 
            <div class="text-sm text-zinc-500"> 
                Menampilkan <span class="text-white">{{ $meters->firstItem() }}</span> sampai <span class="text-white">{{ $meters->lastItem() }}</span> dari <span class="text-white">{{ $meters->total() }}</span> hasil 
            </div> 
            <div class="braga-pagination"> 
                {{ $meters->links('pagination::bootstrap-4') }} 
            </div> 
        </div> 
    </div> 
</div> 

<div class="popup" id="addMeter"> 
    <div class="popup-overlay"></div> 
    <div class="popup-card popup-md"> 
        <div class="popup-close-wrapper"> 
            <button type="button" class="popup-close" data-close="addMeter"> 
                <i class="fa-solid fa-xmark"></i> 
            </button> 
        </div> 
        <h1 class="popup-header">Tambah Meteran Baru</h1> 
        <div class="popup-body"> 
            <form action="{{ route('utility-meters.store') }}" method="POST"> 
                @csrf 
                <div class="flex flex-col gap-6"> 
                    <div> 
                        <div class="text-field"> 
                            <label class="text-field-label">Pilih Unit <span class="text-[#FA8327]">*</span></label> 
                            <div class="custom-dropdown"> 
                                <div class="dropdown-selected"> 
                                    <span class="placeholder"> -- Pilih Unit -- </span> 
                                    <i class="fa-solid fa-angle-down"></i> 
                                </div> 
                                <div class="dropdown-options"> 
                                    @foreach($units as $unit) 
                                        <div class="option" data-value="{{ $unit->id }}" > 
                                            {{ $unit->unit_number }} - Lantai {{ $unit->floor ?? '' }} 
                                        </div> 
                                    @endforeach 
                                </div> 
                                <input type="hidden" name="unit_id" required > 
                            </div> 
                        </div> 
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-field">
                                <label class="text-field-label"> Tipe Meteran <span class="text-[#FA8327]">*</span> </label>
                                <div class="custom-dropdown relative">
                                    <div class="dropdown-selected">
                                        <span class="placeholder"> -- Pilih Tipe -- </span>
                                        <i class="fa-solid fa-angle-down"></i>
                                    </div>
                                    <div class="dropdown-options">
                                        <div class="option" data-value="electricity"> Listrik </div>
                                        <div class="option" data-value="water"> Air </div>
                                    </div>
                                    <input type="hidden" name="meter_type" id="add_meter_type">
                                    
                                    <input type="text" class="absolute bottom-0 left-1/2 w-0 h-0 opacity-0 pointer-events-none" required data-dropdown-fake="add_meter_type">
                                </div>
                            </div>
                            <div class="text-field">
                                <label class="text-field-label"> Kategori <span class="text-[#FA8327]">*</span></label>
                                <div class="custom-dropdown pointer-events-none opacity-80">
                                    <div class="dropdown-selected border border-zinc-700 bg-zinc-800">
                                        <span class="font-medium text-zinc-400">Pascabayar</span>
                                        <i class="fa-solid fa-lock text-zinc-600 text-xs"></i>
                                    </div>
                                    
                                    <input type="hidden" name="meter_category" value="postpaid" required>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4"> 
                            <div class="text-field"> 
                                <label class="text-field-label"> Nomor Meteran <span class="text-[#FA8327]">*</span></label> 
                                <input type="text" name="meter_number" class="text-field-input" placeholder="Misal: MTR-001" required > 
                            </div> 
                            <div class="text-field"> 
                                <label class="text-field-label"> Kapasitas </label> 
                                <input type="text" name="power_capacity" class="text-field-input" placeholder="Misal: 2200 VA" > 
                            </div> 
                        </div> 
                        <div class="text-field"> 
                            <label class="text-field-label"> Faktor Kali (Multiplier) </label> 
                            <div class="lock-field-group"> 
                                <input type="number" step="0.01" name="multiplier" id="multiplierInput" class="lock-field-input" value="1.00" readonly > 
                                <button type="button" class="lock-btn-small" id="btnLock" > 
                                    <i class="fa-solid fa-lock" id="lockIcon"></i> 
                                </button> 
                            </div> 
                        </div> 
                        <div class="text-field">
                            <label class="text-field-label"> Golongan Tarif <span class="text-[#FA8327]">*</span></label>
                            <div class="custom-dropdown relative">
                                <div class="dropdown-selected">
                                    <span class="placeholder"> -- Pilih Tarif -- </span>
                                    <i class="fa-solid fa-angle-down"></i>
                                </div>
                                <div class="dropdown-options">
                                    @foreach($tariffs as $tariff)
                                        <div class="option" data-value="{{ $tariff->id }}">
                                            {{ $tariff->name }} (Rp {{ number_format($tariff->electric_price) }})
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="tariff_id" id="add_tariff_id">
                                
                                <input type="text" class="absolute bottom-0 left-1/2 w-0 h-0 opacity-0 pointer-events-none" required data-dropdown-fake="add_tariff_id">
                            </div>
                        </div> 
                    </div> 
                    <button type="submit" class="light-brown-btn mt-4 py-3" > Simpan Data Meteran </button> 
                </div> 
            </form> 
        </div> 
    </div> 
</div> 

<div class="popup" id="delete-tariff"> 
    <div class="popup-overlay"></div> 
    <div class="popup-card popup-md"> 
        <div class="popup-close-wrapper"> 
            <button class="popup-close" data-close="delete-tariff"> 
                <i class="fa-solid fa-xmark"></i> 
            </button> 
        </div> 
        <div class="popup-header">Hapus Meteran Ini?</div> 
        <div class="popup-body btn-delete-wrapper flex gap-3"> 
            <button class="dark-brown-button flex-1" data-close="delete-tariff">Tidak</button> 
            <button id="confirm-delete-btn" class="light-brown-btn flex-1">Ya, Hapus</button> 
        </div> 
    </div> 
</div> 
@endsection