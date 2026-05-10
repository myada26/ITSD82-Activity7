@extends('layouts.app')
@section('title', 'Attendance Diff – ' . $event->name)
@section('page-title', 'Attendance Diff')

@section('content')
<div>
    <div style="margin-bottom:16px">
        <a href="{{ route('org.events.show', $event) }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#4a6356;text-decoration:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Event
        </a>
    </div>

    <div style="background:white;border:1px solid #dde8e1;border-radius:10px;padding:14px 18px;margin-bottom:14px">
        <div style="font-size:15px;font-weight:700;color:#0f1f17;margin-bottom:4px">Auditor Edits Review: {{ $event->name }}</div>
        <div style="font-size:12.5px;color:#4a6356">Compare the Secretary's original submission (left) versus the Auditor's corrected version (right). Confirm or reject below.</div>
        <div style="display:flex;gap:16px;margin-top:10px;font-size:12px">
            <div style="display:flex;align-items:center;gap:6px"><div style="width:14px;height:14px;border-radius:3px;background:#dcfce7;border:1px solid #bbf7d0"></div> Auditor marked Present (was Absent)</div>
            <div style="display:flex;align-items:center;gap:6px"><div style="width:14px;height:14px;border-radius:3px;background:#fee2e2;border:1px solid #fca5a5"></div> Auditor marked Absent (was Present)</div>
        </div>
    </div>

    <div class="card" style="overflow-x:auto;margin-bottom:16px">
        <table style="width:100%;border-collapse:collapse;min-width:500px">
            <thead>
                <tr style="background:#f0f3f1;border-bottom:1px solid #dde8e1">
                    <th style="padding:9px 13px;text-align:left;font-size:11px;font-weight:700;color:#8aa89a;text-transform:uppercase;letter-spacing:.05em">Student</th>
                    @foreach($slots as $slot)
                    @php $slotLabel = ['MORNING_IN'=>'AM In','MORNING_OUT'=>'AM Out','AFTERNOON_IN'=>'PM In','AFTERNOON_OUT'=>'PM Out'][$slot] ?? $slot; @endphp
                    <th style="padding:9px 13px;text-align:center;font-size:11px;font-weight:700;color:#8aa89a;text-transform:uppercase;letter-spacing:.05em">{{ $slotLabel }}</th>
                    @endforeach
                    <th style="padding:9px 13px;text-align:center;font-size:11px;font-weight:700;color:#8aa89a;text-transform:uppercase;letter-spacing:.05em">Changed?</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                @php
                $rowChanged = false;
                foreach ($slots as $slot) {
                    $orig = (bool) ($secretarySnapshot[$student->id][$slot] ?? false);
                    $curr = (bool) ($currentAttendance[$student->id][$slot] ?? false);
                    if ($orig !== $curr) { $rowChanged = true; break; }
                }
                @endphp
                <tr style="border-bottom:1px solid #eaf0ec{{ $rowChanged ? ';background:#fffbeb' : '' }}">
                    <td style="padding:9px 13px">
                        <div style="font-size:13px;font-weight:600;color:#0f1f17">{{ $student->full_name }}</div>
                        <div style="font-size:11px;color:#8aa89a">{{ $student->student_number }}</div>
                    </td>
                    @foreach($slots as $slot)
                    @php
                    $orig    = (bool) ($secretarySnapshot[$student->id][$slot] ?? false);
                    $curr    = (bool) ($currentAttendance[$student->id][$slot] ?? false);
                    $changed = $orig !== $curr;
                    if ($changed && $curr) {
                        $cellBg    = '#dcfce7'; $cellBorder = '#bbf7d0'; $cellColor = '#15803d';
                    } elseif ($changed && !$curr) {
                        $cellBg    = '#fee2e2'; $cellBorder = '#fca5a5'; $cellColor = '#dc2626';
                    } else {
                        $cellBg    = 'transparent'; $cellBorder = 'transparent'; $cellColor = '#374151';
                    }
                    @endphp
                    <td style="padding:9px 13px;text-align:center">
                        <div style="display:inline-block;padding:3px 10px;border-radius:8px;font-size:12px;font-weight:600;background:{{ $cellBg }};border:1px solid {{ $cellBorder }};color:{{ $cellColor }}">
                            {{ $curr ? 'Present' : 'Absent' }}
                            @if($changed)
                            <div style="font-size:10px;opacity:.75">(was {{ $orig ? 'Present' : 'Absent' }})</div>
                            @endif
                        </div>
                    </td>
                    @endforeach
                    <td style="padding:9px 13px;text-align:center">
                        @if($rowChanged)
                        <span style="font-size:11px;font-weight:700;color:#d97706;background:#fef9ec;padding:2px 8px;border-radius:8px">Changed</span>
                        @else
                        <span style="font-size:11px;color:#8aa89a">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($slots) + 2 }}" style="padding:40px;text-align:center;color:#8aa89a;font-size:13px">No students found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Action buttons --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        <form method="POST" action="{{ route('org.attendance.chairperson-confirm', $event) }}">
            @csrf @method('PATCH')
            <button type="submit" onclick="return confirm('Confirm auditor edits and compute fines? This cannot be undone.')"
                style="padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;border:none;cursor:pointer;background:#1a7a41;color:white">
                Confirm Edits &amp; Compute Fines
            </button>
        </form>
        <button onclick="document.getElementById('reject-cp-form').style.display='block'"
            style="padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid #dc2626;color:#dc2626;background:white;cursor:pointer">
            Reject Edits
        </button>
    </div>

    <div id="reject-cp-form" style="display:none;background:white;border:1px solid #fca5a5;border-radius:12px;padding:18px 22px;margin-top:14px;max-width:520px">
        <div style="font-size:14px;font-weight:700;margin-bottom:10px">Reject Auditor Edits</div>
        <form method="POST" action="{{ route('org.attendance.chairperson-reject', $event) }}">
            @csrf @method('PATCH')
            <textarea name="rejection_reason" rows="3" required placeholder="Explain why the edits should be revisited..."
                style="width:100%;padding:8px 12px;border:1px solid #dde8e1;border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box"></textarea>
            <div style="display:flex;gap:8px;margin-top:10px">
                <button type="submit" style="padding:7px 14px;border-radius:7px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:#dc2626;color:white">Confirm Rejection</button>
                <button type="button" onclick="document.getElementById('reject-cp-form').style.display='none'" style="padding:7px 14px;border-radius:7px;font-size:13px;border:1.5px solid #dde8e1;color:#4a6356;background:white;cursor:pointer">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
