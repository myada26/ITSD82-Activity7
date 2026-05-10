@extends('layouts.app')
@section('title', $event->name)
@section('page-title', $event->name)

@section('content')
<div>
    <div style="margin-bottom:16px">
        <a href="{{ route('org.events.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#4a6356;text-decoration:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Events
        </a>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#15803d;font-weight:600">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
    <div style="background:#fef9ec;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#92400e;font-weight:600">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#dc2626;font-weight:600">{{ session('error') }}</div>
    @endif

    @php
    $statusMap = [
        'DRAFT'               => ['bg'=>'#f3f4f6','color'=>'#374151','label'=>'Draft'],
        'PENDING_APPROVAL'    => ['bg'=>'#fef9ec','color'=>'#92400e','label'=>'Pending Auditor Review'],
        'PENDING_CHAIRPERSON' => ['bg'=>'#eff6ff','color'=>'#1d4ed8','label'=>'Pending Chairperson Confirmation'],
        'APPROVED'            => ['bg'=>'#dcfce7','color'=>'#15803d','label'=>'Approved'],
        'REJECTED'            => ['bg'=>'#fee2e2','color'=>'#dc2626','label'=>'Rejected'],
    ];
    $st = $statusMap[$event->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151','label'=>$event->status];
    @endphp

    {{-- Header card --}}
    <div style="background:white;border-radius:12px;border:1px solid #dde8e1;padding:20px 24px;margin-bottom:14px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div style="font-size:20px;font-weight:700;color:#0f1f17">{{ $event->name }}</div>
                <div style="font-size:13px;color:#4a6356;margin-top:3px">
                    {{ $event->date->format('F d, Y') }}
                    @if($event->venue) · {{ $event->venue }} @endif
                    · {{ $event->time_type === 'FULL_DAY' ? 'Full Day (4 slots)' : 'Half Day (2 slots)' }}
                    · {{ $event->academicYear?->name }}
                </div>
            </div>
            <span style="display:inline-block;padding:5px 14px;border-radius:20px;font-size:12.5px;font-weight:700;background:{{ $st['bg'] }};color:{{ $st['color'] }}">{{ $st['label'] }}</span>
        </div>

        {{-- Status progress steps --}}
        <div style="display:flex;align-items:center;margin-top:20px">
            @php
            $steps = ['DRAFT' => 'Draft', 'PENDING_APPROVAL' => 'Submitted', 'APPROVED' => 'Approved'];
            $order = ['DRAFT','PENDING_APPROVAL','PENDING_CHAIRPERSON','APPROVED','REJECTED'];
            $currentIdx = array_search($event->status, $order);
            @endphp
            @foreach(['Draft','Submitted','Auditor OK','Approved'] as $i => $label)
            @if($i > 0)<div style="flex:1;height:2px;background:{{ $i <= ($currentIdx >= 3 ? 3 : $currentIdx) ? '#1a7a41' : '#dde8e1' }}"></div>@endif
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                <div style="width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;{{ $i < $currentIdx ? 'background:#1a7a41;color:white' : ($i === $currentIdx ? 'background:#0f1f17;color:white' : 'background:#dde8e1;color:#8aa89a') }}">{{ $i+1 }}</div>
                <div style="font-size:10px;color:#8aa89a;white-space:nowrap">{{ $label }}</div>
            </div>
            @endforeach
        </div>

        @if($event->rejection_reason)
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;margin-top:14px;font-size:13px;color:#dc2626">
            <strong>Rejection reason:</strong> {{ $event->rejection_reason }}
        </div>
        @endif
    </div>

    {{-- Attendance summary --}}
    @if($totalStudents > 0 && $event->status !== 'DRAFT')
    <div style="background:white;border-radius:12px;border:1px solid #dde8e1;padding:16px 20px;margin-bottom:14px">
        <div style="font-size:13px;font-weight:700;color:#0f1f17;margin-bottom:10px">Attendance Summary</div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            @foreach($event->slots() as $slot)
            @php
            $present = $presentCountBySlot[$slot] ?? 0;
            $slotLabel = ['MORNING_IN'=>'AM In','MORNING_OUT'=>'AM Out','AFTERNOON_IN'=>'PM In','AFTERNOON_OUT'=>'PM Out'][$slot] ?? $slot;
            @endphp
            <div style="background:#f8fbf9;border:1px solid #dde8e1;border-radius:8px;padding:10px 14px;text-align:center;min-width:80px">
                <div style="font-size:18px;font-weight:700;color:#1a7a41">{{ $present }}</div>
                <div style="font-size:10px;color:#8aa89a;text-transform:uppercase;font-weight:600">{{ $slotLabel }}</div>
                <div style="font-size:11px;color:#4a6356;margin-top:2px">/ {{ $totalStudents }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Action buttons --}}
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">

        {{-- Secretary actions --}}
        @if(auth()->user()->hasRole('SECRETARY'))
        <a href="{{ route('org.attendance.sheet', $event) }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;background:#1a7a41;color:white">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 12l2 2 4-4"/></svg>
            {{ $event->status === 'DRAFT' ? 'Take Attendance' : 'View Sheet' }}
        </a>
        @endif

        {{-- Auditor actions --}}
        @if(auth()->user()->hasRole('AUDITOR') && $event->status === 'PENDING_APPROVAL')
        <a href="{{ route('org.attendance.sheet', $event) }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid #1d4ed8;color:#1d4ed8">
            Review Sheet
        </a>
        <form method="POST" action="{{ route('org.attendance.auditor-approve', $event) }}">
            @csrf @method('PATCH')
            <button type="submit" onclick="return confirm('Approve attendance and compute fines? This cannot be undone.')"
                style="padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:#1a7a41;color:white">
                Approve (No Edits)
            </button>
        </form>
        <form method="POST" action="{{ route('org.attendance.auditor-forward', $event) }}">
            @csrf @method('PATCH')
            <button type="submit" onclick="return confirm('Forward to Chairperson for final confirmation?')"
                style="padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid #1d4ed8;color:#1d4ed8;background:white;cursor:pointer">
                Forward to Chairperson
            </button>
        </form>
        <button onclick="document.getElementById('reject-form-auditor').style.display='block'"
            style="padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid #dc2626;color:#dc2626;background:white;cursor:pointer">
            Reject
        </button>
        @endif

        {{-- Chairperson actions --}}
        @if(auth()->user()->hasRole('CHAIRPERSON') && $event->status === 'PENDING_CHAIRPERSON')
        <a href="{{ route('org.attendance.diff', $event) }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid #7c3aed;color:#7c3aed">
            View Diff (Auditor Edits)
        </a>
        <form method="POST" action="{{ route('org.attendance.chairperson-confirm', $event) }}">
            @csrf @method('PATCH')
            <button type="submit" onclick="return confirm('Confirm edits and compute fines? This cannot be undone.')"
                style="padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:#1a7a41;color:white">
                Confirm &amp; Compute Fines
            </button>
        </form>
        <button onclick="document.getElementById('reject-form-chairperson').style.display='block'"
            style="padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid #dc2626;color:#dc2626;background:white;cursor:pointer">
            Reject Edits
        </button>
        @endif

        {{-- Any role: view sheet (read-only) --}}
        @if(!auth()->user()->hasRole('SECRETARY') && in_array($event->status, ['PENDING_APPROVAL','PENDING_CHAIRPERSON','APPROVED']))
        <a href="{{ route('org.attendance.sheet', $event) }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid #dde8e1;color:#4a6356">
            View Attendance Sheet
        </a>
        @endif

        {{-- Chairperson re-sync (DRAFT only) --}}
        @if(auth()->user()->hasRole('CHAIRPERSON') && $event->status === 'DRAFT')
        <a href="{{ route('org.attendance.sheet', $event) }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid #dde8e1;color:#4a6356">
            View Sheet
        </a>
        <form method="POST" action="{{ route('org.events.resync', $event) }}">
            @csrf
            <button type="submit" onclick="return confirm('Re-sync enrolled students into the attendance sheet?')"
                style="padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid #d97706;color:#d97706;background:white;cursor:pointer">
                Re-sync Students
            </button>
        </form>
        @endif
    </div>

    {{-- Auditor reject modal --}}
    <div id="reject-form-auditor" style="display:none;background:white;border:1px solid #fca5a5;border-radius:12px;padding:18px 22px;margin-bottom:14px;max-width:520px">
        <div style="font-size:14px;font-weight:700;margin-bottom:10px">Reject Attendance — Provide Reason</div>
        <form method="POST" action="{{ route('org.attendance.auditor-reject', $event) }}">
            @csrf @method('PATCH')
            <textarea name="rejection_reason" rows="3" required placeholder="Explain what needs to be corrected..."
                style="width:100%;padding:8px 12px;border:1px solid #dde8e1;border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box"></textarea>
            <div style="display:flex;gap:8px;margin-top:10px">
                <button type="submit" style="padding:7px 14px;border-radius:7px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:#dc2626;color:white">Confirm Rejection</button>
                <button type="button" onclick="document.getElementById('reject-form-auditor').style.display='none'" style="padding:7px 14px;border-radius:7px;font-size:13px;border:1.5px solid #dde8e1;color:#4a6356;background:white;cursor:pointer">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Chairperson reject modal --}}
    <div id="reject-form-chairperson" style="display:none;background:white;border:1px solid #fca5a5;border-radius:12px;padding:18px 22px;margin-bottom:14px;max-width:520px">
        <div style="font-size:14px;font-weight:700;margin-bottom:10px">Reject Auditor Edits — Provide Reason</div>
        <form method="POST" action="{{ route('org.attendance.chairperson-reject', $event) }}">
            @csrf @method('PATCH')
            <textarea name="rejection_reason" rows="3" required placeholder="Explain why the auditor edits are rejected..."
                style="width:100%;padding:8px 12px;border:1px solid #dde8e1;border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box"></textarea>
            <div style="display:flex;gap:8px;margin-top:10px">
                <button type="submit" style="padding:7px 14px;border-radius:7px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:#dc2626;color:white">Confirm Rejection</button>
                <button type="button" onclick="document.getElementById('reject-form-chairperson').style.display='none'" style="padding:7px 14px;border-radius:7px;font-size:13px;border:1.5px solid #dde8e1;color:#4a6356;background:white;cursor:pointer">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Workflow timeline --}}
    <div class="card">
        <div style="padding:13px 20px;border-bottom:1px solid #eaf0ec">
            <div style="font-size:13px;font-weight:700">Workflow Timeline</div>
        </div>
        <div style="padding:16px 20px">
            <div style="display:flex;flex-direction:column;gap:10px">
                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div style="width:8px;height:8px;border-radius:50%;background:#1a7a41;margin-top:5px;flex-shrink:0"></div>
                    <div>
                        <div style="font-size:12.5px;font-weight:600">Event Created</div>
                        <div style="font-size:11.5px;color:#8aa89a">by {{ $event->createdBy?->username }} · {{ $event->created_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @if($event->submitted_at)
                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div style="width:8px;height:8px;border-radius:50%;background:#d97706;margin-top:5px;flex-shrink:0"></div>
                    <div>
                        <div style="font-size:12.5px;font-weight:600">Submitted for Review</div>
                        <div style="font-size:11.5px;color:#8aa89a">by {{ $event->submittedBy?->username }} · {{ $event->submitted_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @endif
                @if($event->auditor_reviewed_at)
                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div style="width:8px;height:8px;border-radius:50%;background:#1d4ed8;margin-top:5px;flex-shrink:0"></div>
                    <div>
                        <div style="font-size:12.5px;font-weight:600">Auditor Reviewed</div>
                        <div style="font-size:11.5px;color:#8aa89a">by {{ $event->auditorReviewer?->username }} · {{ $event->auditor_reviewed_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @endif
                @if($event->approved_at)
                <div style="display:flex;gap:12px;align-items:flex-start">
                    <div style="width:8px;height:8px;border-radius:50%;background:#15803d;margin-top:5px;flex-shrink:0"></div>
                    <div>
                        <div style="font-size:12.5px;font-weight:600">Approved &amp; Fines Computed</div>
                        <div style="font-size:11.5px;color:#8aa89a">by {{ $event->approvedBy?->username }} · {{ $event->approved_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
