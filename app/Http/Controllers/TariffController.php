<?php

namespace App\Http\Controllers;

use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $tariffs = Tariff::query()
            ->when($search, function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('tariffs.index', compact('tariffs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'water_price'    => 'required|numeric',
            'electric_price' => 'required|numeric',
            'tax_percent'    => 'required|numeric',
            'other_fees'     => 'nullable|array',
        ]);

        $otherFees = $request->input('other_fees', []);
        
        $validated['electric_load_cost']      = $otherFees['electric_load'] ?? 0;
        $validated['transformer_maintenance'] = $otherFees['maintenance'] ?? 0;
        $validated['admin_fee']               = $otherFees['admin_fee'] ?? 0;
        $validated['stamp_fee']               = $otherFees['stamp_fee'] ?? 0;

        unset($otherFees['electric_load'], $otherFees['maintenance'], $otherFees['admin_fee'], $otherFees['stamp_fee']);
        $validated['other_fees'] = $otherFees;

        $saved = Tariff::create($validated);
        
        if ($saved) {
            return redirect()->route('tariffs.index')->with('status', 'tariff-stored');
        }
        return back()->with('error', 'Something went wrong.');
    }

    public function create()
    {
        return view('tariffs.create');
    }

    public function edit(Tariff $tariff)
    {
        return view('tariffs.edit', compact('tariff'));
    }

    public function update(Request $request, Tariff $tariff)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'water_price'    => 'required|numeric',
            'electric_price' => 'required|numeric',
            'tax_percent'    => 'nullable|numeric',
            'other_fees'     => 'nullable|array',
        ]);

        $otherFees = $request->input('other_fees', []);
        
        $validated['electric_load_cost']      = $otherFees['electric_load'] ?? 0;
        $validated['transformer_maintenance'] = $otherFees['maintenance'] ?? 0;
        $validated['admin_fee']               = $otherFees['admin_fee'] ?? 0;
        $validated['stamp_fee']               = $otherFees['stamp_fee'] ?? 0;

        unset($otherFees['electric_load'], $otherFees['maintenance'], $otherFees['admin_fee'], $otherFees['stamp_fee']);
        $validated['other_fees'] = $otherFees;

        $tariff->update($validated);
        
        return redirect()->route('tariffs.index')->with('status', 'tariff-updated');
    }

    public function destroy(Tariff $tariff) {
        $tariff->delete();
        return back()->with('status', 'tariff-deleted');
    }

    public function show(Tariff $tariff)
    {
        return view('tariffs.show', compact('tariff'));
    }
}