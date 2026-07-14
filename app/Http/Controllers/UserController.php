<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role   = $request->input('role', 'admin');

        $users = User::when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                return $query->where('role', $role);
            })
            ->latest()
            ->get();

        return view('users.index', compact('users', 'role'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required',
            'username'     => 'required|unique:users',
            'email'        => 'required|email|unique:users',
            'phone_number' => 'nullable|string|max:20',
            'role'         => 'required|in:admin,supervisor,petugas,tenant',
            'password'     => 'required|min:6',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return redirect()->back()->with('status', 'user-stored');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'         => 'required',
            'username'     => "required|unique:users,username,$user->id",
            'email'        => "required|email|unique:users,email,$user->id",
            'phone_number' => 'nullable|string|max:20',
            'role'         => 'required|in:admin,supervisor,petugas,tenant',
            'password'     => 'nullable|min:6',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->back()->with('status', 'user-updated');
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'             => 'sometimes|nullable|string|max:255',
            'phone_number'     => 'sometimes|nullable|string|max:20',
            'email'            => "sometimes|nullable|email|unique:users,email,{$user->id}",
            'role'             => 'sometimes|nullable|string',
            'tenant_name'      => 'sometimes|nullable|string|max:255',
            'contact_phone'    => 'sometimes|nullable|string|max:20',
            'contact_email'    => 'sometimes|nullable|email',
            'person_in_charge' => 'sometimes|nullable|string|max:255',
        ]);

        $user->update(array_filter([
            'name'         => $validated['name'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'email'        => $validated['email'] ?? null,
        ], fn($v) => $v !== null));

        if ($user->role === 'tenant' && $user->tenant) {
            $user->tenant->update(array_filter([
                'tenant_name'      => $validated['tenant_name'] ?? null,
                'contact_phone'    => $validated['contact_phone'] ?? null,
                'contact_email'    => $validated['contact_email'] ?? null,
                'person_in_charge' => $validated['person_in_charge'] ?? null,
            ], fn($v) => $v !== null));
        }

        return response()->json([
            'message' => 'Profile updated',
            'user'    => $user->fresh()->load('tenant'),
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back()->with('status', 'user-deleted');
    }
}