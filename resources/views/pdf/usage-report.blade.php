<!DOCTYPE html>
<html>
<head>
    <title>Usage Report - {{ $report->month_year }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        
        .kop-table { width: 100%; border-bottom: 2px solid #FA8327; margin-bottom: 20px; padding-bottom: 15px; }
        .logo-cell { width: 80px; text-align: left; vertical-align: middle; }
        .company-info { text-align: left; vertical-align: middle;}
        .company-name { font-size: 20px; font-weight: bold; color: #333; letter-spacing: 1px; margin: 0; }
        .company-sub { font-size: 10px; color: #FA8327; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
        .company-address { font-size: 9px; color: #666; line-height: 1.3; }

        .report-title { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 5px; }
        .period-badge { display: inline-block; padding: 4px 12px; background: #FA8327; color: white; border-radius: 20px; font-size: 10px; font-weight: bold; }
        
        .stats-container { width: 100%; margin-bottom: 25px; }
        .stat-box { 
            width: 23%; float: left; padding: 12px 0; border: 1px solid #eee; 
            text-align: center; margin-right: 1.2%; border-radius: 6px; 
        }
        .stat-box strong { display: block; font-size: 9px; color: #999; text-transform: uppercase; margin-bottom: 4px; }
        .stat-box span { font-size: 12px; font-weight: bold; }
        
        .total-box { background: #FFF4ED; border: 1.5px solid #FA8327; }
        .total-text { color: #FA8327 !important; }
        
        .clear { clear: both; }

        table.main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.main-table th { background-color: #333; color: white; padding: 10px; border: 1px solid #333; text-align: left; font-size: 10px; }
        table.main-table td { padding: 10px; border: 1px solid #eee; vertical-align: middle; }
        
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 8px; text-align: center; color: #bbb; padding-bottom: 10px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <table class="kop-table">
        <tr>
            <td class="logo-cell">
                <img src="data:image/svg+xml;base64,<?php echo base64_encode(file_get_contents(public_path('app-logo.svg'))); ?>" 
                    style="width: 44px;">
            </td>
            <td class="company-info">
                <div class="company-sub">Property Management System</div>
                <div class="company-name">PT EIGHT PROPERTY INDONESIA</div>
                <div class="company-address">
                    Gedung Braga 8, Jl. Braga No. 8, Sumur Bandung<br>
                    Kota Bandung, Jawa Barat 40111<br>
                    Email: admin@braga8.co.id | Telp: (022) 123-4567
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 25px;">
        <tr>
            <td style="text-align: left; vertical-align: middle;">
                <div style="font-size: 16px; font-weight: bold; color: #333;">MONTHLY USAGE REPORT</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="period-badge">PERIODE: {{ \Carbon\Carbon::parse($report->month_year)->format('F Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="stats-container">
        <div class="stat-box">
            <strong>Electricity</strong>
            <span>{{ number_format($report->total_electric_usage) }} kWh</span>
        </div>
        <div class="stat-box">
            <strong>Water</strong>
            <span>{{ number_format($report->total_water_usage) }} m³</span>
        </div>
        <div class="stat-box">
            <strong>Units Billed</strong>
            <span>{{ $report->total_units_billed }} Units</span>
        </div>
        <div class="stat-box total-box">
            <strong class="total-text">TOTAL REVENUE</strong>
            <span class="total-text">Rp {{ number_format($report->total_revenue_expected, 0, ',', '.') }}</span>
        </div>
        <div class="clear"></div>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="15%">UNIT</th>
                <th width="35%">TENANT</th>
                <th width="25%">INVOICE NO</th>
                <th width="25%" class="text-right">TOTAL AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td class="font-bold">{{ $inv->unit->unit_number ?? '-' }}</td>
                    <td>{{ $inv->tenant->tenant_name ?? 'N/A' }}</td>
                    <td style="color: #666;">{{ $inv->invoice_number }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999; padding: 30px; font-style: italic;">
                        Belum ada data invoice untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by Braga 8 System | Official Invoice Report | Printed: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
    </div>
</body>
</html>