<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Fee Accountability – FCATS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f7f5; color: #0f1f17; min-height: 100vh; }
        .container { max-width: 720px; margin: 0 auto; padding: 32px 20px; }
        .card { background: white; border: 1px solid #dde8e1; border-radius: 12px; padding: 24px 28px; }
        .badge-unpaid { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11.5px; font-weight: 700; background: #fee2e2; color: #dc2626; }
        .badge-paid   { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11.5px; font-weight: 700; background: #dcfce7; color: #15803d; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 9px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #8aa89a; text-transform: uppercase; letter-spacing: .04em; background: #f0f3f1; border-bottom: 1px solid #dde8e1; }
        td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid #eaf0ec; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        .btn { display: inline-block; padding: 9px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; border: none; background: #1a7a41; color: white; }
    </style>
</head>
<body>
<div class="container">
    {{-- Header --}}
    <div style="text-align:center;margin-bottom:28px">
        <div style="font-size:13px;font-weight:700;color:#1a7a41;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px">FCATS</div>
        <div style="font-size:22px;font-weight:700;color:#0f1f17">Student Fee Accountability</div>
        <div style="font-size:13px;color:#4a6356;margin-top:4px">Enter your student ID number to view your fee status and attendance fines.</div>
    </div>

    {{-- Search form --}}
    <div class="card" style="margin-bottom:20px">
        <form method="GET" action="{{ route('public.check-fees') }}">
            <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
                <div style="flex:1;min-width:200px">
                    <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:5px">Student ID Number</label>
                    <input type="text" name="student_number" value="{{ $studentNumber }}"
                        placeholder="e.g. 2024-00123"
                        style="width:100%;padding:9px 13px;border:1px solid #dde8e1;border-radius:8px;font-size:13.5px;outline:none"
                        required />
                </div>
                <button type="submit" class="btn">Search</button>
            </div>
        </form>
    </div>

    @if($notFound)
    <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:14px 18px;font-size:13px;color:#dc2626;font-weight:600;margin-bottom:16px">
        No student found with ID number <strong>{{ $studentNumber }}</strong>. Please check your student number and try again.
    </div>
    @endif

    @if($student)
    {{-- Student info --}}
    <div class="card" style="margin-bottom:16px">
        <div style="font-size:16px;font-weight:700;margin-bottom:2px">{{ $student->full_name }}</div>
        <div style="font-size:12.5px;color:#4a6356">
            {{ $student->student_number }}
            @if($student->latestEnrollment)
             · {{ $student->latestEnrollment->program?->name ?? '' }}
             @if($student->latestEnrollment->academicYear) · {{ $student->latestEnrollment->academicYear->name }} @endif
            @endif
        </div>
    </div>

    {{-- Fines breakdown --}}
    @php
    $unpaidTotal = $fines->where('status','UNPAID')->sum('fine_amount');
    $orgs        = $fines->groupBy('organization_id');
    @endphp

    @if($fines->isEmpty())
    <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:10px;padding:14px 18px;font-size:13px;color:#15803d;font-weight:600">
        No attendance fines on record for this student.
    </div>
    @else
    <div class="card" style="margin-bottom:16px">
        <div style="font-size:14px;font-weight:700;margin-bottom:14px">Attendance Fines Breakdown</div>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Organization</th>
                        <th>Date</th>
                        <th>Slots Missed</th>
                        <th style="text-align:right">Fine Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fines as $fine)
                    <tr>
                        <td style="font-weight:600;color:#0f1f17">{{ $fine->event?->name ?? '—' }}</td>
                        <td style="color:#4a6356">{{ $fine->organization?->name ?? '—' }}</td>
                        <td style="color:#4a6356;white-space:nowrap">{{ $fine->event?->date?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $fine->slots_missed }} of {{ $fine->event ? count((new \App\Models\Event(['time_type' => $fine->event->time_type ?? 'FULL_DAY']))->slots()) : '?' }}</td>
                        <td style="text-align:right;font-weight:600">₱{{ number_format($fine->fine_amount, 2) }}</td>
                        <td>
                            @if($fine->isPaid())
                            <span class="badge-paid">Paid</span>
                            @if($fine->transaction)
                            <div style="font-size:10.5px;color:#8aa89a;margin-top:2px">OR: {{ $fine->transaction->or_number }}</div>
                            @endif
                            @else
                            <span class="badge-unpaid">Unpaid</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @if($unpaidTotal > 0)
                <tfoot>
                    <tr style="background:#f8fbf9">
                        <td colspan="4" style="font-weight:700;text-align:right;padding:10px 12px">Total Outstanding Balance</td>
                        <td style="font-weight:700;color:#dc2626;font-size:14px;text-align:right;padding:10px 12px">₱{{ number_format($unpaidTotal, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    @endif

    @if($unpaidTotal > 0)
    <div style="background:#fef9ec;border:1px solid #fcd34d;border-radius:10px;padding:14px 18px;font-size:13px;color:#92400e">
        <strong>Outstanding balance: ₱{{ number_format($unpaidTotal, 2) }}</strong><br>
        <span style="font-size:12px">Please approach your organization treasurer to settle your attendance fines.</span>
    </div>
    @endif

    @endif
</div>

<div style="text-align:center;padding:20px;font-size:11.5px;color:#8aa89a">
    FCATS — Fee Collection and Tracking System · This page is read-only and for inquiry purposes only.
</div>
</body>
</html>
