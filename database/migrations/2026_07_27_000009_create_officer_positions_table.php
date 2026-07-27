<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officer_positions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('distribution_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('officer_id')->constrained()->restrictOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['distribution_run_id', 'recorded_at']);
            $table->index(['officer_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_positions');
    }
};
