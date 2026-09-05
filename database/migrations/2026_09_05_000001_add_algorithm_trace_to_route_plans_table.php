<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_plans', function (Blueprint $table): void {
            $table->json('algorithm_trace')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('route_plans', function (Blueprint $table): void {
            $table->dropColumn('algorithm_trace');
        });
    }
};
