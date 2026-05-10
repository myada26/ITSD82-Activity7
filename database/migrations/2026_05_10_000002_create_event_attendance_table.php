<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->enum('slot', ['MORNING_IN', 'MORNING_OUT', 'AFTERNOON_IN', 'AFTERNOON_OUT']);
            $table->boolean('is_present')->default(false);    // FALSE = absent = ₱10 fine (FR-0027)
            $table->foreignId('recorded_by_user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // One row per student per slot per event (FR-0027)
            $table->unique(['event_id', 'student_id', 'slot']);
            $table->index(['event_id', 'student_id']);
            $table->index(['event_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendance');
    }
};
