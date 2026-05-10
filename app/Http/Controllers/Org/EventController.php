<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Event;
use App\Services\AttendancePopulationService;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        $orgId  = auth()->user()->organization_id;
        $user   = auth()->user();

        $query = Event::where('organization_id', $orgId)
            ->with(['createdBy', 'academicYear'])
            ->orderByDesc('date');

        // Secretary only sees events they can interact with (DRAFT)
        if ($user->hasRole('SECRETARY')) {
            $query->whereIn('status', ['DRAFT', 'APPROVED']);
        }

        $events = $query->paginate(20);

        $pendingAuditorCount = 0;
        if ($user->hasRole('AUDITOR')) {
            $pendingAuditorCount = Event::where('organization_id', $orgId)
                ->where('status', 'PENDING_APPROVAL')
                ->count();
        }

        $pendingChairpersonCount = 0;
        if ($user->hasRole('CHAIRPERSON')) {
            $pendingChairpersonCount = Event::where('organization_id', $orgId)
                ->where('status', 'PENDING_CHAIRPERSON')
                ->count();
        }

        return view('org.events.index', compact('events', 'pendingAuditorCount', 'pendingChairpersonCount'));
    }

    public function create()
    {
        return view('org.events.create');
    }

    public function store(StoreEventRequest $request)
    {
        $orgId  = auth()->user()->organization_id;
        $sem    = AcademicYear::where('is_active', true)->firstOrFail();

        $event = DB::transaction(function () use ($request, $orgId, $sem) {
            $event = Event::create([
                ...$request->validated(),
                'organization_id'    => $orgId,
                'academic_year_id'   => $sem->id,
                'status'             => 'DRAFT',
                'created_by_user_id' => auth()->user()->id,
            ]);

            $count = app(AttendancePopulationService::class)->populate($event);

            if ($count === 0) {
                session()->flash('warning', 'Event created, but no enrolled students were found for this semester. The attendance sheet is currently empty.');
            }

            return $event;
        });

        AuditLog::create([
            'user_id'     => auth()->user()->id,
            'action'      => 'EVENT_CREATED',
            'entity_type' => 'EVENT',
            'entity_id'   => $event->id,
            'details'     => [
                'name'      => $event->name,
                'date'      => $event->date->toDateString(),
                'time_type' => $event->time_type,
            ],
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        return redirect()->route('org.events.show', $event)
            ->with('success', "Event \"{$event->name}\" created.");
    }

    public function show(Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);

        $event->load([
            'organization',
            'academicYear',
            'createdBy',
            'submittedBy',
            'auditorReviewer',
            'approvedBy',
        ]);

        $presentCountBySlot = [];
        $totalStudents      = 0;

        if ($event->status !== 'DRAFT') {
            foreach ($event->slots() as $slot) {
                $presentCountBySlot[$slot] = $event->attendance()
                    ->where('slot', $slot)
                    ->where('is_present', true)
                    ->count();
            }
            $totalStudents = $event->attendance()
                ->where('slot', $event->slots()[0])
                ->count();
        } else {
            $totalStudents = $event->attendance()
                ->where('slot', $event->slots()[0])
                ->count();
        }

        return view('org.events.show', compact('event', 'presentCountBySlot', 'totalStudents'));
    }

    public function resync(Event $event)
    {
        abort_unless($event->organization_id === auth()->user()->organization_id, 403);
        abort_unless($event->status === 'DRAFT', 403, 'Re-sync is only available for DRAFT events.');

        $count = app(AttendancePopulationService::class)->populate($event);

        return back()->with('success', "{$count} new student rows added to the attendance sheet.");
    }
}
