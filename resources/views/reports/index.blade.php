@extends('layouts.app') 

@section('content') 
<div class="min-h-screen"> 
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8"> 
        <div> 
            <h1 class="title-text">Laporan Pemakaian</h1> 
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
        </button> </div> 
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
    <div id="error-alert" class="fixed top-6 right-6 z-[9999] flex items-start justify-between p-5 min-w-[380px] text-white border border-white/10 rounded-2xl backdrop-blur-md shadow-[0_10px_40px_rgba(0,0,0,0.5)] transition-all duration-500" style="background-color: rgba(60, 0, 0, 0.75);"> 
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
        <div class="toolbar flex items-end justify-between"> 
            <div class="flex flex-col"> 
                <form method="GET" action="{{ route('reports.index') }}" class="search-wrapper"> 
                    <input type="text" name="search" placeholder="Cari Laporan.." id="tableSearch" value="{{ request('search') }}"> 
                    <span><i class="fa-solid fa-magnifying-glass"></i></span> 
                </form> 
            </div> 
            <div class="toolbar-action"> 
                <button type="button" class="light-brown-btn btn-small" data-popup="generate-report-modal"> 
                    <i class="fa-solid fa-plus mr-2"></i> <span>Buat Laporan Baru</span> 
                </button> 
            </div> 
        </div> 

        @php 
            $latest = $reports->first(); 
            \Carbon\Carbon::setLocale('id'); 
            
            $revenueStr = $latestReport ? 'Rp ' . number_format($latestReport->total_revenue_expected, 0, ',', '.') : '0';
        @endphp 
        
        <div class="card-image-container mb-2"> 
            <div class="card card-with-image"> 
                <div class="card-image"></div> 
                <div class="card-body"> 
                    <p class="card-label">Estimasi Pendapatan ({{ $latestReport ? \Carbon\Carbon::parse($latestReport->month_year)->translatedFormat('M Y') : '-' }})</p> 
                    <p class="card-value" style="{{ strlen($revenueStr) > 11 ? 'font-size: 30px;' : '' }}">
                        {{ $revenueStr }}
                    </p> 
                </div> 
            </div> 
            
            @php
                $electricStr = $latestReport ? number_format($latestReport->total_electric_usage) : '0';
            @endphp
            <div class="card card-with-image"> 
                <div class="card-image"></div> 
                <div class="card-body"> 
                    <p class="card-label">Total Penggunaan Listrik</p> 
                    <p class="card-value" style="{{ strlen($electricStr) > 8 ? 'font-size: 30px;' : '' }}">
                        {{ $electricStr }} <span class="text-xs font-normal">kWh</span>
                    </p> 
                </div> 
            </div> 
            
            @php
                $waterStr = $latestReport ? number_format($latestReport->total_water_usage) : '0';
            @endphp 
            <div class="card card-with-image"> 
                <div class="card-image"></div> 
                <div class="card-body"> 
                    <p class="card-label">Total Penggunaan Air</p> 
                    <p class="card-value" style="{{ strlen($waterStr) > 8 ? 'font-size: 30px;' : '' }}">
                        {{ $waterStr }} <span class="text-xs font-normal">m³</span>
                    </p> 
                </div> 
            </div>
        </div>  

        <div class="table-wrapper"> 
            @if($reports->count() > 0) 
            <div class="table-card mb-8"> 
                <div class="table-card-header"> 
                    <div class="table-card-title"> 
                        <span class="label">Total Laporan:</span> 
                        <span class="value">{{ $reports->total() }}</span> 
                    </div> 
                </div> 
                <table class="table"> 
                    <thead> 
                        <tr> 
                            <th>Bulan / Tahun</th> 
                            <th>Unit Ditagih</th> 
                            <th>Listrik (kWh)</th> 
                            <th>Air (m³)</th> 
                            <th>Total Pendapatan</th> 
                            <th class="text-center">Aksi</th> 
                        </tr> 
                    </thead> 
                    <tbody> 
                        @foreach($reports as $report) 
                        @php 
                            $formattedPeriod = \Carbon\Carbon::parse($report->month_year)->translatedFormat('F Y'); 
                        @endphp 
                        <tr> 
                            <td class="font-bold text-zinc-800">{{ $formattedPeriod }}</td> 
                            <td><span class="blue-btn pointer-events-none">{{ $report->total_units_billed }} Unit</span></td> 
                            <td class="text-zinc-600 font-medium">{{ number_format($report->total_electric_usage) }}</td> 
                            <td class="text-zinc-600 font-medium">{{ number_format($report->total_water_usage) }}</td> 
                            <td class="font-bold text-[#602316]"> Rp {{ number_format($report->total_revenue_expected, 0, ',', '.') }} </td> 
                            <td class="actions"> 
                                <div class="flex justify-center items-center gap-2"> 
                                    <button class="light-green-btn-action" data-popup="detail-report-{{ $report->id }}"> 
                                        <span><i class="fa-solid fa-eye"></i></span> <span>Lihat</span> 
                                    </button> 

                                    <a href="{{ route('reports.pdf', $report->id) }}" class="light-brown-btn-action" download> 
                                        <i class="fa-solid fa-file-pdf"></i> <span>Ekspor PDF</span> 
                                    </a> 

                                    <form id="delete-form-{{ $report->id }}" action="{{ route('reports.destroy', $report->id) }}" method="POST" class="m-0 p-0"> 
                                        @csrf 
                                        @method('DELETE') 
                                        <button type="button" class="dark-brown-btn-action" 
                                            data-popup="delete-report-modal" 
                                            data-id="{{ $report->id }}" 
                                            data-invoice="{{ $formattedPeriod }}"> 
                                            <span><i class="fa-solid fa-trash"></i></span> <span>Hapus</span> 
                                        </button> 
                                    </form> 
                                </div> 
                            </td> 
                        </tr> 
                        @endforeach 
                    </tbody> 
                </table> 
            </div> 
            @else 
            <div class="table-card p-10 text-center text-zinc-400 italic"> Belum ada data laporan </div> 
            @endif 
        </div> 

        @if($reports->count() > 0) 
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-2"> 
            <div class="text-sm text-zinc-500"> Showing <span class="text-white">{{ $reports->firstItem() }}</span> to <span class="text-white">{{ $reports->lastItem() }}</span> of <span class="text-white">{{ $reports->total() }}</span> results </div> 
            <div class="braga-pagination"> {{ $reports->links('pagination::bootstrap-4') }} </div> 
        </div> 
        @endif 
    </div> 
