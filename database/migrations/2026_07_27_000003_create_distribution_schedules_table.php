<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_schedules', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->date('scheduled_date');
            $table->foreignId('officer_id')->constrained()->restrictOnDelete();
            $table->foreignId('depot_location_id')->constrained('locations')->restrictOnDelete();
            $table->unsignedInteger('total_portions')->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['scheduled_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_schedules');
    }
};
