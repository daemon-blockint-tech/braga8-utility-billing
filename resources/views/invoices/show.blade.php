@extends('layouts.app')

@section('content')

<div class="min-h-screen">

    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8">
        <div>
            <h1 class="title-text">Detail Tagihan</h1>
            <p class="subtitle-text">Braga8 Utility Billing Management</p>
        </div>
        <div class="header-user">
            <div class="icon-wrapper" data-popup="notif-popup">
                <i class="fa-solid fa-bell"></i>
                <span class="notif-dot"></span>
            </div>
            <div class="profile-container" data-popup="detail-profile-popup">
                <div class="profile-icon">
                    <i class="fa-solid fa-user text-2xl text-[#a04d30]"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6">

        <div class="toolbar">
            <div class="toolbar-action"> 
                <a href="{{ route('invoices.index') }}" class="dark-brown-button btn-small inline-flex items-center gap-2"> 
                    <i class="fa-solid fa-angle-left"></i>
                    <span>Kembali</span>
                </a> 
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6"">
            <div>
                <table class="invoice-items">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>{{ $invoice->tenant->tenant_name }}</th>
                        </tr>
                    </thead>

                    <tbody>

                            <tr>
                                <td>Invoice No</td>
                                <td>
                                    {{ $invoice->invoice_number }}
                                </td>
                            </tr>
                            <tr>
                                <td>Unit</td>
                                <td>
                                    {{ $invoice->unit->unit_number }}
                                </td>
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td>
                                    {{ $invoice->unit->unit_number }}
                                </td>
                            </tr>
                            <tr>
                                <td>Masa Periode</td>
                                <td>
                                    {{ $invoice->billing_period_start }}
                                    -
                                    {{ $invoice->billing_period_end }}
                                </td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td class="status">
                                    <button class="
                                        {{ $invoice->status == 'paid'
                                            ? 'dark-green-btn'
                                            : ($invoice->status == 'pending'
                                                ? 'light-brown-btn-action'
                                                : 'red-btn') }}
                                        pointer-events-none
                                    ">
                                        {{ strtoupper($invoice->status) }}
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Aksi</td>
                                <td class="actions">
                                    <div class="flex items-center justify-center gap-2">

                                        <a href="{{ route('invoices.pdf', $invoice) }}"
                                        class="light-brown-btn-action">
                                            <span>
                                                <i class="fa-solid fa-download"></i>
                                            </span>
                                            <span>Download PDF</span>
                                        </a>

                                    </div>
                                </td>
                            </tr>

                    </tbody>
                </table>
            </div>

            <div>
                <table class="invoice-items">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>

                                <td>
                                    Rp {{ number_format($item->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <h1 class="invoice-title-card">Meter Reading Evidence</h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="table-card p-5 flex flex-col gap-4">

                    <div class="flex items-center justify-between">

                        <h2 class="text-lg font-medium text-zinc-400">
                            Electricity Meter
                        </h2>
                    
                        <p class="text-xs text-zinc-400">
                            Latest uploaded evidence
                        </p>
                    </div>

                    @if(
                        $invoice->unit->electricityMeter &&
                        $invoice->unit->electricityMeter->latestReading
                    )

                        <img
                            src="{{ asset('storage/' . $invoice->unit->electricityMeter->latestReading->photo_path) }}"
                            class="w-full h-[320px] object-cover rounded-2xl border border-zinc-200"
                        >

                        <div class="mt-4 grid grid-cols-2 gap-4">

                            <div class="detail-item">
                                <p>Reading Value</p>
                                <p>
                                    {{ $invoice->unit->electricityMeter->latestReading->reading_value }}
                                    kWh
                                </p>
                            </div>

                            <div class="detail-item">
                                <p>Recorded At</p>
                                <p>
                                    {{ \Carbon\Carbon::parse($invoice->unit->electricityMeter->latestReading->recorded_at)->format('d M Y') }}
                                </p>
                            </div>

                        </div>

                    @else

                        <div class="flex flex-col items-center justify-center h-[320px] border border-dashed border-zinc-300 rounded-2xl text-zinc-400">
                            <i class="fa-solid fa-image text-4xl mb-4"></i>
                            <p>No electricity photo recorded.</p>
                        </div>

                    @endif

                </div>

                <div class="table-card p-5 flex flex-col gap-4">

                    <div class="flex items-center justify-between">

                            <h2 class="text-lg font-medium text-zinc-400">
                                Water Meter
                            </h2>

                            <p class="text-xs text-zinc-400">
                                Latest uploaded evidence
                            </p>

                    </div>

                    @if(
                        $invoice->unit->waterMeter &&
                        $invoice->unit->waterMeter->latestReading
                    )

                        <img
                            src="{{ asset('storage/' . $invoice->unit->waterMeter->latestReading->photo_path) }}"
                            class="w-full h-[320px] object-cover rounded-2xl border border-zinc-200"
                        >

                        <div class="mt-4 grid grid-cols-2 gap-4">

                            <div class="detail-item">
                                <p>Reading Value</p>
                                <p>
                                    {{ $invoice->unit->waterMeter->latestReading->reading_value }}
                                    m³
                                </p>
                            </div>

                            <div class="detail-item">
                                <p>Recorded At</p>
                                <p>
                                    {{ \Carbon\Carbon::parse($invoice->unit->waterMeter->latestReading->recorded_at)->format('d M Y') }}
                                </p>
                            </div>

                        </div>

                    @else

                        <div class="flex flex-col items-center justify-center h-[320px] border border-dashed border-zinc-300 rounded-2xl text-zinc-400">
                            <i class="fa-solid fa-image text-4xl mb-4"></i>
                            <p>No water photo recorded.</p>
                        </div>

                    @endif

                </div>

            </div>
        </div>
    </div>

</div>

@endsection