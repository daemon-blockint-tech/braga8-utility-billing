<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $query = Reminder::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('role_target', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $reminders = $query->latest()->paginate(10); 
        return view('reminders.index', compact('reminders'));
    }

    public function create(): View
    {
        return view('reminders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'reminder_date' => 'required|date',
            'due_date'      => 'required|date|after_or_equal:reminder_date',
            'role_target'   => 'required|in:supervisor,admin,tenant,petugas',
        ]);

        $reminderDate = Carbon::parse($validated['reminder_date']);
        $calculatedStatus = $reminderDate->isFuture() ? 'pending' : 'sent';

        if ($request->has('auto_escalate')) {
            $baseDate = Carbon::parse($validated['reminder_date']);
            
            $escalations = [
                ['title' => ' (Teguran 1)', 'days' => 7, 'msg' => 'Teguran 1: Pembayaran melewati batas.'],
                ['title' => ' (Teguran 2)', 'days' => 14, 'msg' => 'Teguran 2: Segera lunasi tagihan Anda.'],
                ['title' => ' (Peringatan Terakhir)', 'days' => 21, 'msg' => 'Peringatan Terakhir: Utilitas akan diputus besok.'],
            ];

            foreach ($escalations as $index => $step) {
                $remindAt = $baseDate->copy()->addDays($step['days']);
                
                $escalationStatus = $remindAt->isFuture() ? 'pending' : 'sent';

                $reminder = Reminder::create([
                    'title'         => $validated['title'] . $step['title'],
                    'reminder_date' => $remindAt,
                    'due_date'      => $remindAt->copy()->addDay(),
                    'role_target'   => 'tenant',
                    'status'        => $escalationStatus
                ]);

                if ($escalationStatus === 'sent') {
                    $this->sendImpulsiveNotification($reminder, $step['msg'], $index);
                }
            }

            return redirect()->route('reminders.index')->with('status', 'reminder-stored');

        } else {
            $reminder = Reminder::create([
                ...$validated,
                'status' => $calculatedStatus
            ]);

            if ($calculatedStatus === 'sent') {
                $users = User::where('role', $reminder->role_target)->get();
                foreach ($users as $user) {
                    Notification::create([
                        'user_id' => $user->id,
                        'title'   => $reminder->title,
                        'message' => 'Pemberitahuan: ' . $reminder->title,
                        'type'    => 'reminder',
                    ]);
                }
            }

            return redirect()->route('reminders.index')->with('status', 'reminder-stored');
        }
    }

    private function sendImpulsiveNotification($reminder, $message, $index)
    {
        $targetRoles = ['tenant'];
        
        if ($index === 2) { 
            $targetRoles[] = 'petugas'; 
        }

        $users = User::whereIn('role', $targetRoles)->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title'   => $reminder->title,
                'message' => $message,
                'type'    => 'reminder',
            ]);
        }
    }

    public function edit(Reminder $reminder): View
    {
        return view('reminders.edit', compact('reminder'));
    }

    public function update(Request $request, Reminder $reminder): RedirectResponse
    {
        $validated = $request->validate([
            'title'         => 'sometimes|string|max:255',
            'reminder_date' => 'sometimes|date',
            'due_date'      => 'sometimes|date|after_or_equal:reminder_date',
            'role_target'   => 'sometimes|in:supervisor,admin,tenant,petugas',
        ]);

        if (isset($validated['reminder_date'])) {
            $reminderDate = Carbon::parse($validated['reminder_date']);
            $validated['status'] = $reminderDate->isFuture() ? 'pending' : 'sent';
        }

        $reminder->update($validated);

        return redirect()->route('reminders.index')->with('status', 'reminder-updated');
    }

    public function destroy(Reminder $reminder): RedirectResponse
    {
        $reminder->delete();

        return redirect()->route('reminders.index')->with('status', 'reminder-deleted');
    }
}