<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_schedule_destinations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('distribution_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->foreignId('recipient_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('portion_count');
            $table->unsignedInteger('sequence_order')->default(0);
            $table->timestamps();

            $table->unique(['distribution_schedule_id', 'location_id']);
            $table->unique(['distribution_schedule_id', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_schedule_destinations');
    }
};
