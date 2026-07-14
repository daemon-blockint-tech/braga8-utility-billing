@extends('layouts.app') 

@section('content') 
<div class="min-h-screen"> 
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 border-zinc-200 pb-8"> 
        <div> 
            <h1 class="title-text">Daftar Unit</h1> 
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
        @if (session('success')) 
            <div id="universal-alert" class="fixed top-6 right-6 z-[9999] flex items-center justify-between p-5 min-w-[380px] text-white border border-white/20 rounded-2xl backdrop-blur-md shadow-[0_10px_40px_rgba(0,0,0,0.5)] transition-all duration-500" style="background-color: rgba(96, 35, 22, 0.6);"> 
                <div class="flex items-center gap-4"> 
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/10 border border-white/10 shadow-inner"> 
                        <i class="fa-solid fa-circle-check text-[#FA8327] text-lg"></i> 
                    </div> 
                    <div class="flex flex-col gap-0.5"> 
                        <p class="text-sm font-bold tracking-wide">Berhasil!</p> 
                        <p class="text-xs text-white/50 font-light">{{ session('success') }}</p> 
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

        <div class="toolbar"> 
            <form action="{{ route('units.index') }}" method="GET" class="search-wrapper"> 
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Tenant / Unit..."> 
                <span><i class="fa-solid fa-magnifying-glass"></i></span> 
            </form> 
            <div class="toolbar-action"> 
                <button class="light-brown-btn btn-small" data-popup="addUnitModal"> 
                    <span><i class="fa-solid fa-plus"></i></span> 
                    <span>Tambah Unit</span> 
                </button> 
            </div> 
        </div> 

        <div class="table-wrapper"> 
            @forelse($tenants as $tenant) 
                <div class="table-card mb-8"> 
                    <div class="table-card-header"> 
                        <div class="table-card-title"> 
                            <span class="label">Tenant:</span> 
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
                                <th>Lantai</th> 
                                <th>Luas (m²)</th> 
                                <th>Status</th> 
                                <th>Mulai Sewa</th> 
                                <th>Sewa Berakhir</th> 
                                <th class="text-center">Aksi</th> 
                            </tr> 
                        </thead> 
                        <tbody> 
                            @foreach($tenant->units as $unit) 
                                <tr> 
                                    <td><strong>{{ $unit->unit_number }}</strong></td> 
                                    <td>{{ $unit->floor ?? '-' }}</td> 
                                    <td>{{ $unit->area_size ?? '0' }} m²</td> 
                                    <td> 
                                        @if($unit->is_active) 
                                            <span class="dark-green-btn px-3 py-1 rounded-full text-[10px]">Aktif</span> 
                                        @else 
                                            <span class="red-btn px-3 py-1 rounded-full text-[10px]" style="background: #fee2e2; color: #ef4444; border-color: #fecaca;">Nonaktif</span> 
                                        @endif 
                                    </td> 
                                    <td>{{ $unit->lease_start ? \Carbon\Carbon::parse($unit->lease_start)->format('d/m/Y') : '-' }}</td> 
                                    <td>{{ $unit->lease_end ? \Carbon\Carbon::parse($unit->lease_end)->format('d/m/Y') : '-' }}</td> 
                                    <td class="actions"> 
                                        <div class="grid grid-cols-3 gap-2 w-full"> 
                                            <button type="button" class="light-green-btn-action w-full justify-center" data-popup="detail-unit-{{ $unit->id }}"> 
                                                <i class="fa-solid fa-eye text-xs"></i> 
                                                <span class="text-xs">Lihat</span> 
                                            </button> 
                                            <button type="button" class="light-brown-btn-action w-full justify-center text-center" data-popup="edit-unit-{{ $unit->id }}"> 
                                                <i class="fa-solid fa-pen text-xs"></i> 
                                                <span class="text-xs">Ubah</span> 
                                            </button> 
                                            <form id="delete-form-{{ $unit->id }}" action="{{ route('units.destroy', $unit->id) }}" method="POST" class="m-0 p-0"> 
                                                @csrf 
                                                @method('DELETE') 
                                                <button type="button" class="dark-brown-btn-action border-0 w-full justify-center btn-trigger-delete" data-popup="delete-unit-modal" data-id="{{ $unit->id }}" data-unit="{{ $unit->unit_number }}"> 
                                                    <div class="flex items-center gap-2"> 
                                                        <i class="fa-solid fa-trash text-xs"></i> 
                                                        <span class="text-xs">Hapus</span> 
                                                    </div> 
                                                </button> 
                                            </form> 
                                        </div> 
                                    </td> 
                                </tr> 

                                {{-- POPUP DETAIL UNIT --}}
                                <div class="popup" id="detail-unit-{{ $unit->id }}"> 
                                    <div class="popup-overlay"></div> 
                                    <div class="popup-card popup-md"> 
                                        <div class="popup-close-wrapper"> 
                                            <button class="popup-close" data-close="detail-unit-{{ $unit->id }}"> 
                                                <i class="fa-solid fa-xmark"></i> 
                                            </button> 
                                        </div> 
                                        <div class="popup-header">Unit {{ $unit->unit_number }}</div> 
                                        <div class="popup-body user-detail-info text-left"> 
                                            <div class="flex justify-between"> 
                                                <div class="flex flex-col gap-5"> 
                                                    <div class="flex flex-col gap-2"> 
                                                        <div class="detail-item"> 
                                                            <p>Tenant</p> 
                                                            <p>{{ $tenant->tenant_name }}</p> 
                                                        </div> 
                                                        <div class="detail-item"> 
                                                            <p>Lantai</p> 
                                                            <p>{{ $unit->floor ?? '-' }}</p> 
                                                        </div> 
                                                        <div class="detail-item"> 
                                                            <p>Luas Area</p> 
                                                            <p>{{ $unit->area_size ?? '0' }} m²</p> 
                                                        </div> 
                                                    </div> 
                                                </div> 
                                                <div class="flex flex-col gap-5"> 
                                                    <div class="flex flex-col gap-2"> 
                                                        <div class="detail-item"> 
                                                            <p>Mulai Sewa</p> 
                                                            <p>{{ $unit->lease_start ? \Carbon\Carbon::parse($unit->lease_start)->format('d M Y') : '-' }}</p> 
                                                        </div> 
                                                        <div class="detail-item"> 
                                                            <p>Berakhir Sewa</p> 
                                                            <p>{{ $unit->lease_end ? \Carbon\Carbon::parse($unit->lease_end)->format('d M Y') : '-' }}</p> 
                                                        </div> 
                                                        <div class="detail-item"> 
                                                            <p>Status</p> 
                                                            <p>{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</p> 
                                                        </div> 
                                                    </div> 
                                                </div> 
                                            </div> 
                                        </div> 
                                        <div class="mt-6 flex justify-between items-center text-[12px] text-zinc-400"> 
                                            <p>ID: #UNIT-{{ $unit->id }}</p> 
                                            <p>Terakhir Diperbarui: {{ $unit->updated_at->format('d M Y') }}</p> 
                                        </div> 
                                    </div> 
                                </div> 

                                {{-- POPUP EDIT UNIT --}}
                                <div class="popup" id="edit-unit-{{ $unit->id }}"> 
                                    <div class="popup-overlay"></div> 
                                    <div class="popup-card popup-md text-left"> 
                                        <div class="popup-close-wrapper"> 
                                            <button class="popup-close" data-close="edit-unit-{{ $unit->id }}"> 
                                                <i class="fa-solid fa-xmark"></i> 
                                            </button> 
                                        </div> 
                                        <div class="popup-header">Ubah Unit {{ $unit->unit_number }}</div> 
                                        <form action="{{ route('units.update', $unit->id) }}" method="POST"> 
                                            @csrf 
                                            @method('PUT') 
                                            <div class="popup-body flex flex-col gap-5"> 
                                                <div> 
                                                    <div class="text-field"> 
                                                        <label class="text-field-label">Pilih Tenant</label> 
                                                        <div class="custom-dropdown w-full"> 
                                                            <div class="dropdown-selected"> 
                                                                <span class="placeholder">{{ $tenant->tenant_name }}</span> 
                                                                <i class="fa-solid fa-chevron-down text-xs"></i> 
                                                            </div> 
                                                            <div class="dropdown-options"> 
                                                                @foreach($tenants as $t) 
                                                                    <div class="option" data-value="{{ $t->id }}"> 
                                                                        {{ $t->tenant_name }} 
                                                                    </div> 
                                                                @endforeach 
                                                            </div> 
                                                            <input type="hidden" name="tenant_id" value="{{ $unit->tenant_id }}" required> 
                                                        </div> 
                                                    </div> 
                                                    <div class="grid grid-cols-2 gap-4"> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label">Nomor Unit</label> 
                                                            <input type="text" name="unit_number" class="text-field-input" value="{{ old('unit_number', $unit->unit_number) }}" required> 
                                                        </div> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label">Lantai</label> 
                                                            <input type="text" name="floor" class="text-field-input" value="{{ old('floor', $unit->floor) }}"> 
                                                        </div> 
                                                    </div> 
                                                    <div class="grid grid-cols-2 gap-4"> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label">Luas Area (m²)</label> 
                                                            <input type="number" step="0.01" name="area_size" class="text-field-input" value="{{ old('area_size', $unit->area_size) }}"> 
                                                        </div> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label">Status</label> 
                                                            <div class="custom-dropdown w-full"> 
                                                                <div class="dropdown-selected"> 
                                                                    <span class="placeholder">{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</span> 
                                                                    <i class="fa-solid fa-chevron-down text-xs"></i> 
                                                                </div> 
                                                                <div class="dropdown-options"> 
                                                                    <div class="option" data-value="1">Aktif</div> 
                                                                    <div class="option" data-value="0">Nonaktif</div> 
                                                                </div> 
                                                                <input type="hidden" name="is_active" value="{{ $unit->is_active }}"> 
                                                            </div> 
                                                        </div> 
                                                    </div> 
                                                    <div class="grid grid-cols-2 gap-4"> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label">Mulai Sewa</label> 
                                                            <input type="date" name="lease_start" class="text-field-input" value="{{ $unit->lease_start?->format('Y-m-d') }}"> 
                                                        </div> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label">Sewa Berakhir</label> 
                                                            <input type="date" name="lease_end" class="text-field-input" value="{{ $unit->lease_end?->format('Y-m-d') }}"> 
                                                        </div> 
                                                    </div> 
                                                </div> 
                                                <button type="submit" class="dark-brown-button py-4 mt-2"> Simpan Perubahan </button> 
                                            </div> 
                                        </form> 
                                    </div> 
                                </div> 
                            @endforeach 
                        </tbody> 
                    </table> 
                </div> 
            @empty 
                <div class="table-card p-10 text-center text-zinc-400 italic"> 
                    Data unit tidak ditemukan 
                </div> 
            @endforelse 
        </div> 

        {{-- BAGIAN PAGINATION DAN INFO TOTAL DATA UTUH SESUAI LOG AUDIT --}}
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

{{-- POPUP TAMBAH UNIT --}}
<div class="popup" id="addUnitModal"> 
    <div class="popup-overlay"></div> 
    <div class="popup-card popup-md"> 
        <div class="popup-close-wrapper"> 
            <button class="popup-close" data-close="addUnitModal"> 
                <i class="fa-solid fa-xmark"></i> 
            </button> 
        </div> 
        <div class="popup-header">Tambah Unit Baru</div> 
        <form action="{{ route('units.store') }}" method="POST"> 
            @csrf 
            <div class="popup-body flex flex-col gap-6 text-left"> 
                <div> 
                    <div class="text-field"> 
                        <label class="text-field-label">Pilih Tenant <span class="text-[#FA8327]">*</span></label> 
                        <div class="custom-dropdown w-full"> 
                            <div class="dropdown-selected"> 
                                <span class="placeholder">-- Pilih Tenant --</span> 
                                <i class="fa-solid fa-chevron-down text-xs"></i> 
                            </div> 
                            <div class="dropdown-options"> 
                                @foreach($tenants as $t) 
                                    <div class="option" data-value="{{ $t->id }}"> {{ $t->tenant_name }} </div> 
                                @endforeach 
                            </div> 
                            <input type="hidden" name="tenant_id" id="tenant_id_input" required> 
                        </div> 
                    </div> 
                    <div class="grid grid-cols-2 gap-4 mt-4"> 
                        <div class="text-field"> 
                            <label class="text-field-label">Nomor Unit <span class="text-[#FA8327]">*</span></label> 
                            <input type="text" name="unit_number" class="text-field-input" placeholder="Misal: 2A" required> 
                        </div> 
                        <div class="text-field"> 
                            <label class="text-field-label">Lantai <span class="text-[#FA8327]">*</span></label> 
                            <input type="text" name="floor" class="text-field-input" placeholder="Misal: 2" required> 
                        </div> 
                    </div> 
                    <div class="grid grid-cols-2 gap-4 mt-4"> 
                        <div class="text-field"> 
                            <label class="text-field-label">Luas Area (m²) <span class="text-[#FA8327]">*</span></label> 
                            <input type="number" step="0.01" name="area_size" class="text-field-input" placeholder="0.00" required> 
                        </div> 
                        <div class="text-field"> 
                            <label class="text-field-label">Status <span class="text-[#FA8327]">*</span></label> 
                            <div class="custom-dropdown w-full"> 
                                <div class="dropdown-selected"> 
                                    <span class="placeholder">Aktif</span> 
                                    <i class="fa-solid fa-chevron-down text-xs"></i> 
                                </div> 
                                <div class="dropdown-options"> 
                                    <div class="option" data-value="1">Aktif</div> 
                                    <div class="option" data-value="0">Nonaktif</div> 
                                </div> 
                                <input type="hidden" name="is_active" value="1"> 
                            </div> 
                        </div> 
                    </div> 
                    <div class="grid grid-cols-2 gap-4 mt-4"> 
                        <div class="text-field"> 
                            <label class="text-field-label">Mulai Sewa <span class="text-[#FA8327]">*</span></label> 
                            <input type="date" name="lease_start" class="text-field-input" required> 
                        </div> 
                        <div class="text-field"> 
                            <label class="text-field-label">Sewa Berakhir <span class="text-[#FA8327]">*</span></label> 
                            <input type="date" name="lease_end" class="text-field-input" required> 
                        </div> 
                    </div> 
                </div> 
                <button type="submit" class="dark-brown-button py-4"> Simpan Unit Baru </button> 
            </div> 
        </form> 
    </div> 
</div> 

{{-- POPUP CONFIRM DELETE UNIT --}}
<div class="popup" id="delete-unit-modal"> 
    <div class="popup-overlay"></div> 
    <div class="popup-card popup-md"> 
        <div class="popup-close-wrapper"> 
            <button class="popup-close" data-close="delete-unit-modal"> 
                <i class="fa-solid fa-xmark"></i> 
            </button> 
        </div> 
        <div class="popup-header">Hapus Unit <span id="display-unit-number" class="text-[#FA8327]"></span>?</div> 
        <div class="popup-body"> 
            <div class="btn-delete-wrapper flex gap-3"> 
                <button class="dark-brown-button flex-1" data-close="delete-unit-modal">Batal</button> 
                <button id="confirm-delete-btn" class="light-brown-btn flex-1">Ya, Hapus</button> 
            </div> 
        </div> 
    </div> 
</div> 
@endsection