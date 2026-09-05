<?php

namespace App\Console\Commands;

use App\Models\DistributionRun;
use App\Models\DistributionRunDestination;
use App\Models\DistributionSchedule;
use App\Models\DistributionScheduleDestination;
use App\Models\Location;
use App\Models\Officer;
use App\Models\OfficerPosition;
use App\Models\Recipient;
use App\Models\RoutePlan;
use App\Models\RoutePlanStep;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WipeOperationalData extends Command
{
    protected $signature = 'app:wipe-operational-data {--force : Skip the confirmation prompt}';

    protected $description = 'Permanently delete all master/operational data (locations, officers, recipients, schedules, runs, routes) while keeping user login accounts and roles intact.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will PERMANENTLY delete all locations, officers, recipients, schedules, runs and routes. Continue?')) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        DB::transaction(function (): void {
            // Deleted in an order that respects restrictOnDelete foreign keys:
            // children/leaf tables first, up to root master-data tables last.
            RoutePlanStep::query()->delete();
            OfficerPosition::query()->delete();
            RoutePlan::query()->delete();
            DistributionRunDestination::query()->delete();
            DistributionRun::query()->delete();
            DistributionScheduleDestination::query()->delete();
            DistributionSchedule::query()->delete();
            Recipient::query()->delete();
            Officer::query()->delete();
            Location::query()->delete();
        });

        $this->info('Operational data wiped. User login accounts and roles were left untouched.');

        return self::SUCCESS;
    }
}
