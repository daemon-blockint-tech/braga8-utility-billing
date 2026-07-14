<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reported_by', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'latest');
        $sort === 'oldest' ? $query->oldest() : $query->latest();

        $complaints = $query->paginate(10)->appends($request->all());

        return view('complaints.index', compact('complaints'));
    }

    public function show(Complaint $complaint)
    {
        return view('complaints.show', compact('complaint'));
    }

    public function create()
    {
        return view('complaints.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'report_date' => 'required|date',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('complaints', 'public');
        }

        Complaint::create($validated);

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint filed successfully.');
    }

    public function edit(Complaint $complaint)
    {
        return view('complaints.edit', compact('complaint'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status'      => 'sometimes|in:pending,in_progress,resolved,rejected',
            'solution'    => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($complaint->image) {
                Storage::disk('public')->delete($complaint->image);
            }
            $validated['image'] = $request->file('image')->store('complaints', 'public');
        }

        $complaint->update($validated);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint updated successfully.');
    }

    public function action(Request $request, Complaint $complaint)
    {
        $request->validate([
            'solution' => 'required|string|min:5',
        ]);

        $complaint->update([
            'solution' => $request->solution,
            'status'   => 'resolved',
        ]);

        if ($complaint->user_id) {
            \App\Models\Notification::create([
                'user_id' => $complaint->user_id,
                'title'   => 'Komplain Ditanggapi',
                'message' => 'Komplain Anda "' . $complaint->title . '" telah mendapat solusi dari admin.',
                'type'    => 'complaint',
            ]);
        }

        return redirect()->back()->with('status', 'complaint-resolved');
    }

    public function destroy(Complaint $complaint)
    {
        if ($complaint->image) {
            Storage::disk('public')->delete($complaint->image);
        }

        $complaint->delete();

        return redirect()->back()->with('status', 'complaint-deleted');
    }
}