</div> 

<div class="popup" id="generate-report-modal"> 
    <div class="popup-overlay"></div> 
    <div class="popup-card popup-md text-left"> 
        <div class="popup-close-wrapper"> 
            <button class="popup-close" data-close="generate-report-modal"> <i class="fa-solid fa-xmark"></i> </button> 
        </div> 
        <div class="popup-header">Buat Laporan Bulanan</div> 
        <div class="popup-body"> 
            <form action="{{ route('reports.generate') }}" method="POST"> 
                @csrf 
                <div class="flex flex-col gap-6"> 
                    <div> 
                        <div class="text-field"> 
                            <label class="text-field-label text-left text-zinc-700">Pilih Periode Laporan <span class="text-[#FA8327]">*</span></label> 
                            <input type="month" name="month" required class="text-field-input [color-scheme:light] cursor-pointer text-zinc-800 border-zinc-300"> 
                        </div> 
                        <p class="text-xs text-zinc-400 italic mt-2"> Pilih periode bulan dan tahun untuk menghitung penggunaan listrik, air, dan estimasi pendapatan secara otomatis. </p> 
                    </div> 
                    <button type="submit" class="dark-brown-button flex-1 py-3"> Buat Sekarang </button> 
                </div> 
            </form> 
        </div> 
    </div> 
</div> 

