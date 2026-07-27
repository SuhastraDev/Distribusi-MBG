<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('distribution_schedule_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('officer_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('ready');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_runs');
    }
};
