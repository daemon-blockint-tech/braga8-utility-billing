<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function markAsRead(Request $request, $notification)
    {
        $notif = Notification::where('user_id', auth()->id())->findOrFail($notification);
        $notif->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function destroy(Request $request, $notification)
    {
        $notif = Notification::where('user_id', auth()->id())->findOrFail($notification);
        $notif->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function destroyAll()
    {
        auth()->user()->customNotifications()->delete();
        return back();
    }
}