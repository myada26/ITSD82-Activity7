<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->unsignedSmallInteger('slots_missed');
            $table->decimal('fine_amount', 10, 2);            // slots_missed × ₱10.00 (FR-0029)
            $table->enum('status', ['UNPAID', 'PAID'])->default('UNPAID');
            $table->foreignId('transaction_id')
                  ->nullable()
                  ->constrained('transactions')
                  ->nullOnDelete();                            // set when paid via POS
            $table->timestamps();

            // One fine record per student per event (FR-0029)
            $table->unique(['student_id', 'event_id']);
            $table->index(['student_id', 'organization_id', 'status']); // public lookup + POS
            $table->index(['organization_id', 'academic_year_id', 'status']); // org reporting
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fines');
    }
};
