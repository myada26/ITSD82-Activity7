<?php

namespace App\Services;

use App\Models\Event;
use App\Models\StudentFine;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class FineService
{
    // Fine rate per unchecked slot (FR-0029)
    public const FINE_PER_SLOT = 10.00;

    /**
     * Compute and persist StudentFine records for every absent member of the event.
     * Called when attendance status transitions to APPROVED.
     *
     * Uses insertOrIgnore — the UNIQUE(student_id, event_id) constraint means this is
     * safe to call twice without double-counting (idempotent).
     *
     * @return int Number of fine records created.
     */
    public function computeFines(Event $event): int
    {
        $slots = $event->slots();
        $now   = now();

        $absentCounts = DB::table('event_attendance')
            ->where('event_id', $event->id)
            ->where('is_present', false)
            ->whereIn('slot', $slots)
            ->select('student_id', DB::raw('COUNT(*) as missed_count'))
            ->groupBy('student_id')
            ->get();

        $rows = [];
        foreach ($absentCounts as $row) {
            if ($row->missed_count <= 0) {
                continue;
            }
            $rows[] = [
                'student_id'       => $row->student_id,
                'organization_id'  => $event->organization_id,
                'event_id'         => $event->id,
                'academic_year_id' => $event->academic_year_id,
                'slots_missed'     => $row->missed_count,
                'fine_amount'      => round($row->missed_count * self::FINE_PER_SLOT, 2),
                'status'           => 'UNPAID',
                'transaction_id'   => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('student_fines')->insertOrIgnore($rows);
        }

        return count($rows);
    }

    /**
     * Mark a StudentFine as PAID when its linked FINE transaction is confirmed.
     * No-ops if the transaction was not linked to a specific fine (backward-compatible).
     */
    public function markPaid(Transaction $transaction): void
    {
        if (!$transaction->student_fine_id) {
            return;
        }

        StudentFine::where('id', $transaction->student_fine_id)
            ->where('status', 'UNPAID')
            ->update([
                'status'         => 'PAID',
                'transaction_id' => $transaction->id,
                'updated_at'     => now(),
            ]);
    }

    /**
     * Revert a StudentFine to UNPAID when its payment transaction is voided.
     */
    public function revertPaid(Transaction $transaction): void
    {
        if (!$transaction->student_fine_id) {
            return;
        }

        StudentFine::where('id', $transaction->student_fine_id)
            ->where('status', 'PAID')
            ->update([
                'status'         => 'UNPAID',
                'transaction_id' => null,
                'updated_at'     => now(),
            ]);
    }
}
