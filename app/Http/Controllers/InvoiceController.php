<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\MeterReading;
use App\Models\Notification;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['tenant', 'unit']);

        if ($request->filled('search')) {
            $search = $request->search;

            $bulanIndo = [
                'januari'   => 1,  'jan' => 1,
                'februari'  => 2,  'feb' => 2,
                'maret'     => 3,  'mar' => 3,
                'april'     => 4,  'apr' => 4,
                'mei'       => 5,
                'juni'      => 6,  'jun' => 6,
                'juli'      => 7,  'jul' => 7,
                'agustus'   => 8,  'agu' => 8, 'ags' => 8,
                'september' => 9,  'sep' => 9,
                'oktober'   => 10, 'okt' => 10,
                'november'  => 11, 'nov' => 11,
                'desember'  => 12, 'des' => 12,
            ];

            $searchLower = strtolower($search);
            $targetMonth = null;
            $targetYear  = null;

            foreach ($bulanIndo as $namaBulan => $angkaBulan) {
                if (str_contains($searchLower, $namaBulan)) {
                    $targetMonth = $angkaBulan;
                    break;
                }
            }

            if (preg_match('/\b\d{4}\b/', $search, $matches)) {
                $targetYear = $matches[0];
            }

            $query->where(function ($q) use ($search, $targetMonth, $targetYear) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($q) use ($search) {
                        $q->where('tenant_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('unit', function ($q) use ($search) {
                        $q->where('unit_number', 'like', "%{$search}%");
                    });

                if ($targetMonth) {
                    $q->orWhere(function ($subQuery) use ($targetMonth, $targetYear) {
                        $subQuery->whereMonth('created_at', $targetMonth);
                        if ($targetYear) {
                            $subQuery->whereYear('created_at', $targetYear);
                        }
                    });
                }
            });
        }

        $invoices = $query->latest()->paginate(10)->appends($request->all());
        $tenants  = Tenant::orderBy('tenant_name')->get();
        $units    = Unit::with(['meters.tariff', 'tenant'])->get();

        return view('invoices.index', compact('invoices', 'tenants', 'units'));
    }

    public function create()
    {
        $tenants = Tenant::all();
        $units   = Unit::all();

        return view('invoices.create', compact('tenants', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'unit_id'   => 'required|exists:units,id',
        ]);

        $unit   = Unit::with(['meters.tariff'])->findOrFail($request->unit_id);
        $tenant = Tenant::findOrFail($request->tenant_id);

        $startDate = Carbon::now()->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        $elecMeter  = $unit->meters->where('meter_type', 'electricity')->first();
        $waterMeter = $unit->meters->where('meter_type', 'water')->first();

        $checkReadingExists = function ($meterId) {
            if (!$meterId) return false;
            return MeterReading::where('meter_id', $meterId)
                ->where('status', 'checked')
                ->exists();
        };

        $errorsList = [];
        if ($elecMeter && !$checkReadingExists($elecMeter->id)) {
            $errorsList[] = "Data meteran LISTRIK belum di-input atau belum dikonfirmasi oleh admin untuk unit ini.";
        }
        if ($waterMeter && !$checkReadingExists($waterMeter->id)) {
            $errorsList[] = "Data meteran AIR belum di-input atau belum dikonfirmasi oleh admin untuk unit ini.";
        }

        if (!empty($errorsList)) {
            return back()->withErrors($errorsList)->withInput();
        }

        $readings = $this->calculateUsage($unit);

        if (isset($readings['error'])) {
            return back()->withErrors($readings['error'])->withInput();
        }

        $elecTariff  = $elecMeter  ? $elecMeter->tariff  : null;
        $waterTariff = $waterMeter ? $waterMeter->tariff : null;
        $activeTariff = $elecTariff ?? $waterTariff;

        if (!$activeTariff) {
            return back()->withErrors('Error: Data Master Tarif belum di-set untuk meteran di unit ini.')->withInput();
        }

        $waterUsage    = $readings['water_usage'];
        $electricUsage = $readings['electric_usage'];

        $waterCost    = $waterUsage    * ($waterTariff->water_price    ?? 0);
        $electricCost = $electricUsage * ($elecTariff->electric_price  ?? 0);

        $otherFee = $request->filled('manual_other_fee')
            ? $request->manual_other_fee
            : ($activeTariff->other_fee ?? 0);

        $subtotal = $waterCost + $electricCost
            + ($activeTariff->electric_load_cost      ?? 0)
            + ($activeTariff->transformer_maintenance ?? 0)
            + ($activeTariff->admin_fee               ?? 0)
            + ($activeTariff->stamp_fee               ?? 0)
            + $otherFee;

        $taxRaw             = ($subtotal * ($activeTariff->tax_percent ?? 0)) / 100;
        $grandTotalRaw      = $subtotal + $taxRaw;
        $totalRounded       = round($grandTotalRaw / 1000) * 1000;
        $roundingAdjustment = $totalRounded - $grandTotalRaw;

        return DB::transaction(function () use (
            $tenant, $unit, $startDate, $endDate,
            $totalRounded, $waterCost, $electricCost,
            $activeTariff, $otherFee, $taxRaw,
            $waterUsage, $electricUsage, $roundingAdjustment
        ) {
            $invoice = Invoice::create([
                'tenant_id'            => $tenant->id,
                'unit_id'              => $unit->id,
                'invoice_number'       => 'INV-' . strtoupper(bin2hex(random_bytes(4))),
                'billing_period_start' => $startDate,
                'billing_period_end'   => $endDate,
                'total_amount'         => $totalRounded,
                'status'               => 'unpaid',
            ]);

            Notification::create([
                'user_id' => $tenant->user_id,
                'title'   => 'New Invoice',
                'message' => "Invoice {$invoice->invoice_number} bulan "
                    . $startDate->translatedFormat('F Y') . " sudah terbit.",
                'type'    => 'invoice',
            ]);

            $items = [
                ['description' => "Pemakaian Air ($waterUsage m3)",          'amount' => $waterCost],
                ['description' => "Pemakaian Listrik ($electricUsage kWh)",  'amount' => $electricCost],
                ['description' => 'Biaya Beban Listrik', 'amount' => $activeTariff->electric_load_cost      ?? 0],
                ['description' => 'Pemeliharaan Trafo',  'amount' => $activeTariff->transformer_maintenance ?? 0],
                ['description' => 'Administrasi',        'amount' => $activeTariff->admin_fee               ?? 0],
                ['description' => 'Materai',             'amount' => $activeTariff->stamp_fee               ?? 0],
                ['description' => 'Lain-lain',           'amount' => $otherFee],
                ['description' => 'PPN (' . ($activeTariff->tax_percent ?? 0) . '%)', 'amount' => $taxRaw],
            ];

            if ($roundingAdjustment != 0) {
                $items[] = ['description' => 'Pembulatan', 'amount' => $roundingAdjustment];
            }

            foreach ($items as $item) {
                $invoice->items()->create($item);
            }

            return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dibuat!');
        });
    }

    private function calculateUsage($unit)
    {
        $elecMeter  = $unit->meters->where('meter_type', 'electricity')->first();
        $waterMeter = $unit->meters->where('meter_type', 'water')->first();

        if (!$elecMeter && !$waterMeter) {
            return ['error' => "Unit ini belum dikonfigurasi memiliki meteran air maupun listrik."];
        }

        $getReadings = function ($meterId) {
            if (!$meterId) return collect();
            return MeterReading::where('meter_id', $meterId)
                ->where('status', 'checked')
                ->orderBy('recorded_at', 'desc')
                ->limit(2)
                ->get();
        };

        $eReadings = $elecMeter  ? $getReadings($elecMeter->id)  : collect();
        $wReadings = $waterMeter ? $getReadings($waterMeter->id) : collect();

        $electricUsage = 0;
        if ($eReadings->count() > 0) {
            $elecMultiplier = $elecMeter->multiplier ?? 1;
            $electricUsage  = $eReadings->count() >= 2
                ? ($eReadings[0]->reading_value - $eReadings[1]->reading_value) * $elecMultiplier
                : $eReadings[0]->reading_value * $elecMultiplier;
        }

        $waterUsage = 0;
        if ($wReadings->count() > 0) {
            $waterMultiplier = $waterMeter->multiplier ?? 1;
            $waterUsage      = $wReadings->count() >= 2
                ? ($wReadings[0]->reading_value - $wReadings[1]->reading_value) * $waterMultiplier
                : $wReadings[0]->reading_value * $waterMultiplier;
        }

        return [
            'electric_usage' => max(0, $electricUsage),
            'water_usage'    => max(0, $waterUsage),
        ];
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['tenant', 'unit', 'items']);
        return view('invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['tenant', 'unit', 'items']);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate(['status' => 'required|in:unpaid,paid,canceled']);
        $invoice->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Status updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Tagihan Berhasil di Hapus.');
    }

    public function notifyTenant(Invoice $invoice)
    {
        $tenant  = $invoice->tenant;
        $phone   = $tenant->contact_phone;
        $picName = $tenant->person_in_charge;

        if (!$phone) {
            return back()->with('error', 'No PIC phone number found for this tenant.');
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $itemsList = "";
        foreach ($invoice->items as $item) {
            $itemsList .= "• " . $item->description . ": Rp " . number_format($item->amount) . "\n";
        }

        $message = "*TAGIHAN INVOICE: " . $invoice->invoice_number . "*\n"
            . "Gedung Braga 8\n"
            . "--------------------------\n"
            . "*PIC:* " . $picName . "\n"
            . "*Unit:* " . $invoice->unit->unit_number . "\n"
            . "--------------------------\n"
            . "*Detail Pemakaian:*\n"
            . $itemsList
            . "--------------------------\n"
            . "*TOTAL TAGIHAN: Rp " . number_format($invoice->total_amount) . "*\n"
            . "--------------------------\n\n"
            . "Silahkan cek detail lengkap di aplikasi. Terima kasih.";

        $invoice->update(['notified_at' => now()]);
        $waUrl = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($message);

        return redirect()->away($waUrl);
    }
}