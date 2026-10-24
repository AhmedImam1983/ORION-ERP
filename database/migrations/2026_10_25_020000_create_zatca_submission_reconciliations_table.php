<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zatca_submission_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')
                ->constrained('zatca_invoice_submissions')
                ->cascadeOnDelete();

            // Append-only history of the local administrative review decision.
            $table->string('outcome', 20); // accepted, rejected, unresolved
            $table->string('previous_status', 20)->nullable();
            $table->string('new_status', 20)->nullable();
            $table->string('previous_environment', 30)->nullable();
            $table->string('new_environment', 30)->nullable();
            $table->string('target_environment', 30)->nullable();
            $table->unsignedSmallInteger('previous_status_code')->nullable();
            $table->longText('previous_response')->nullable();

            $table->string('evidence_type', 30);
            $table->string('evidence_reference', 255);
            $table->text('notes');
            $table->foreignId('reconciled_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reconciled_at');
            $table->timestamps();

            $table->index(['submission_id', 'id'], 'zatca_reconciliations_submission_id_index');
            $table->index(['outcome', 'reconciled_at'], 'zatca_reconciliations_outcome_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zatca_submission_reconciliations');
    }
};
