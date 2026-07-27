<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_plan_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('route_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distribution_run_destination_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('step_type');
            $table->decimal('distance_from_previous_km', 10, 3)->default(0);
            $table->decimal('cumulative_distance_km', 10, 3)->default(0);
            $table->timestamps();

            $table->unique(['route_plan_id', 'step_order']);
            $table->index(['route_plan_id', 'step_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_plan_steps');
    }
};
