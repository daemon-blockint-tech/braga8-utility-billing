<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Http\Request;


class InvoiceSummaryController extends Controller
{
   public function index()
   {
       $tenants = Tenant::with([
           'invoices' => function ($q) {
               $q->with('unit')->latest('billing_period_start');
           },
       ])->get();


       $data = $tenants
           ->filter(fn($t) => $t->invoices->isNotEmpty())
           ->map(fn($tenant) => [
               'tenant_id'   => $tenant->id,
               'tenant_name' => $tenant->name,
               'invoices'    => $tenant->invoices->map(fn($inv) => [
                   'id'                   => $inv->id,
                   'invoice_number'       => $inv->invoice_number ?? '-',
                   'unit_number'          => $inv->unit?->unit_number ?? '-',
                   'total_amount'         => (float) ($inv->total_amount ?? 0),
                   'status'               => $inv->status,
                   'is_paid'              => $inv->status === 'paid',
                   'billing_period_start' => $inv->billing_period_start?->toDateString() ?? now()->toDateString(),
                   'billing_period_end'   => $inv->billing_period_end?->toDateString() ?? now()->toDateString(),
               ])->values(),
           ])->values();


       return response()->json(['data' => $data]);
   }

   public function pay(Invoice $invoice)
   {
       if ($invoice->status === 'paid') {
           return response()->json(['message' => 'Invoice sudah lunas.'], 422);
       }


       $invoice->payments()->create([
           'amount_paid' => $invoice->total_amount,
           'status'      => 'verified',
           'paid_at'     => now(),
       ]);


       $invoice->update(['status' => 'paid']);


       return response()->json(['message' => 'Pembayaran berhasil dicatat.']);
   }

   public function detail(Invoice $invoice)
   {
       $invoice->load(['items', 'unit.meters.readings' => function ($q) {
           $q->where('status', 'checked')->orderBy('recorded_at', 'desc');
       }]);


       $meterPhotos = [];
       foreach ($invoice->unit->meters as $meter) {
           $reading = $meter->readings
               ->whereBetween('recorded_at', [
                   $invoice->billing_period_start,
                   $invoice->billing_period_end->endOfDay(),
               ])
               ->first();


           if (!$reading) {
               $reading = $meter->readings->first();
           }


           if ($reading) {
               $meterPhotos[] = [
                   'meter_type'    => $meter->meter_type,
                   'reading_value' => $reading->reading_value,
                   'recorded_at'   => $reading->recorded_at,
                'photo_url' => $reading->photo_path
   ? url('storage/' . $reading->photo_path)
   : null,
               ];
           }
       }

       return response()->json([
           'items'        => $invoice->items,
           'meter_photos' => $meterPhotos,
       ]);
   }
}

