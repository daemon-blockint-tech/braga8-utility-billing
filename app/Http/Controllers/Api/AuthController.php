<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->header('X-Platform') === 'mobile' && $user->role === 'admin') {
            return response()->json([
                'message' => 'Admins are not authorized to use the mobile application.'
            ], 403);
        }

        $token = $user->createToken('braga8_auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUserResponse($user)
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'phone_number'     => 'nullable|string',
            'tenant_name'      => 'required_if:role,tenant|string|max:255',
            'person_in_charge' => 'required_if:role,tenant|string|max:255',
            'contact_phone'    => 'required_if:role,tenant|string',
        ]);

        return DB::transaction(function () use ($request, $user) {
            try {
                $user->update([
                    'name'         => $request->name,
                    'phone_number' => $request->phone_number,
                ]);

                if ($user->role === 'tenant' && $user->tenant) {
                    $user->tenant()->update([
                        'tenant_name'      => $request->tenant_name,
                        'person_in_charge' => $request->person_in_charge,
                        'contact_phone'    => $request->contact_phone,
                    ]);
                }

                return response()->json([
                    'message' => 'Profile updated successfully',
                    'user'    => $this->formatUserResponse($user->fresh())
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to update profile',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    }

    private function formatUserResponse($user)
    {
        if ($user->role === 'tenant') {
            $user->load('tenant');
        }

        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'role'           => $user->role,
            'phone_number'   => $user->phone_number,
            'username'       => $user->username ?? $user->name,
            'tenant_details' => $user->tenant ?? null,
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}