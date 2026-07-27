<?php

namespace Tests\Feature;

use App\Models\DistributionRun;
use App\Models\DistributionRunDestination;
use App\Models\DistributionSchedule;
use App\Models\DistributionScheduleDestination;
use App\Models\Location;
use App\Models\Officer;
use App\Models\Recipient;
use App\Models\Role;
use App\Models\RoutePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_distribution_report_summary(): void
    {
        $admin = $this->createUserWithRole('admin');
        $completedRun = $this->createRunWithReportData('completed', '2026-08-01');
        $this->createRunWithReportData('in_progress', '2026-08-02');

        $this->actingAs($admin)
            ->get(route('reports.distributions.index'))
            ->assertOk()
            ->assertSee('Laporan Distribusi')
            ->assertSee('Total distribusi: 2')
            ->assertSee('Selesai: 1')
            ->assertSee('Berjalan: 1')
            ->assertSee($completedRun->code);
    }

    public function test_head_can_view_report_but_officer_cannot(): void
    {
        $head = $this->createUserWithRole('kepala_sppg');
        $officer = $this->createUserWithRole('petugas');
        $run = $this->createRunWithReportData('completed', '2026-08-01');

        $this->actingAs($head)
            ->get(route('reports.distributions.index'))
            ->assertOk()
            ->assertSee($run->code);

        $this->actingAs($officer)
            ->get(route('reports.distributions.index'))
            ->assertForbidden();
    }

    public function test_report_can_filter_by_status_and_schedule_date(): void
    {
        $admin = $this->createUserWithRole('admin');
        $completedRun = $this->createRunWithReportData('completed', '2026-08-01');
        $this->createRunWithReportData('cancelled', '2026-08-01');
        $outsideDateRun = $this->createRunWithReportData('completed', '2026-09-01');

        $this->actingAs($admin)
            ->get(route('reports.distributions.index', [
                'status' => 'completed',
                'date_from' => '2026-08-01',
                'date_to' => '2026-08-31',
            ]))
            ->assertOk()
            ->assertSee($completedRun->code)
            ->assertDontSee($outsideDateRun->code)
            ->assertSee('Total distribusi: 1');
    }

    public function test_report_can_filter_by_officer(): void
    {
        $admin = $this->createUserWithRole('admin');
        $selectedOfficer = $this->createOfficer();
        $selectedRun = $this->createRunWithReportData('completed', '2026-08-01', $selectedOfficer);
        $otherRun = $this->createRunWithReportData('completed', '2026-08-01');

        $this->actingAs($admin)
            ->get(route('reports.distributions.index', ['officer_id' => $selectedOfficer->id]))
            ->assertOk()
            ->assertSee($selectedOfficer->name)
            ->assertSee($selectedRun->code)
            ->assertDontSee($otherRun->code)
            ->assertSee('Total distribusi: 1');
    }

    public function test_admin_can_view_distribution_report_detail(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRunWithReportData('completed', '2026-08-01');

        $this->actingAs($admin)
            ->get(route('reports.distributions.show', $run))
            ->assertOk()
            ->assertSee('Detail Laporan Distribusi')
            ->assertSee($run->code)
            ->assertSee('Status akhir')
            ->assertSee('Waktu aktual')
            ->assertSee('Detail tujuan')
            ->assertSee('Timeline status')
            ->assertSee('Tujuan selesai');
    }

    public function test_admin_can_export_distribution_report_csv(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRunWithReportData('completed', '2026-08-01');

        $response = $this->actingAs($admin)
            ->get(route('reports.distributions.export', ['status' => 'completed']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();

        $this->assertStringContainsString('Kode Distribusi', $content);
        $this->assertStringContainsString($run->code, $content);
        $this->assertStringContainsString('completed', $content);
    }

    public function test_admin_can_export_distribution_report_excel(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRunWithReportData('completed', '2026-08-01');

        $response = $this->actingAs($admin)
            ->get(route('reports.distributions.export-excel', ['status' => 'completed']));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('content-type'));

        $content = $response->streamedContent();

        $this->assertStringContainsString('<table border="1">', $content);
        $this->assertStringContainsString('Waktu Aktual Menit', $content);
        $this->assertStringContainsString($run->code, $content);
    }

    private function createRunWithReportData(string $status, string $scheduledDate, ?Officer $officer = null): DistributionRun
    {
        $officer ??= $this->createOfficer();
        $depot = Location::factory()->create(['type' => 'depot']);
        $location = Location::factory()->create();
        $recipient = Recipient::factory()->create(['location_id' => $location->id, 'portion_count' => 100]);
        $schedule = DistributionSchedule::factory()->create([
            'officer_id' => $officer->id,
            'depot_location_id' => $depot->id,
            'scheduled_date' => $scheduledDate,
            'status' => 'scheduled',
        ]);
        $scheduleDestination = DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $location->id,
            'recipient_id' => $recipient->id,
            'portion_count' => 100,
        ]);
        $run = DistributionRun::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'officer_id' => $officer->id,
            'status' => $status,
            'started_at' => $status === 'completed' ? '2026-08-01 08:00:00' : null,
            'completed_at' => $status === 'completed' ? '2026-08-01 09:15:00' : null,
        ]);

        DistributionRunDestination::factory()->create([
            'distribution_run_id' => $run->id,
            'distribution_schedule_destination_id' => $scheduleDestination->id,
            'location_id' => $location->id,
            'recipient_id' => $recipient->id,
            'planned_portion_count' => 100,
            'delivered_portion_count' => $status === 'completed' ? 95 : null,
            'status' => $status === 'completed' ? 'delivered' : 'pending',
            'arrived_at' => $status === 'completed' ? '2026-08-01 08:45:00' : null,
            'delivered_at' => $status === 'completed' ? '2026-08-01 09:00:00' : null,
            'proof_notes' => $status === 'completed' ? 'Porsi diterima pihak sekolah.' : null,
        ]);

        RoutePlan::factory()->create([
            'distribution_run_id' => $run->id,
            'total_distance_km' => 12.345,
            'total_estimated_minutes' => 30,
        ]);

        return $run;
    }

    private function createOfficer(): Officer
    {
        return Officer::factory()->create([
            'user_id' => $this->createUserWithRole('petugas')->id,
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            ['display_name' => str($roleName)->replace('_', ' ')->title()->toString()]
        );

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'status' => 'active',
        ], $attributes));
    }
}
