<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #333; 
            line-height: 1.4; 
        }
        
        .kop-table { 
            width: 100%; 
            border-bottom: 2px solid #FA8327; 
            margin-bottom: 20px; 
            padding-bottom: 15px; 
        }
        .kop-table td {
            border: none !important;
            padding: 0 !important;
        }
        .logo-cell { width: 60px; text-align: left; vertical-align: middle; }
        .company-info { text-align: left; vertical-align: middle; padding-left: 10px !important; }
        .company-name { font-size: 18px; font-weight: bold; color: #333; letter-spacing: 1px; margin: 0; }
        .company-sub { font-size: 9px; color: #FA8327; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; }
        .company-address { font-size: 9px; color: #666; line-height: 1.3; }

        .meta-table { 
            width: 100%; 
            margin-bottom: 25px; 
        }
        .meta-table td { 
            border: none !important; 
            padding: 0 !important; 
            vertical-align: top;
        }
        .invoice-title { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 5px; }
        .invoice-number { font-size: 11px; color: #666; font-weight: bold; }
        .period-badge { 
            display: inline-block; 
            padding: 5px 12px; 
            background: #FA8327; 
            color: white; 
            border-radius: 20px; 
            font-size: 9px; 
            font-weight: bold; 
        }

        .info-block {
            margin-top: 10px;
            font-size: 10px;
            color: #555;
        }
        .info-block table { width: 100%; }
        .info-block td { border: none !important; padding: 2px 0 !important; }
        .info-block .label { width: 70px; color: #888; text-transform: uppercase; font-size: 9px; }

        table.main-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.main-table th { 
            background-color: #333; 
            color: white; 
            padding: 9px 10px; 
            border: 1px solid #333; 
            text-align: left; 
            font-size: 10px; 
            text-transform: uppercase;
        }
        table.main-table td { padding: 9px 10px; border: 1px solid #eee; vertical-align: middle; }
        
        .total-row td {
            background: #FFF4ED;
            border-top: 1.5px solid #FA8327 !important;
            border-bottom: 1.5px solid #FA8327 !important;
        }
        .total-text { color: #FA8327 !important; font-size: 12px; }

        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .footer { 
            position: fixed; 
            bottom: 0; 
            width: 100%; 
            font-size: 8px; 
            text-align: center; 
            color: #bbb; 
            padding-bottom: 10px; 
            border-top: 1px solid #eee; 
            padding-top: 10px; 
        }
    </style>
</head>
<body>

    <table class="kop-table">
        <tr>
            <td class="logo-cell">
                <img src="data:image/svg+xml;base64,<?php echo base64_encode(file_get_contents(public_path('app-logo.svg'))); ?>" style="width: 44px;">
            </td>
            <td class="company-info">
                <div class="company-sub">Property Management System</div>
                <div class="company-name">PT EIGHT PROPERTY INDONESIA</div>
                <div class="company-address">
                    Gedung Braga 8, Jl. Braga No. 8, Sumur Bandung<br>
                    Kota Bandung, Jawa Barat 40111<br>
                    Email: finance@braga8.co.id | Telp: (022) 123-4567
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td width="60%">
                <div class="invoice-title">OFFICIAL INVOICE</div>
                <div class="invoice-number">No: {{ $invoice->invoice_number }}</div>
                
                <div class="info-block" style="margin-top: 15px;">
                    <table>
                        <tr>
                            <td class="label">Ditujukan Ke</td>
                            <td>: <span class="font-bold" style="color: #333;">{{ $invoice->tenant->tenant_name }}</span></td>
                        </tr>
                        <tr>
                            <td class="label">No. Unit</td>
                            <td>: <span class="font-bold">{{ $invoice->unit->unit_number }}</span></td>
                        </tr>
                    </table>
                </div>
            </td>
            <td width="40%" class="text-right">
                <div class="period-badge">
                    PERIODE: {{ \Carbon\Carbon::parse($invoice->billing_period_start)->translatedFormat('F Y') }}
                </div>
                <div class="info-block" style="margin-top: 18px; text-align: right;">
                    <table>
                        <tr>
                            <td class="text-right" style="color: #888; font-size: 9px; text-transform: uppercase;">Tanggal Cetak:</td>
                            <td width="90px" class="text-right font-bold" style="padding-left: 5px !important;">
                                {{ now()->timezone('Asia/Jakarta')->format('d M Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right" style="color: #888; font-size: 9px; text-transform: uppercase;">Status Tagihan:</td>
                            <td class="text-right font-bold" style="color: {{ $invoice->status === 'paid' ? '#10B981' : '#EF4444' }}; padding-left: 5px !important; text-transform: uppercase;">
                                {{ $invoice->status }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="70%">Deskripsi Tagihan</th>
                <th width="30%" class="text-right">Jumlah (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td style="color: #444;">{{ $item->description }}</td>
                    <td class="text-right font-bold" style="color: #333;">
                        Rp {{ number_format($item->amount, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            
            <tr class="total-row">
                <td class="text-right font-bold total-text">TOTAL TAGIHAN</td>
                <td class="text-right font-bold total-text">
                    Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generated by Braga 8 System | Official Invoice Document | Printed: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
    </div>

</body>
</html>