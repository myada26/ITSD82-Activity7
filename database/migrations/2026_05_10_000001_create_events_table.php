<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('name');
            $table->date('date');
            $table->string('venue')->nullable();
            $table->enum('time_type', ['HALF_DAY', 'FULL_DAY']);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', [
                'DRAFT',
                'PENDING_APPROVAL',       // submitted by secretary, awaiting auditor
                'PENDING_CHAIRPERSON',    // auditor made edits, awaiting chairperson confirm
                'APPROVED',
                'REJECTED',
            ])->default('DRAFT');

            // Stage 1 — Creation
            $table->foreignId('created_by_user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Stage 2 — Secretary submission
            $table->foreignId('submitted_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();

            // JSON snapshot of attendance at submission time (for Chairperson diff view — FR-0028)
            $table->json('secretary_snapshot')->nullable();

            // Stage 3 — Auditor review
            $table->foreignId('auditor_reviewed_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('auditor_reviewed_at')->nullable();

            // Stage 4 — Final approval
            $table->foreignId('approved_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
