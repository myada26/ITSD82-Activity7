@extends('layouts.app')
@section('title', 'Events')
@section('page-title', 'Events')

@section('content')
<div>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px">
        <div>
            <h1 style="font-size:19px;font-weight:700;color:#0f1f17">Attendance Events</h1>
            <p style="font-size:12.5px;color:#4a6356;margin-top:2px">Manage events and track member attendance (FR-0026)</p>
        </div>
        @if(auth()->user()->hasRole('CHAIRPERSON'))
        <a href="{{ route('org.events.create') }}" style="display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;background:#1a7a41;color:white">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Event
        </a>
        @endif
    </div>

    @if($pendingAuditorCount > 0)
    <div style="background:#fef9ec;border:1px solid #fcd34d;border-radius:10px;padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;gap:8px;font-size:13px;color:#92400e;font-weight:600">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ $pendingAuditorCount }} event(s) awaiting your auditor review
    </div>
    @endif

    @if($pendingChairpersonCount > 0)
    <div style="background:#fef9ec;border:1px solid #fcd34d;border-radius:10px;padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;gap:8px;font-size:13px;color:#92400e;font-weight:600">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ $pendingChairpersonCount }} event(s) awaiting your final confirmation
    </div>
    @endif

    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#15803d;font-weight:600">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
    <div style="background:#fef9ec;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#92400e;font-weight:600">{{ session('warning') }}</div>
    @endif

    <div class="card">
        <div style="padding:13px 20px;border-bottom:1px solid #eaf0ec">
            <div style="font-size:14px;font-weight:700">All Events</div>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f0f3f1;border-bottom:1px solid #dde8e1">
                        <th style="padding:9px 13px;text-align:left;font-size:11px;font-weight:700;color:#8aa89a;text-transform:uppercase;letter-spacing:.05em">Event</th>
                        <th style="padding:9px 13px;text-align:left;font-size:11px;font-weight:700;color:#8aa89a;text-transform:uppercase;letter-spacing:.05em">Date</th>
                        <th style="padding:9px 13px;text-align:left;font-size:11px;font-weight:700;color:#8aa89a;text-transform:uppercase;letter-spacing:.05em">Type</th>
                        <th style="padding:9px 13px;text-align:left;font-size:11px;font-weight:700;color:#8aa89a;text-transform:uppercase;letter-spacing:.05em">Semester</th>
                        <th style="padding:9px 13px;text-align:left;font-size:11px;font-weight:700;color:#8aa89a;text-transform:uppercase;letter-spacing:.05em">Status</th>
                        <th style="padding:9px 13px;text-align:right;font-size:11px;font-weight:700;color:#8aa89a;text-transform:uppercase;letter-spacing:.05em">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    @php
                    $statusMap = [
                        'DRAFT'               => ['bg'=>'#f3f4f6','color'=>'#374151','label'=>'Draft'],
                        'PENDING_APPROVAL'    => ['bg'=>'#fef9ec','color'=>'#92400e','label'=>'Pending Auditor'],
                        'PENDING_CHAIRPERSON' => ['bg'=>'#eff6ff','color'=>'#1d4ed8','label'=>'Pending Chairperson'],
                        'APPROVED'            => ['bg'=>'#dcfce7','color'=>'#15803d','label'=>'Approved'],
                        'REJECTED'            => ['bg'=>'#fee2e2','color'=>'#dc2626','label'=>'Rejected'],
                    ];
                    $st = $statusMap[$event->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151','label'=>$event->status];
                    @endphp
                    <tr style="border-bottom:1px solid #eaf0ec" onmouseover="this.style.background='#f8fbf9'" onmouseout="this.style.background=''">
                        <td style="padding:10px 13px">
                            <div style="font-size:13px;font-weight:600;color:#0f1f17">{{ $event->name }}</div>
                            @if($event->venue)
                            <div style="font-size:11px;color:#8aa89a;margin-top:1px">{{ $event->venue }}</div>
                            @endif
                        </td>
                        <td style="padding:10px 13px;font-size:13px;color:#4a6356">{{ $event->date->format('M d, Y') }}</td>
                        <td style="padding:10px 13px">
                            <span style="font-size:11px;font-weight:600;padding:2px 7px;border-radius:4px;{{ $event->time_type === 'HALF_DAY' ? 'background:#e0f2fe;color:#0369a1' : 'background:#fce7f3;color:#be185d' }}">
                                {{ $event->time_type === 'HALF_DAY' ? 'Half Day' : 'Full Day' }}
                            </span>
                        </td>
                        <td style="padding:10px 13px;font-size:12.5px;color:#4a6356">{{ $event->academicYear?->name }}</td>
                        <td style="padding:10px 13px">
                            <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11.5px;font-weight:700;background:{{ $st['bg'] }};color:{{ $st['color'] }}">{{ $st['label'] }}</span>
                        </td>
                        <td style="padding:10px 13px;text-align:right">
                            <a href="{{ route('org.events.show', $event) }}" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:6px;font-size:12px;text-decoration:none;border:1.5px solid #dde8e1;color:#4a6356">View</a>
                            @if(auth()->user()->hasRole('SECRETARY') && $event->status === 'DRAFT')
                            <a href="{{ route('org.attendance.sheet', $event) }}" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:6px;font-size:12px;text-decoration:none;border:1.5px solid #1a7a41;color:#1a7a41;margin-left:4px">Open Sheet</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:44px 24px;text-align:center;color:#8aa89a">
                            <div style="font-size:14px;font-weight:600;color:#4a6356;margin-bottom:4px">No events yet</div>
                            @if(auth()->user()->hasRole('CHAIRPERSON'))
                            <div style="font-size:12.5px">Create the first event using the button above.</div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 20px;border-top:1px solid #eaf0ec;display:flex;justify-content:flex-end;background:#f8fbf9">
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection
