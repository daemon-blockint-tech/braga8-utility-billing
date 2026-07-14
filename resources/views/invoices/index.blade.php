@extends('layouts.app') 

@section('content') 
<div class="min-h-screen"> 
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8"> 
        <div> 
            <h1 class="title-text">Tagihan</h1> 
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

    @if (session('success')) 
        <div id="universal-success-alert" class="fixed top-6 right-6 z-[9999] flex items-center justify-between p-5 min-w-[380px] text-white border border-white/20 rounded-2xl backdrop-blur-md shadow-[0_10px_40px_rgba(0,0,0,0.5)] transition-all duration-500" style="background-color: rgba(96, 35, 22, 0.6);"> 
            <div class="flex items-center gap-4"> 
                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/10 border border-white/10 shadow-inner"> 
                    <i class="fa-solid fa-circle-check text-[#FA8327] text-lg"></i> 
                </div> 
                <div class="flex flex-col gap-0.5"> 
                    <p class="text-sm font-bold tracking-wide">Berhasil!</p> 
                    <p class="text-xs text-white/50 font-light">{{ session('success') }}</p> 
                </div> 
            </div> 
            <button type="button" onclick="closeSuccessAlert()" class="p-2 text-white/20 hover:text-[#FA8327] transition-colors"> 
                <i class="fa-solid fa-xmark text-lg"></i> 
            </button> 
        </div> 
        <script> 
            function closeSuccessAlert() { 
                const alert = document.getElementById('universal-success-alert'); 
                if (alert) { 
                    alert.style.opacity = '0'; 
                    alert.style.transform = 'translateX(30px)'; 
                    setTimeout(() => alert.remove(), 500); 
                } 
            } 
            setTimeout(closeSuccessAlert, 4500); 
        </script> 
    @endif 

    @if ($errors->any()) 
        <div id="error-alert" class="fixed top-6 right-6 z-[9999] flex items-start justify-between p-5 min-w-[380px] text-white border border-white/10 rounded-2xl backdrop-blur-md shadow-[0_10px_40px_rgba(0,0,0,0.5)] transition-all duration-500" style="background-color: rgba(60, 0, 0, 0.75); border: 1px solid rgba(255, 255, 255, 0.1);"> 
            <div class="flex items-start gap-4"> 
                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-red-950/40 border border-white/5 shadow-inner shrink-0"> 
                    <i class="fa-solid fa-circle-exclamation text-red-500 text-lg"></i> 
                </div> 
                <div class="flex flex-col gap-1"> 
                    <p class="text-sm font-bold tracking-wide text-red-100">Terjadi Kesalahan!</p> 
                    <ul class="list-none p-0 m-0"> 
                        @foreach ($errors->all() as $error) 
                            <li class="text-xs text-white/70 font-light">{{ $error }}</li> 
                        @endforeach 
                    </ul> 
                </div> 
            </div> 
            <button type="button" onclick="closeErrorAlert()" class="p-2 text-white/20 hover:text-red-400 transition-colors"> 
                <i class="fa-solid fa-xmark text-lg"></i> 
            </button> 
        </div> 
        <script> 
            function closeErrorAlert() { 
                const alert = document.getElementById('error-alert'); 
                if (alert) { 
                    alert.style.opacity = '0'; 
                    alert.style.transform = 'translateX(30px)'; 
                    setTimeout(() => alert.remove(), 500); 
                } 
            } 
            setTimeout(closeErrorAlert, 4500); 
        </script> 
    @endif 

    <div class="flex flex-col gap-6"> 
        <div class="toolbar"> 
            <form method="GET" action="{{ route('invoices.index') }}" class="search-wrapper"> 
                <input type="text" name="search" placeholder="Cari Tagihan.." value="{{ request('search') }}"> 
                <span><i class="fa-solid fa-magnifying-glass"></i></span> 
            </form> 
            <div class="toolbar-action"> 
                <button class="light-brown-btn btn-small" data-popup="add-invoices"> 
                    <span><i class="fa-solid fa-plus"></i></span> 
                    <span>Buat Tagihan</span> 
                </button> 
            </div> 
        </div> 

        <div class="table-wrapper"> 
            @forelse($invoices->groupBy('tenant.tenant_name') as $tenantName => $groupedInvoices) 
                <div class="table-card mb-4"> 
                    <div class="table-card-header"> 
                        <div class="table-card-title"> 
                            <span class="label">Penyewa:</span> 
                            <span class="value">{{ $tenantName }}</span> 
                        </div> 
                        <div class="table-card-meta"> 
                            {{ $groupedInvoices->count() }} Tagihan 
                        </div> 
                    </div> 
                    <table class="table"> 
                        <thead> 
                            <tr> 
                                <th>Bulan</th> 
                                <th>No. Invoice</th> 
                                <th>Unit</th> 
                                <th>Total</th> 
                                <th>Status</th> 
                                <th>Aksi</th> 
                            </tr> 
                        </thead> 
                        <tbody> 
                            @foreach($groupedInvoices as $invoice) 
                                <tr> 
                                    <td> 
                                        {{ $invoice->created_at ? $invoice->created_at->translatedFormat('F Y') : '-' }} 
                                    </td> 
                                    <td>{{ $invoice->invoice_number }}</td> 
                                    <td>{{ $invoice->unit->unit_number }}</td> 
                                    <td>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td> 
                                    <td class="status"> 
                                        @if($invoice->notified_at) 
                                            <button class="dark-green-btn">Terkirim</button> 
                                        @else 
                                            <button class="red-btn">Belum Terkirim</button> 
                                        @endif 
                                    </td> 
                                    <td class="actions"> 
                                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 w-full"> 
                                            {{-- WHATSAPP LOGIC --}}
                                            @if($invoice->notified_at) 
                                                @php 
                                                    $isCooldown = $invoice->notified_at && $invoice->notified_at->diffInDays(now()) < 2; 
                                                @endphp 
                                                @if($isCooldown) 
                                                    <div class="light-grey-btn-action opacity-50 cursor-not-allowed flex items-center justify-center gap-1 py-1"> 
                                                        <i class="fa-solid fa-clock-rotate-left text-zinc-400"></i> 
                                                        <span class="text-[9px] font-bold">Wait {{ ceil(2 - $invoice->notified_at->diffInDays(now())) }}d</span> 
                                                    </div> 
                                                @else 
                                                    <a href="{{ route('invoices.notify', $invoice->id) }}" target="_blank" onclick="setTimeout(() => { window.location.reload(); }, 1000);" class="light-grey-btn-action flex items-center justify-center gap-1.5 bg-[#FA8327]/10 border border-[#FA8327]/30 text-[#FA8327]"> 
                                                        <span><i class="fa-brands fa-whatsapp"></i></span> 
                                                        <span>Ingatkan</span> 
                                                    </a> 
                                                @endif 
                                            @else 
                                                <a href="{{ route('invoices.notify', $invoice->id) }}" target="_blank" onclick="setTimeout(() => { window.location.reload(); }, 1000);" class="light-grey-btn-action flex items-center justify-center gap-1.5"> 
                                                    <span><i class="fa-brands fa-whatsapp"></i></span> 
                                                    <span>Kirim</span> 
                                                </a> 
                                            @endif 

                                            <a href="{{ route('invoices.show', $invoice) }}" class="light-green-btn-action"> 
                                                <span><i class="fa-solid fa-eye"></i></span> 
                                                <span>Lihat</span> 
                                            </a> 

                                            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="light-brown-btn-action"> 
                                                <span><i class="fa-solid fa-file-pdf"></i></span> 
                                                <span>Ekspor PDF</span> 
                                            </a> 

                                            <form id="delete-form-{{ $invoice->id }}" action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline"> 
                                                @csrf 
                                                @method('DELETE') 
                                                <button type="button" class="dark-brown-btn-action" data-popup="delete-invoice" data-id="{{ $invoice->id }}" data-invoice="{{ $invoice->invoice_number }}"> 
                                                    <span><i class="fa-solid fa-trash"></i></span> 
                                                    <span>Hapus</span> 
                                                </button> 
                                            </form> 
                                        </div> 
                                    </td> 
                                </tr> 
                            @endforeach 
                        </tbody> 
                    </table> 
                </div> 
            @empty 
                <div class="table-card p-10 text-center text-zinc-400 italic"> 
                    Data tagihan tidak ditemukan 
                </div> 
            @endforelse 
        </div> 

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-2"> 
            <div class="text-sm text-zinc-500"> 
                Menampilkan <span class="text-white">{{ $invoices->firstItem() }}</span> sampai <span class="text-white">{{ $invoices->lastItem() }}</span> dari <span class="text-white">{{ $invoices->total() }}</span> hasil 
            </div> 
            <div class="braga-pagination"> 
                {{ $invoices->links('pagination::bootstrap-4') }} 
            </div> 
        </div> 
    </div> 

    <div class="popup" id="add-invoices"> 
        <div class="popup-overlay"></div> 
        <div class="popup-card popup-md text-left"> 
            <div class="popup-close-wrapper"> 
                <button class="popup-close" data-close="add-invoices"> 
                    <i class="fa-solid fa-xmark"></i> 
                </button> 
            </div> 
            <div class="popup-header">Buat Tagihan Baru</div> 
            <div class="popup-body"> 
                <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm"> 
                    @csrf 
                    <div class="flex flex-col gap-6"> 
                        <div> 
                            <div class="text-field"> 
                                <label class="text-field-label">Pilih Penyewa</label> 
                                <div class="custom-dropdown" id="tenantDropdown"> 
                                    <div class="dropdown-selected"> 
                                        <span class="placeholder">-- Pilih Penyewa --</span> 
                                        <i class="fa-solid fa-angle-down"></i> 
                                    </div> 
                                    <div class="dropdown-options"> 
                                        @foreach($tenants as $tenant) 
                                            <div class="option" data-value="{{ $tenant->id }}"> 
                                                {{ $tenant->tenant_name }} 
                                            </div> 
                                        @endforeach 
                                    </div> 
                                    <input type="hidden" name="tenant_id" id="tenant_id_input" required> 
                                </div> 
                            </div> 
                            <div class="text-field"> 
                                <label class="text-field-label">Pilih Unit</label> 
                                <div class="custom-dropdown" id="unitDropdown"> 
                                    <div class="dropdown-selected"> 
                                        <span class="placeholder">-- Pilih Unit --</span> 
                                        <i class="fa-solid fa-angle-down"></i> 
                                    </div> 
                                    <div class="dropdown-options" id="unitOptionsContainer"> 
                                        @foreach($units as $unit) 
                                            <div class="option" data-value="{{ $unit->id }}" data-tenant="{{ $unit->tenant_id ?? ($unit->tenant->id ?? '') }}"> 
                                                {{ $unit->unit_number }} (Lantai {{ $unit->floor }}) 
                                            </div> 
                                        @endforeach 
                                    </div> 
                                    <input type="hidden" name="unit_id" id="unit_id_input" required> 
                                </div> 
                                <p class="text-[10px] text-zinc-400 mt-1 italic">Pastikan meteran sudah di-input untuk unit ini.</p> 
                            </div> 
                            <div class="grid grid-cols-2 gap-4 mt-2"> 
                                <div class="text-field"> 
                                    <label class="text-field-label">Periode Tagihan</label> 
                                    <input type="text" class="text-field-input bg-zinc-100 opacity-70" value="{{ now()->translatedFormat('F Y') }}" readonly> 
                                </div> 
                                <div class="text-field"> 
                                    <label class="text-field-label text-blue-600">Biaya Manual (Opsional)</label> 
                                    <input type="number" name="manual_other_fee" class="text-field-input" placeholder="Rp 0" min="0"> 
                                </div> 
                            </div> 
                        </div> 
                        <button type="submit" class="dark-brown-button flex-1 py-3"> Buat Tagihan </button> 
                    </div> 
                </form> 
            </div> 
        </div> 
    </div> 

    <div class="popup" id="delete-invoice"> 
        <div class="popup-overlay"></div> 
        <div class="popup-card popup-md"> 
            <div class="popup-close-wrapper"> 
                <button class="popup-close" data-close="delete-invoice"> 
                    <i class="fa-solid fa-xmark"></i> 
                </button> 
            </div> 
            <div class="popup-header">Hapus Invoice <span id="display-invoice-number" class="text-[#FA8327]"></span>?</div> 
            <div class="popup-body btn-delete-wrapper"> 
                <button class="dark-brown-button" data-close="delete-invoice"> Tidak </button> 
                <button id="confirm-delete-btn" class="light-brown-btn"> Ya, Hapus </button> 
            </div> 
        </div> 
    </div> 
</div> 
@endsection