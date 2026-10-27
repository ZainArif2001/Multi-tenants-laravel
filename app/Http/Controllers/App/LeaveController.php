<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    /**
     * Employee sees own requests; admin sees all.
     */
    public function index(Request $request): View
    {
        $isAdmin = $request->user()->hasRole('admin');

        return view('app.leaves.index', [
            'leaves' => LeaveRequest::with('user')
                ->when(! $isAdmin, fn ($q) => $q->where('user_id', $request->user()->id))
                ->latest()->paginate(20),
            'isAdmin' => $isAdmin,
            'types' => LeaveRequest::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(LeaveRequest::TYPES)),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        LeaveRequest::create($data + ['user_id' => $request->user()->id]);

        foreach (\App\Models\User::role('admin')->pluck('id') as $adminId) {
            \App\Models\UserNotification::notify($adminId, 'employee', 'New leave request',
                "{$request->user()->name}: {$data['type']} {$data['start_date']}→{$data['end_date']}", route('tenant.leaves.index'));
        }

        return back()->with('success', 'Leave request submitted.');
    }

    /**
     * Admin approve / reject.
     */
    public function review(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $leave->update($data);
        \App\Models\UserNotification::notify($leave->user_id, 'employee',
            "Leave request {$data['status']}",
            "{$leave->type} {$leave->start_date->format('M j')}→{$leave->end_date->format('M j')}"
                . ($data['admin_note'] ? " — {$data['admin_note']}" : ''),
            route('tenant.leaves.index'));

        return back()->with('success', "Request {$data['status']}.");
    }
}
