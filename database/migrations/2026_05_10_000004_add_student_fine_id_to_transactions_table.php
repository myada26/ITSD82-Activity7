<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Links a FINE transaction to its specific StudentFine record (FR-0029)
            // Enables FineService::markPaid to reliably sync fine status after POS payment.
            // nullOnDelete: voiding a transaction un-links the fine (allowing re-payment) without
            // cascade-deleting the fine record itself.
            $table->foreignId('student_fine_id')
                  ->nullable()
                  ->after('transaction_type')
                  ->constrained('student_fines')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['student_fine_id']);
            $table->dropColumn('student_fine_id');
        });
    }
};
