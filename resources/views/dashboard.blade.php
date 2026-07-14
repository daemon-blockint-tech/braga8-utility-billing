@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="min-h-screen">
    
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 border-zinc-200 pb-8">
        <div>
            <h1 class="title-text">Dashboard</h1>
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

    <div class="flex flex-col gap-4">

        @if (session('status'))
            @php
                $alerts = [
                    'profile-updated' => [
                        'title' => 'Profil Diperbarui!',
                        'desc'  => 'Informasi akun kamu sudah berhasil diubah.',
                        'icon'  => 'fa-user-check'
                    ]
                ];
                $current = $alerts[session('status')] ?? null;
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <div class="card lg:col-span-4">
                <p class="card-label whitespace-nowrap">Total Pembayaran</p>
                <h3 class="card-value" style="{{ strlen((string)$totalPaidAmount) > 8 ? 'font-size: 30px !important;' : '' }}">
                    <span class="font-medium">Rp</span> {{ number_format($totalPaidAmount, 0, ',', '.') }}
                </h3>
                <div class="mt-2 flex items-center gap-1.5 justify-end whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <p class="card-caption">{{ $paidCount }} Pembayaran</p>
                </div>
            </div>

            <div class="card lg:col-span-4">
                <p class="card-label whitespace-nowrap">Tagihan Tertunda</p>
                <h3 class="card-value" style="{{ strlen((string)$totalUnpaidAmount) > 8 ? 'font-size: 30px !important;' : '' }}">
                    <span class="font-medium">Rp</span> {{ number_format($totalUnpaidAmount, 0, ',', '.') }}
                </h3>
                <div class="mt-2 flex items-center gap-1.5 justify-end whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    <p class="card-caption">{{ $unpaidCount }} Belum Dibayar</p>
                </div>
            </div>

            <div class="card lg:col-span-2">
                <p class="card-label whitespace-nowrap">Jumlah Penyewa</p>
                <h3 class="card-value">{{ $totalTenants }}</h3>
                <p class="card-caption text-right whitespace-nowrap">
                    +{{ $newTenantsThisMonth }}
                </p>
            </div>

            <div class="card lg:col-span-2">
                <p class="card-label whitespace-nowrap">Keluhan</p>
                <h3 class="card-value">{{ $totalComplaints }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            <div class="card lg:col-span-4">
                <p class="card-label">Ringkasan Tagihan</p>
                
                <div class="chart">
                    <div class="pie-chart" 
                        id="chart" 
                        data-paid="{{ $percentPaid }}" 
                        data-unpaid="{{ $percentUnpaid }}" 
                        data-overdue="{{ $percentOverdue }}">
                    </div>

                    <div class="chart-info">
                        <div class="item">
                            <span class="dot paid"></span>
                            <div>
                                <p>Lunas</p>
                                <h2 id="paid-val">{{ $percentPaid }}%</h2>
                            </div>
                        </div>

                        <div class="item">
                            <span class="dot unpaid"></span>
                            <div>
                                <p>Belum Dibayar</p>
                                <h2 id="unpaid-val">{{ $percentUnpaid }}%</h2>
                            </div>
                        </div>

                        <div class="item">
                            <span class="dot overdue"></span>
                            <div>
                                <p>Terlambat</p>
                                <h2 id="overdue-val">{{ $percentOverdue }}%</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card lg:col-span-3">
        
                <div class="relative z-10">
                    <p class="card-label">Input Meter Bulan Ini</p>
                    <div class="mt-6">
                        <h3>
                            <span class="card-value">{{ $metersDone }}</span>
                            <span class="metric-max">/{{ $totalMeters }}</span>
                        </h3>
                        <p class="card-caption">{{ $unitsCompleted }} Unit Selesai Bulan Ini</p>
                    </div>
                </div>

                <div class="mt-auto flex flex-col gap-6">
                    
                    <div class="relative z-10"> 
                        <div class="w-full bg-white/10 h-1.5 rounded-full">
                            <div class="bg-white h-full rounded-full transition-all duration-1000" 
                                style="width: {{ ($metersDone / max($totalMeters, 1)) * 100 }}%">
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('audit_logs.index') }}" class="light-brown-btn">
                        Log Audit
                        <i class="fa-solid fa-angle-right"></i>
                    </a>

                </div>
            </div>

            <div class="card bar-chart-container lg:col-span-5">
                <div class="bar-chart-content">
                    <p class="card-label">Grafik Pemakaian Bulanan</p>
                    
                    <div class="bar-chart-wrapper">
                        <div class="bar-chart-grid"></div>
                        
                        <div class="bar-chart-bars">
                            @forelse($chartData as $data)
                            <div class="relative flex flex-col items-center h-full">
                                <div class="flex items-end gap-1 h-full w-12 justify-center">
                                    
                                    <div class="bar-chart-bar bar-chart-bar-electricity group min-h-[8px]" 
                                        style="height: {{ $maxVal > 0 ? min(max(($data['electricity'] / $maxVal) * 100, 8), 100) : 8 }}%">
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-zinc-800 text-[10px] text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-50 transition-opacity">
                                            <i class="fa-solid fa-bolt-lightning text-orange-400"></i> {{ number_format($data['electricity'], 0, ',', '.') }} kWh
                                        </span>
                                    </div>
                                    
                                    <div class="bar-chart-bar bar-chart-bar-water group min-h-[8px]" 
                                        style="height: {{ $maxVal > 0 ? min(max(($data['water'] / $maxVal) * 100, 8), 100) : 8 }}%">
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-zinc-800 text-[10px] text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-50 transition-opacity">
                                            <i class="fa-solid fa-droplet text-orange-400"></i> {{ number_format($data['water'], 0, ',', '.') }} m³
                                        </span>
                                    </div>

                                </div>
                                <span class="absolute top-[calc(100%+8px)] bar-chart-labels">
                                    {{ $data['month'] }}
                                </span>
                            </div>
                            @empty
                            <div class="w-full flex items-center justify-center h-full text-zinc-500 italic text-xs">
                                Belum ada data laporan penggunaan.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="bar-chart-legend">
                    <div class="bar-chart-legend-item">
                        <span class="bar-chart-legend-color electricity shadow-[1px_1px_0px_#191717]"></span>
                        <div>
                            <p class="text-[9px] uppercase font-bold text-zinc-500 leading-none">Listrik</p>
                            <p class="text-white font-medium">kWh</p>
                        </div>
                    </div>
                    <div class="bar-chart-legend-item">
                        <span class="bar-chart-legend-color water shadow-[1px_1px_0px_#191717]"></span>
                        <div>
                            <p class="text-[9px] uppercase font-bold text-zinc-500 leading-none">Air</p>
                            <p class="text-white font-medium">m³</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>  
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

            <div class="card lg:col-span-3">
                <p class="card-label">Akses Cepat</p>
                <div class="button-wrapper">
                    <a href="{{ route('invoices.index') }}" class="dark-brown-button">
                        <span><i class="fa-solid fa-plus"></i></span>
                        <span>Buat Tagihan</span>
                    </a>
                    <a href="{{ route('tenants.index') }}" class="dark-brown-button">
                        <span><i class="fa-solid fa-plus"></i></span>
                        <span>Tambah Penyewa</span>
                    </a>
                    <a href="{{ route('tariffs.index') }}" class="dark-brown-button">
                        <span><i class="fa-solid fa-plus"></i></span>
                        <span>Ubah Tarif</span>
                    </a>
                </div>
            </div>

            <div class="card-body lg:col-span-9">
                <h2 class="card-label">Pemakaian Bulanan</h2>

                <div class="card-scroll">
                    <table class="usage-table">
                        <thead>
                            <tr class="sticky">
                                <th>Bulan</th>
                                <th>Pemakaian Listrik</th>
                                <th>Pemakaian Air</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($chartData as $data)
                                <tr>
                                    <td>{{ $data['month'] }}</td>
                                    <td>{{ number_format($data['electricity'], 0, ',', '.') }} kWh</td>
                                    <td>{{ number_format($data['water'], 0, ',', '.') }} L</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-zinc-500">
                                        Belum ada laporan penggunaan untuk periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    
</div>
@endsection