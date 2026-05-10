<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Student;
use App\Services\FineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function sheet(Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);

        $slots = $event->slots();

        // Paginate students but keep the full attendance map for the live counter
        $allAttendanceRows = EventAttendance::where('event_id', $event->id)
            ->whereIn('slot', $slots)
            ->get(['student_id', 'slot', 'is_present']);

        // Build full map {studentId: {slot: bool}} for Alpine.js
        $attendanceMap = [];
        foreach ($allAttendanceRows as $row) {
            $attendanceMap[$row->student_id][$row->slot] = (bool) $row->is_present;
        }

        // Student list (paginated for rendering, 100 per page)
        $studentIds = array_keys($attendanceMap);
        $students   = Student::whereIn('id', $studentIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(100)
            ->withQueryString();

        $user    = auth()->user();
        $canEdit = $event->status === 'DRAFT' && $user->hasRole('SECRETARY');

        // Auditor may also toggle slots while event is PENDING_APPROVAL
        $auditorCanEdit = $event->status === 'PENDING_APPROVAL' && $user->hasRole('AUDITOR');

        $attendanceData = [
            'attendance'     => $attendanceMap,
            'canEdit'        => $canEdit || $auditorCanEdit,
            'toggleBaseUrl'  => route('org.attendance.toggle-slot', [
                'event'   => $event->id,
                'student' => '__STUDENT__',
                'slot'    => '__SLOT__',
            ]),
            'totalStudents' => count($studentIds),
        ];

        return view('org.attendance.sheet', compact('event', 'slots', 'students', 'attendanceData'));
    }

    public function toggleSlot(Request $request, Event $event, Student $student, string $slot): JsonResponse
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);
        abort_unless(in_array($slot, ['MORNING_IN', 'MORNING_OUT', 'AFTERNOON_IN', 'AFTERNOON_OUT'], true), 422);

        $user = auth()->user();

        // Secretary can only toggle DRAFT events; Auditor can toggle PENDING_APPROVAL
        if ($user->hasRole('SECRETARY')) {
            abort_unless($event->status === 'DRAFT', 403, 'Attendance can only be edited for DRAFT events.');
        } elseif ($user->hasRole('AUDITOR')) {
            abort_unless($event->status === 'PENDING_APPROVAL', 403, 'Auditor can only edit PENDING_APPROVAL events.');
        } else {
            abort(403);
        }

        $record = EventAttendance::where([
            'event_id'   => $event->id,
            'student_id' => $student->id,
            'slot'       => $slot,
        ])->first();

        $wasPresent = $record ? $record->is_present : false;
        $nowPresent = !$wasPresent;

        if ($record) {
            $record->update([
                'is_present'          => $nowPresent,
                'recorded_by_user_id' => $user->id,
                'recorded_at'         => now(),
                'updated_at'          => now(),
            ]);
        } else {
            // Student enrolled after event creation — create the row
            $record = EventAttendance::create([
                'event_id'            => $event->id,
                'student_id'          => $student->id,
                'slot'                => $slot,
                'is_present'          => $nowPresent,
                'recorded_by_user_id' => $user->id,
                'recorded_at'         => now(),
                'updated_at'          => now(),
            ]);
        }

        // Audit each individual slot toggle made by auditor (FR-0028)
        if ($user->hasRole('AUDITOR')) {
            AuditLog::create([
                'user_id'     => $user->id,
                'action'      => 'ATTENDANCE_EDITED_BY_AUDITOR',
                'entity_type' => 'EVENT',
                'entity_id'   => $event->id,
                'details'     => [
                    'student_id' => $student->id,
                    'slot'       => $slot,
                    'old_value'  => $wasPresent,
                    'new_value'  => $nowPresent,
                ],
                'ip_address' => $request->ip(),
                'timestamp'  => now(),
            ]);
        }

        return response()->json(['is_present' => $record->is_present]);
    }

    public function submit(Request $request, Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);
        abort_unless($event->status === 'DRAFT', 403, 'Only DRAFT events can be submitted.');
        abort_unless(auth()->user()->hasRole('SECRETARY'), 403);

        // Capture immutable snapshot before auditor could edit (FR-0028)
        $snapshot = EventAttendance::where('event_id', $event->id)
            ->get(['student_id', 'slot', 'is_present'])
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->pluck('is_present', 'slot')->toArray())
            ->toArray();

        $event->update([
            'status'               => 'PENDING_APPROVAL',
            'submitted_by_user_id' => auth()->user()->id,
            'submitted_at'         => now(),
            'secretary_snapshot'   => $snapshot,
        ]);

        AuditLog::create([
            'user_id'     => auth()->user()->id,
            'action'      => 'ATTENDANCE_SUBMITTED',
            'entity_type' => 'EVENT',
            'entity_id'   => $event->id,
            'details'     => ['event_name' => $event->name],
            'ip_address'  => $request->ip(),
            'timestamp'   => now(),
        ]);

        return redirect()->route('org.events.show', $event)
            ->with('success', 'Attendance submitted for auditor review.');
    }

    public function auditorApprove(Request $request, Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);
        abort_unless($event->status === 'PENDING_APPROVAL', 403, 'Event is not pending approval.');
        abort_unless(auth()->user()->hasRole('AUDITOR'), 403);

        $finesCount = 0;

        DB::transaction(function () use ($event, $request, &$finesCount) {
            // Guard against double-compute
            if (!$event->fines()->exists()) {
                $finesCount = app(FineService::class)->computeFines($event);
            }

            $event->update([
                'status'                      => 'APPROVED',
                'auditor_reviewed_by_user_id' => auth()->user()->id,
                'auditor_reviewed_at'         => now(),
                'approved_by_user_id'         => auth()->user()->id,
                'approved_at'                 => now(),
            ]);

            AuditLog::create([
                'user_id'     => auth()->user()->id,
                'action'      => 'ATTENDANCE_APPROVED_BY_AUDITOR',
                'entity_type' => 'EVENT',
                'entity_id'   => $event->id,
                'details'     => ['event_name' => $event->name],
                'ip_address'  => $request->ip(),
                'timestamp'   => now(),
            ]);

            AuditLog::create([
                'user_id'     => auth()->user()->id,
                'action'      => 'FINES_COMPUTED',
                'entity_type' => 'EVENT',
                'entity_id'   => $event->id,
                'details'     => ['fines_created' => $finesCount],
                'ip_address'  => $request->ip(),
                'timestamp'   => now(),
            ]);
        });

        return redirect()->route('org.events.show', $event)
            ->with('success', "Attendance approved. {$finesCount} fine record(s) computed.");
    }

    public function auditorForward(Request $request, Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);
        abort_unless($event->status === 'PENDING_APPROVAL', 403, 'Event is not pending approval.');
        abort_unless(auth()->user()->hasRole('AUDITOR'), 403);

        $event->update([
            'status'                      => 'PENDING_CHAIRPERSON',
            'auditor_reviewed_by_user_id' => auth()->user()->id,
            'auditor_reviewed_at'         => now(),
        ]);

        AuditLog::create([
            'user_id'     => auth()->user()->id,
            'action'      => 'ATTENDANCE_SENT_TO_CHAIRPERSON',
            'entity_type' => 'EVENT',
            'entity_id'   => $event->id,
            'details'     => ['event_name' => $event->name],
            'ip_address'  => $request->ip(),
            'timestamp'   => now(),
        ]);

        return redirect()->route('org.events.show', $event)
            ->with('success', 'Attendance forwarded to Chairperson for final review.');
    }

    public function auditorReject(Request $request, Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);
        abort_unless($event->status === 'PENDING_APPROVAL', 403, 'Event is not pending approval.');
        abort_unless(auth()->user()->hasRole('AUDITOR'), 403);

        $reason = $request->validate(['rejection_reason' => 'required|string|max:2000'])['rejection_reason'];

        $event->update([
            'status'                      => 'DRAFT',
            'auditor_reviewed_by_user_id' => auth()->user()->id,
            'auditor_reviewed_at'         => now(),
            'rejection_reason'            => $reason,
        ]);

        AuditLog::create([
            'user_id'     => auth()->user()->id,
            'action'      => 'ATTENDANCE_REJECTED_BY_AUDITOR',
            'entity_type' => 'EVENT',
            'entity_id'   => $event->id,
            'details'     => ['reason' => $reason],
            'ip_address'  => $request->ip(),
            'timestamp'   => now(),
        ]);

        return redirect()->route('org.events.show', $event)
            ->with('success', 'Attendance rejected and returned to Secretary for revision.');
    }

    public function diff(Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);
        abort_unless($event->status === 'PENDING_CHAIRPERSON', 403, 'Diff view is only available for events pending Chairperson review.');
        abort_unless(auth()->user()->hasRole('CHAIRPERSON'), 403);

        $slots            = $event->slots();
        $secretarySnapshot = $event->secretary_snapshot ?? [];

        $currentAttendance = EventAttendance::where('event_id', $event->id)
            ->whereIn('slot', $slots)
            ->get(['student_id', 'slot', 'is_present'])
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->pluck('is_present', 'slot')->toArray())
            ->toArray();

        $studentIds = array_unique(array_merge(
            array_keys($secretarySnapshot),
            array_keys($currentAttendance)
        ));

        $students = Student::whereIn('id', $studentIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('org.attendance.diff', compact(
            'event', 'slots', 'students', 'secretarySnapshot', 'currentAttendance'
        ));
    }

    public function chairpersonConfirm(Request $request, Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);
        abort_unless($event->status === 'PENDING_CHAIRPERSON', 403, 'Event is not awaiting Chairperson confirmation.');
        abort_unless(auth()->user()->hasRole('CHAIRPERSON'), 403);

        $finesCount = 0;

        DB::transaction(function () use ($event, $request, &$finesCount) {
            if (!$event->fines()->exists()) {
                $finesCount = app(FineService::class)->computeFines($event);
            }

            $event->update([
                'status'              => 'APPROVED',
                'approved_by_user_id' => auth()->user()->id,
                'approved_at'         => now(),
            ]);

            AuditLog::create([
                'user_id'     => auth()->user()->id,
                'action'      => 'ATTENDANCE_APPROVED_BY_CHAIRPERSON',
                'entity_type' => 'EVENT',
                'entity_id'   => $event->id,
                'details'     => ['event_name' => $event->name],
                'ip_address'  => $request->ip(),
                'timestamp'   => now(),
            ]);

            AuditLog::create([
                'user_id'     => auth()->user()->id,
                'action'      => 'FINES_COMPUTED',
                'entity_type' => 'EVENT',
                'entity_id'   => $event->id,
                'details'     => ['fines_created' => $finesCount],
                'ip_address'  => $request->ip(),
                'timestamp'   => now(),
            ]);
        });

        return redirect()->route('org.events.show', $event)
            ->with('success', "Attendance confirmed. {$finesCount} fine record(s) computed.");
    }

    public function chairpersonReject(Request $request, Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);
        abort_unless($event->status === 'PENDING_CHAIRPERSON', 403, 'Event is not awaiting Chairperson review.');
        abort_unless(auth()->user()->hasRole('CHAIRPERSON'), 403);

        $reason = $request->validate(['rejection_reason' => 'required|string|max:2000'])['rejection_reason'];

        $event->update([
            'status'           => 'PENDING_APPROVAL',
            'rejection_reason' => $reason,
        ]);

        AuditLog::create([
            'user_id'     => auth()->user()->id,
            'action'      => 'ATTENDANCE_REJECTED_BY_CHAIRPERSON',
            'entity_type' => 'EVENT',
            'entity_id'   => $event->id,
            'details'     => ['reason' => $reason],
            'ip_address'  => $request->ip(),
            'timestamp'   => now(),
        ]);

        return redirect()->route('org.events.show', $event)
            ->with('success', 'Edits rejected. Attendance returned to Auditor for re-review.');
    }
}
