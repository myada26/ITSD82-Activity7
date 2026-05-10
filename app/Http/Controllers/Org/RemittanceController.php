<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class RemittanceController extends Controller
{
    public function index()
    {
        $orgId = auth()->user()->organization_id;
        $remittances = \App\Models\Remittance::where('organization_id', $orgId)
            ->with('academicYear')
            ->orderByDesc('created_at')
            ->paginate(15);

        // Summary stats for the view
        $pendingCount   = \App\Models\Remittance::where('organization_id', $orgId)->where('status', 'PENDING')->count();
        $pendingAmount  = \App\Models\Transaction::whereHas('remittance', fn($q) => $q->where('organization_id', $orgId)->where('status', 'PENDING'))->sum('amount_paid');
        
        $verifiedCount  = \App\Models\Remittance::where('organization_id', $orgId)->where('status', 'VERIFIED')->count();
        $verifiedAmount = \App\Models\Transaction::whereHas('remittance', fn($q) => $q->where('organization_id', $orgId)->where('status', 'VERIFIED'))->sum('amount_paid');

        $acceptedCount  = \App\Models\Remittance::where('organization_id', $orgId)->where('status', 'ACCEPTED')->count();
        $acceptedAmount = \App\Models\Transaction::whereHas('remittance', fn($q) => $q->where('organization_id', $orgId)->where('status', 'ACCEPTED'))->sum('amount_paid');

        return view('org.remittances.index', compact(
            'remittances', 'pendingCount', 'pendingAmount', 
            'verifiedCount', 'verifiedAmount', 'acceptedCount', 'acceptedAmount'
        ));
    }

    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;
        $activeSemester = \App\Models\AcademicYear::where('is_active', true)->first();

        if (!$activeSemester) {
            return back()->with('error', 'No active academic year found.');
        }

        // Find unremitted transactions for this org
        $transactions = \App\Models\Transaction::where('organization_id', $orgId)
            ->whereNull('remittance_id')
            ->where('is_void', false)
            ->get();

        if ($transactions->isEmpty()) {
            return back()->with('error', 'No unremitted transactions found.');
        }

        $remittance = \App\Models\Remittance::create([
            'control_number'    => sprintf('REM-%s-%05d', now()->format('Y'), \App\Models\Remittance::where('organization_id', $orgId)->count() + 1),
            'organization_id'  => $orgId,
            'academic_year_id' => $activeSemester->id,
            'total_amount'      => $transactions->sum('amount_paid'),
            'created_by_user_id' => auth()->user()->id,
            'status'           => 'PENDING',
        ]);

        foreach ($transactions as $tx) {
            $tx->update(['remittance_id' => $remittance->id]);
        }

        AuditLog::create([
            'user_id'     => auth()->user()->id,
            'action'      => 'REMITTANCE_CREATED',
            'entity_type' => 'REMITTANCE',
            'entity_id'   => $remittance->id,
            'details'     => [
                'control_number'  => $remittance->control_number,
                'total_amount'    => $remittance->total_amount,
                'tx_count'        => $transactions->count(),
            ],
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        return redirect()->route('org.remittances.index')->with('success', 'Remittance batch created.');
    }

    public function show(\App\Models\Remittance $remittance)
    {
        if ($remittance->organization_id !== auth()->user()->organization_id) {
            abort(403);
        }

        $remittance->load(['academicYear', 'transactions.student']);
        return view('org.remittances.show', compact('remittance'));
    }

    public function verify(Request $request, \App\Models\Remittance $remittance)
    {
        if ($remittance->organization_id !== auth()->user()->organization_id) {
            abort(403);
        }

        if ($remittance->status !== 'PENDING') {
            return back()->with('error', 'Only pending remittances can be verified.');
        }

        $remittance->update([
            'status' => 'VERIFIED',
            'verified_by_user_id' => auth()->user()->id,
            'verified_at' => now(),
        ]);

        AuditLog::create([
            'user_id'     => auth()->user()->id,
            'action'      => 'REMITTANCE_VERIFIED',
            'entity_type' => 'REMITTANCE',
            'entity_id'   => $remittance->id,
            'details'     => ['control_number' => $remittance->control_number],
            'ip_address'  => $request->ip(),
            'timestamp'   => now(),
        ]);

        return back()->with('success', 'Remittance batch verified.');
    }

    public function accept(Request $request, \App\Models\Remittance $remittance)
    {
        if ($remittance->organization_id !== auth()->user()->organization_id) {
            abort(403);
        }

        if ($remittance->status !== 'VERIFIED') {
            return back()->with('error', 'Only verified remittances can be accepted.');
        }

        $remittance->update([
            'status' => 'ACCEPTED',
            'accepted_by_user_id' => auth()->user()->id,
            'accepted_at' => now(),
        ]);

        AuditLog::create([
            'user_id'     => auth()->user()->id,
            'action'      => 'REMITTANCE_ACCEPTED',
            'entity_type' => 'REMITTANCE',
            'entity_id'   => $remittance->id,
            'details'     => ['control_number' => $remittance->control_number],
            'ip_address'  => $request->ip(),
            'timestamp'   => now(),
        ]);

        return back()->with('success', 'Remittance batch accepted.');
    }
}