@foreach($reports as $report) 
<div class="popup" id="detail-report-{{ $report->id }}"> 
    <div class="popup-overlay"></div> 
    <div class="popup-card popup-md"> 
        <div class="popup-close-wrapper"> 
            <button class="popup-close" data-close="detail-report-{{ $report->id }}"><i class="fa-solid fa-xmark"></i></button> 
        </div> 
        <div class="popup-header text-left">Rincian Laporan: {{ \Carbon\Carbon::parse($report->month_year)->translatedFormat('F Y') }}</div> 
        
        <div class="popup-body user-detail-info flex flex-col gap-5 text-left"> 
            
            <div class="grid grid-cols-2 gap-x-6 gap-y-4"> 
                <div class="detail-item"> 
                    <p>Total Unit Ditagih</p> 
                    <p class="text-zinc-800 font-bold">{{ $report->total_units_billed }} Unit</p> 
                </div> 
                <div class="detail-item"> 
                    <p>Estimasi Pendapatan</p> 
                    <p class="text-[#602316] font-bold">Rp {{ number_format($report->total_revenue_expected, 0, ',', '.') }}</p> 
                </div> 
                <div class="detail-item"> 
                    <p>Total Konsumsi Listrik</p> 
                    <p class="text-zinc-800 font-bold">{{ number_format($report->total_electric_usage) }} <span class="text-[10px] text-zinc-400 font-normal">kWh</span></p> 
                </div> 
                <div class="detail-item"> 
                    <p>Total Konsumsi Air</p> 
                    <p class="text-zinc-800 font-bold">{{ number_format($report->total_water_usage) }} <span class="text-[10px] text-zinc-400 font-normal">m³</span></p> 
                </div> 
            </div> 

            <div class="flex flex-col gap-2 border-t border-dashed border-zinc-200 pt-4 w-full"> 
                <p class="font-bold text-zinc-500 text-[10px] uppercase tracking-widest mb-1">Rincian Kontribusi Per Unit</p> 
                
                <div class="max-h-[160px] overflow-y-auto border border-zinc-700 rounded-xl bg-white/5 p-2.5 flex flex-col gap-2 shadow-inner"> 
                    @forelse($report->details as $inv) 
                        <div class="flex justify-between items-center w-full border-b border-zinc-100 pb-2 last:border-0 last:pb-0 px-1 rounded"> 
                            <div class="flex flex-col gap-0.5">
                                <p class="text-xs text-zinc-200 font-bold">{{ $inv->tenant->tenant_name ?? 'N/A' }}</p> 
                                <p class="text-[10px] text-zinc-400">Unit: {{ $inv->unit->unit_number ?? '-' }} &bull; {{ $inv->invoice_number }}</p>
                            </div>
                            <p class="text-xs font-bold text-[#FA8327]">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</p> 
                        </div> 
                    @empty 
                        <div class="p-6 text-center text-zinc-400 italic text-xs"> 
                            Tidak ada rincian invoice aktif pada periode ini. 
                        </div> 
                    @endforelse 
                </div> 
            </div> 

            <div class="mt-2 flex justify-between items-center text-[11px] text-zinc-400"> 
                <p>ID: #RPT-{{ $report->id }}</p> 
                <p>Dibuat Pada: {{ $report->created_at->format('d M Y') }}</p> 
            </div>
        </div> 
    </div> 
</div> 
@endforeach 

<div class="popup" id="delete-report-modal"> 
    <div class="popup-overlay"></div> 
    <div class="popup-card popup-md"> 
        <div class="popup-close-wrapper"> 
            <button class="popup-close" data-close="delete-report-modal"><i class="fa-solid fa-xmark"></i></button> 
        </div> 
        <div class="popup-header">Hapus Laporan Periode <span id="display-invoice-number" class="text-[#FA8327]"></span>?</div> 
        <div class="popup-body btn-delete-wrapper"> 
            <div class="flex items-center gap-3 w-full"> 
                <button type="button" class="dark-brown-button flex-1 py-2.5 text-xs font-bold rounded-xl" data-close="delete-report-modal">Tidak</button>
                <button id="confirm-delete-btn" class="light-brown-btn flex-1 py-2.5 text-xs font-bold rounded-xl">Ya, Hapus</button> 
            </div> 
        </div> 
    </div> 
</div> 
@endsection