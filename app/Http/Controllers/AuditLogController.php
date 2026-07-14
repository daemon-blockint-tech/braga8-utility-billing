<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $latestIds = AuditLog::latest()->limit(50)->pluck('id');
        AuditLog::whereNotIn('id', $latestIds)->update(['is_archived' => true]);

        $query = AuditLog::with('user')->where('is_archived', false);

    if ($request->filled('search')) {
        $search = strtolower($request->search);

        $actionMap = [
            'tambah'  => 'created',
            'menambah'  => 'created',
            'buat'    => 'created',
            'ubah'    => 'updated',
            'edit'    => 'updated',
            'perbarui' => 'updated',
            'hapus'   => 'deleted',
        ];

        $query->where(function ($q) use ($search, $actionMap) {
            foreach ($actionMap as $key => $value) {
                if (str_contains($search, $key)) {
                    $q->orWhere('action', $value);
                }
            }

            $q->orWhere('action', 'like', "%{$search}%")
            ->orWhere('table_name', 'like', "%{$search}%")
            ->orWhere('record_id', 'like', "%{$search}%")
            ->orWhereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%");
            });
        });
    }

        if ($request->filled('category')) {
        $query->where('table_name', $request->category);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->paginate(10)->withQueryString();

        $categories = AuditLog::where('is_archived', false)
            ->select('table_name')
            ->distinct()
            ->pluck('table_name');

        return view('audit_logs.index', compact('logs', 'categories'));
    }

    public function apiIndex()
    {
        $logs = AuditLog::with('user')
            ->where('user_id', FacadesAuth::id())
            ->where('is_archived', false)
            ->latest()
            ->paginate(10);

        return response()->json($logs);
    }
}