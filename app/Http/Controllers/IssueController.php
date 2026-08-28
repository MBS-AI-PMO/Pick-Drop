<?php

namespace App\Http\Controllers;

use App\Models\IssueReport;
use App\Support\AppPagination;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $query = IssueReport::with(['user', 'pickupRequest.parent', 'pickupRequest.driver'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $issues = $query->paginate(AppPagination::PER_PAGE)->withQueryString();
        $counts = [
            'all' => IssueReport::count(),
            'open' => IssueReport::where('status', 'open')->count(),
            'in_progress' => IssueReport::where('status', 'in_progress')->count(),
            'resolved' => IssueReport::whereIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('pickdrop.issues.index', compact('issues', 'counts'));
    }

    public function show(IssueReport $issueReport)
    {
        $issueReport->load(['user', 'pickupRequest.parent', 'pickupRequest.driver', 'pickupRequest.student', 'resolver']);

        return view('pickdrop.issues.show', ['issue' => $issueReport]);
    }

    public function updateStatus(Request $request, IssueReport $issueReport)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $issueReport->status = $validated['status'];
        $issueReport->admin_notes = $validated['admin_notes'] ?? $issueReport->admin_notes;

        if (in_array($validated['status'], ['resolved', 'closed'], true)) {
            $issueReport->resolved_by = $request->user()->id;
            $issueReport->resolved_at = now();
        } else {
            $issueReport->resolved_by = null;
            $issueReport->resolved_at = null;
        }

        $issueReport->save();

        if ($issueReport->user_id) {
            app(\App\Services\AppNotificationService::class)->notify(
                (int) $issueReport->user_id,
                'issue_updated',
                'Issue update',
                sprintf('Your issue "%s" is now %s.', $issueReport->subject, $issueReport->statusLabel()),
                ['issue_id' => $issueReport->id]
            );
        }

        return redirect()
            ->route('issues.show', $issueReport)
            ->with('success', 'Issue updated.');
    }
}
