<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::query();

        if ($request->filled('search')) {
            $query->where('tenant_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('person_in_charge', 'LIKE', "%{$request->search}%");
        }

        $query->with(['units.meters.readings' => function($q) {
            $q->orderBy('recorded_at', 'desc');
        }]);

        $tenants = $query->latest()->paginate(10)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($tenants);
        }

        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_name'      => 'required|string|max:255',
            'person_in_charge' => 'required|string|max:255',
            'business_type'    => 'required|string|max:255',
            'contact_phone'    => 'required|string|max:20',
            'contact_email'    => 'required|email|max:255|unique:users,email',
            'company_name'     => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'         => $validated['person_in_charge'],
                'email'        => $validated['contact_email'],
                'username'     => Str::slug($validated['person_in_charge']) . rand(10, 99),
                'password'     => Hash::make('password123'),
                'phone_number' => $validated['contact_phone'],
            ]);
            $user->role = 'tenant';
            $user->save();

            Tenant::create([
                'user_id'          => $user->id,
                'tenant_name'      => $validated['tenant_name'],
                'company_name'     => $validated['company_name'],
                'business_type'    => $validated['business_type'],
                'person_in_charge' => $validated['person_in_charge'],
                'contact_phone'    => $validated['contact_phone'],
                'contact_email'    => $validated['contact_email'],
            ]);
        });

        return redirect()->route('tenants.index')->with('status', 'tenant-created');
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'tenant_name'      => 'required|string|max:255',
            'person_in_charge' => 'required|string|max:255',
            'business_type'    => 'required|string|max:255',
            'contact_phone'    => 'required|string|max:20',
            'contact_email'    => 'required|email|max:255|unique:users,email,' . $tenant->user_id,
            'company_name'     => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $tenant) {

            $tenant->update($validated);

            if ($tenant->user_id) {
                User::where('id', $tenant->user_id)->update([
                    'name'         => $validated['person_in_charge'],
                    'email'        => $validated['contact_email'],
                    'phone_number' => $validated['contact_phone'],
                ]);
            }

            return redirect()->route('tenants.index')->with('status', 'tenant-updated');
        });
    }

    public function show(Tenant $tenant)
    {
        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    public function destroy(Tenant $tenant)
    {
        return DB::transaction(function () use ($tenant) {

            if ($tenant->user_id) {
                User::where('id', $tenant->user_id)->delete();
            }

            $tenant->delete();

            return redirect()->route('tenants.index')->with('status', 'tenant-deleted');
        });
    }
}
