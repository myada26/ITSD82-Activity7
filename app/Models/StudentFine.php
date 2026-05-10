<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFine extends Model
{
    protected $fillable = [
        'student_id',
        'organization_id',
        'event_id',
        'academic_year_id',
        'slots_missed',
        'fine_amount',
        'status',
        'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'fine_amount'  => 'decimal:2',
            'slots_missed' => 'integer',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Nullable — set when paid via POS (FR-0029)
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }
}
