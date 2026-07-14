<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Tenant;
use App\Models\UtilityMeter;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MeterReadingController extends Controller
{
    private function jsonResponse($message, $data = null, $status = 200)
    {
        return response()->json(['message' => $message, 'data' => $data], $status);
    }

    public function index(Request $request)
    {
        $search = $request->query('search');

        $tenants = Tenant::with(['units.meters.readings.user'])
            ->when($search, function ($query) use ($search) {
                $query->where('tenant_name', 'LIKE', "%{$search}%")
                    ->orWhereHas('units', function ($q) use ($search) {
                        $q->where('unit_number', 'LIKE', "%{$search}%");
                    });
            })
            ->paginate(5)
            ->appends($request->all());

        $meters = UtilityMeter::with('unit')->get();

        return view('readings.index', compact('tenants', 'meters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_id'       => 'required_without:meter_id|nullable|integer',
            'meter_type'    => 'required_without:meter_id|nullable|in:electricity,water',
            'meter_id'      => 'nullable|exists:utility_meters,id',
            'reading_value' => 'required|numeric',
            'photo'         => 'nullable|image|max:5120',
            'photo_base64'  => 'nullable|string',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'description'   => 'nullable|string',
        ]);

        if (!$request->hasFile('photo') && !$request->filled('photo_base64')) {
            return $this->jsonResponse("Foto meter wajib diisi", null, 422);
        }

        $meterId = $request->meter_id;
        if (!$meterId) {
            $meter = UtilityMeter::where('unit_id', $request->unit_id)
                ->where('meter_type', $request->meter_type)
                ->first();

            if (!$meter) {
                return $this->jsonResponse("Meter tidak ditemukan untuk unit ini", null, 404);
            }
            $meterId = $meter->id;
        }

        $lastReading = MeterReading::where('meter_id', $meterId)
            ->latest('recorded_at')
            ->first();

        if ($lastReading && $request->reading_value < $lastReading->reading_value) {
            return $this->jsonResponse(
                "Input ({$request->reading_value}) lebih rendah dari sebelumnya ({$lastReading->reading_value}).",
                null,
                422
            );
        }

        $path = null;
        if ($request->filled('photo_base64')) {
            try {
                $dataUri = $request->input('photo_base64');
                $parts   = explode(';base64,', $dataUri);
                if (count($parts) == 2) {
                    $decoded   = base64_decode($parts[1]);
                    $extension = str_contains($parts[0], 'png') ? 'png' : 'jpg';
                    $filename  = 'readings/meter_' . time() . '_' . Str::random(6) . '.' . $extension;
                    Storage::disk('public')->put($filename, $decoded);
                    $path = $filename;
                }
            } catch (\Exception $e) {
                Log::error('Base64 upload error: ' . $e->getMessage());
                return $this->jsonResponse("Gagal memproses foto", null, 500);
            }
        } elseif ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('readings', 'public');
        }

        $address = $this->getAddress($request->latitude, $request->longitude);

        $reading = MeterReading::create([
            'meter_id'         => $meterId,
            'user_id'          => Auth::id() ?? 1,
            'reading_value'    => $request->reading_value,
            'photo_path'       => $path,
            'latitude'         => $request->latitude,
            'longitude'        => $request->longitude,
            'location_address' => $address,
            'description'      => $request->description,
            'recorded_at'      => Carbon::now(),
        ]);

        return $this->jsonResponse("Data berhasil disimpan", $reading, 201);
    }

    public function update(Request $request, $id)
    {
        $reading = MeterReading::findOrFail($id);

        $request->validate([
            'reading_value' => 'required|numeric',
            'description'   => 'nullable|string',
            'photo'         => 'nullable|image|max:5120',
            'photo_base64'  => 'nullable|string',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
        ]);

        if ($request->filled('photo_base64')) {
            if ($reading->photo_path) {
                Storage::disk('public')->delete($reading->photo_path);
            }
            $dataUri = $request->input('photo_base64');
            $parts   = explode(';base64,', $dataUri);
            if (count($parts) == 2) {
                $decoded   = base64_decode($parts[1]);
                $extension = str_contains($parts[0], 'png') ? 'png' : 'jpg';
                $filename  = 'readings/meter_' . time() . '_' . Str::random(6) . '.' . $extension;
                Storage::disk('public')->put($filename, $decoded);
                $reading->photo_path = $filename;
            }
        }

        $reading->reading_value = $request->reading_value;
        $reading->description   = $request->description;

        if ($request->filled('latitude') && $request->filled('longitude')) {
            $reading->latitude        = $request->latitude;
            $reading->longitude       = $request->longitude;
            $reading->location_address = $this->getAddress($request->latitude, $request->longitude);
        }

        $reading->save();

        return $this->jsonResponse("Data berhasil diupdate", $reading);
    }

    public function updateStatus($id)
    {
        $reading         = MeterReading::findOrFail($id);
        $reading->status = $reading->status === 'checked' ? null : 'checked';
        $reading->save();
        return back()->with('status', 'reading-confirmed');
    }

    public function summary()
    {
        $tenants = Tenant::with(['units.meters.readings' => function ($query) {
            $query->latest();
        }])->get();
        return response()->json($tenants);
    }

    public function getMonthlyProgress()
    {
        $totalMeters       = UtilityMeter::count();
        $readingsThisMonth = MeterReading::whereMonth('recorded_at', now()->month)
            ->whereYear('recorded_at', now()->year)
            ->distinct('meter_id')
            ->count();

        return response()->json([
            'total'      => $totalMeters,
            'readings'   => $readingsThisMonth,
            'percentage' => $totalMeters > 0 ? round($readingsThisMonth / $totalMeters, 2) : 0,
        ]);
    }

    private function getAddress($lat, $lon)
    {
        if (!$lat || !$lon || ($lat == 0 && $lon == 0)) {
            return "Koordinat tidak valid (GPS tidak terkunci)";
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Braga8-Ujikom-App-Student-Project',
                'Accept'     => 'application/json',
            ])
            ->timeout(10)
            ->get("https://nominatim.openstreetmap.org/reverse", [
                'format'         => 'jsonv2',
                'lat'            => $lat,
                'lon'            => $lon,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['display_name'] ?? "Alamat tidak ditemukan di peta";
            }

            Log::error("Nominatim Error: " . $response->status() . " - " . $response->body());
            return "Gagal melacak alamat (Server Map Error)";

        } catch (\Exception $e) {
            Log::error("Geo Error: " . $e->getMessage());
            return "Gagal melacak alamat (Koneksi Timeout)";
        }
    }
}