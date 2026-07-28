<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_run_destinations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('distribution_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distribution_schedule_destination_id')->constrained(indexName: 'drd_sched_dest_id_foreign')->restrictOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->foreignId('recipient_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('planned_portion_count');
            $table->unsignedInteger('delivered_portion_count')->nullable();
            $table->unsignedInteger('sequence_order')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('proof_notes')->nullable();
            $table->timestamps();

            $table->unique(['distribution_run_id', 'distribution_schedule_destination_id'], 'drd_run_sched_dest_unique');
            $table->index(['distribution_run_id', 'status'], 'drd_run_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_run_destinations');
    }
};
