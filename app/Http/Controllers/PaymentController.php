<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\User;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $totalBill       = Invoice::sum('total_amount');
        $totalCollected  = Payment::where('status', 'verified')->sum('amount_paid');
        $outstandingBill = max(0, $totalBill - $totalCollected);

        $invoices = Invoice::with('tenant')
            ->where('status', '!=', 'paid')
            ->get();

        $query = Payment::with(['invoice.tenant', 'invoice.unit']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('paid_using', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($inv) use ($search) {
                        $inv->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('tenant', function ($tn) use ($search) {
                                $tn->where('tenant_name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $payments = $query->latest()->paginate(10)->appends($request->all());

        return view('payments.index', compact('payments', 'totalBill', 'totalCollected', 'outstandingBill', 'invoices'));
    }

    public function create()
    {
        $invoices = Invoice::with('tenant')
            ->where('status', '!=', 'paid')
            ->get();

        return view('payments.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        $invoice = Invoice::findOrFail($request->invoice_id);

        $request->validate([
            'invoice_id'   => 'required|exists:invoices,id',
            'amount_paid'  => 'required|numeric|min:' . $invoice->total_amount,
            'payment_date' => 'required|date',
            'paid_using'   => 'required|string',
            'proof_img'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'invoice_id.required'   => 'ID Invoice wajib dipilih.',
            'amount_paid.required'  => 'Jumlah pembayaran wajib diisi.',
            'amount_paid.numeric'   => 'Jumlah pembayaran harus berupa angka.',
            'amount_paid.min'       => 'Jumlah pembayaran tidak boleh kurang dari total tagihan (Rp ' . number_format($invoice->total_amount, 0, ',', '.') . ').',
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
            'paid_using.required'   => 'Metode pembayaran wajib diisi.',
            'proof_img.image'       => 'File harus berupa gambar.',
            'proof_img.mimes'       => 'Format gambar yang didukung: jpeg, png, jpg.',
            'proof_img.max'         => 'Ukuran gambar maksimal adalah 2MB.',
        ]);

        $path = $request->hasFile('proof_img')
            ? $request->file('proof_img')->store('payments', 'public')
            : null;

        Payment::create([
            'invoice_id'   => $request->invoice_id,
            'amount_paid'  => $request->amount_paid,
            'due_date'     => $invoice->billing_period_end,
            'paid_using'   => $request->paid_using,
            'status'       => 'pending',
            'payment_date' => $request->payment_date,
            'proof_img'    => $path,
            'reminded_at'  => null,
        ]);

        return redirect()->route('payments.index')->with('status', 'payment-stored');
    }

    public function edit(Payment $payment)
    {
        $payment->load('invoice.tenant');
        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'amount_paid'  => 'required|numeric',
            'status'       => 'required|in:pending,verified,rejected',
            'payment_date' => 'required|date',
            'paid_using'   => 'required|string',
            'proof_img'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'amount_paid.required'  => 'Jumlah pembayaran wajib diisi.',
            'status.required'       => 'Status pembayaran wajib dipilih.',
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
            'proof_img.max'         => 'Ukuran gambar maksimal adalah 5MB.',
        ]);

        $data = $request->only(['amount_paid', 'status', 'payment_date', 'paid_using']);

        if ($request->hasFile('proof_img')) {
            if ($payment->proof_img) Storage::disk('public')->delete($payment->proof_img);
            $data['proof_img'] = $request->file('proof_img')->store('payments', 'public');
        }

        $payment->update($data);

        if ($payment->status === 'verified') {
            $payment->invoice->update(['status' => 'paid']);

            $tenantName = $payment->invoice->tenant->tenant_name;

            foreach (User::where('role', 'admin')->get() as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title'   => 'Pembayaran Diterima',
                    'message' => "{$tenantName} telah membayar tagihan mereka.",
                    'type'    => 'payment',
                ]);
            }
        }

        return redirect()->route('payments.index')->with('status', 'payment-updated');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->proof_img) Storage::disk('public')->delete($payment->proof_img);
        $payment->delete();

        return back()->with('status', 'payment-deleted');
    }

    public function remind(Payment $payment)
    {
        if ($payment->reminded_at && now()->lessThan($payment->reminded_at->copy()->addDays(2))) {
            return back()->with('status', 'remind-cooldown');
        }

        $tenant = $payment->invoice->tenant;
        $phone  = $tenant->contact_phone;

        if (!$phone) {
            return back()->with('error', 'Nomor telepon penyewa tidak ditemukan.');
        }

        $payment->update(['reminded_at' => now()]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $statusText = ($payment->status === 'pending') ? 'MENUNGGU VERIFIKASI' : strtoupper($payment->status);
        $amount     = number_format($payment->amount_paid, 0, ',', '.');
        $invoiceNo  = $payment->invoice->invoice_number;

        $message = "*KONFIRMASI PEMBAYARAN: " . $invoiceNo . "*\n"
            . "Gedung Braga 8\n"
            . "--------------------------\n"
            . "*Penyewa:* " . $tenant->tenant_name . "\n"
            . "*Status:* " . $statusText . "\n"
            . "*Jumlah:* Rp " . $amount . "\n"
            . "--------------------------\n\n"
            . "Halo, ini adalah pengingat bahwa status pembayaran Anda saat ini adalah *" . $statusText . "*.\n"
            . "Mohon tunggu proses verifikasi oleh tim admin kami. Terima kasih atas kerja samanya.";

        return redirect()->away("https://wa.me/{$cleanPhone}?text=" . urlencode($message));
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'invoice_id'   => 'required|exists:invoices,id',
            'amount_paid'  => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'paid_using'   => 'required|string|max:100',
            'proof_base64' => 'required|string',
            'notes'        => 'nullable|string|max:500',
        ]);

        $base64 = $request->proof_base64;
        if (str_contains($base64, ',')) {
            [, $base64] = explode(',', $base64, 2);
        }

        $decoded = base64_decode($base64);
        if (!$decoded) {
            return response()->json(['message' => 'Gambar tidak valid'], 422);
        }

        $filename = 'payments/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($filename, $decoded);

        $invoice = Invoice::findOrFail($request->invoice_id);

        $payment = Payment::create([
            'invoice_id'   => $request->invoice_id,
            'amount_paid'  => $request->amount_paid,
            'due_date'     => $invoice->billing_period_end,
            'paid_using'   => $request->paid_using,
            'status'       => 'pending',
            'payment_date' => $request->payment_date,
            'proof_img'    => $filename,
            'notes'        => $request->notes,
            'reminded_at'  => null,
        ]);

        $tenantName = $invoice->tenant->tenant_name ?? 'Tenant';

        foreach (User::where('role', 'admin')->get() as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title'   => 'Bukti Pembayaran Masuk',
                'message' => "{$tenantName} mengirimkan bukti pembayaran (menunggu verifikasi)",
                'type'    => 'payment',
            ]);
        }

        return response()->json([
            'message' => 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi admin.',
            'data'    => [
                'payment_id' => $payment->id,
                'status'     => $payment->status,
            ],
        ], 201);
    }

    public function apiIndex(Request $request)
    {
        $payments = Payment::with(['invoice.unit'])
            ->latest()
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'invoice_id'   => $p->invoice_id,
                'amount_paid'  => $p->amount_paid,
                'paid_using'   => $p->paid_using,
                'status'       => $p->status,
                'payment_date' => $p->payment_date,
                'proof_img'    => $p->proof_img
                    ? url('api/proof/' . basename($p->proof_img))
                    : null,
                'notes'        => $p->notes,
                'invoice'      => [
                    'invoice_number' => $p->invoice->invoice_number ?? '-',
                    'unit_number'    => $p->invoice->unit?->unit_number ?? '-',
                ],
            ]);

        return response()->json(['data' => $payments]);
    }
}