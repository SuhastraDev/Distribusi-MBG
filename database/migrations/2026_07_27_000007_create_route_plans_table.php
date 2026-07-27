<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('distribution_run_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('algorithm')->default('greedy_nearest_neighbor');
            $table->decimal('total_distance_km', 10, 3)->default(0);
            $table->unsignedInteger('total_estimated_minutes')->default(0);
            $table->string('status')->default('generated');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_plans');
    }
};
