<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAttendance extends Model
{
    // Uses recorded_at + updated_at manually — no auto timestamps()
    public $timestamps = false;

    protected $table = 'event_attendance';

    protected $fillable = [
        'event_id',
        'student_id',
        'slot',
        'is_present',
        'recorded_by_user_id',
        'recorded_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_present'  => 'boolean',
            'recorded_at' => 'datetime',
            'updated_at'  => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
