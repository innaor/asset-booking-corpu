<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class BugReportController extends Controller
{
        // ==================== USER SIDE ====================

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:150',
            'category'     => 'required|in:ui,data,system_error,other',
            'related_page' => 'nullable|string|max:100',
            'description'  => 'required|string',
            'attachment'   => 'nullable|image|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('bug-reports', 'public');
        }

        BugReport::create([
            'user_id'         => auth()->id(),
            'title'           => $request->title,
            'category'        => $request->category,
            'related_page'    => $request->related_page,
            'description'     => $request->description,
            'attachment_path' => $path,
            'status'          => 'pending',
        ]);

        return redirect('/user/bug-reports')->with('success', 'Aduan bug berhasil dikirim. Silakan tunggu tindak lanjut dari admin.');
    }

    public function index()
    {
        $bugReports = BugReport::where('user_id', auth()->id())
                        ->latest()
                        ->get();

        return view('user.bug-reports.index', compact('bugReports'));
    }


    // ==================== ADMIN SIDE ====================

    public function adminIndex(Request $request)
    {
        $query = BugReport::with(['user', 'handledBy']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $bugReports = $query->latest()->get();

        $allReports = BugReport::all();
        $counts = [
            'all'         => $allReports->count(),
            'pending'     => $allReports->where('status', 'pending')->count(),
            'in_progress' => $allReports->where('status', 'in_progress')->count(),
            'resolved'    => $allReports->where('status', 'resolved')->count(),
            'rejected'    => $allReports->where('status', 'rejected')->count(),
        ];

        return view('admin.bug-reports.index', compact('bugReports', 'counts'));
    }

    public function updateStatus(Request $request, $id)
    {
        $bugReport = BugReport::findOrFail($id);

        $request->validate([
            'status'     => 'required|in:pending,in_progress,resolved,rejected',
            'admin_note' => 'required_if:status,resolved,rejected|nullable|string',
        ]);

        $bugReport->update([
            'status'      => $request->status,
            'admin_note'  => $request->admin_note,
            'handled_by'  => auth()->id(),
            'resolved_at' => in_array($request->status, ['resolved', 'rejected']) ? now() : null,
        ]);

        ActivityLog::create([
            'admin_id'       => auth()->id(),
            'target_user_id' => $bugReport->user_id,
            'action'         => 'handle_bug_report',
            'description'    => 'Mengubah status aduan bug "' . $bugReport->title . '" menjadi ' . $request->status,
        ]);

        return back()->with('success', 'Status aduan bug berhasil diperbarui.');
    }
}