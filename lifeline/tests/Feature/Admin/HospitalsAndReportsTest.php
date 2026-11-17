<?php

namespace Tests\Feature\Admin;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** M2.6: hospital CRUD and the reports with CSV exports. */
class HospitalsAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function hospitalForm(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Sample Test Hospital',
            'type' => 'hospital',
            'address' => 'Sample street',
            'city' => 'Butuan City',
            'latitude' => '8.947500',
            'longitude' => '125.540600',
            'contact_number' => '',
        ];
    }

    public function test_admins_can_add_edit_and_delete_a_hospital(): void
    {
        $this->actingAs($this->admin)->post(route('admin.hospitals.store'), $this->hospitalForm())
            ->assertRedirect(route('admin.hospitals.index'));
        $hospital = Hospital::where('name', 'Sample Test Hospital')->firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.hospitals.edit', $hospital))->assertOk()->assertSee('Sample Test Hospital');
        $this->actingAs($this->admin)->put(route('admin.hospitals.update', $hospital), $this->hospitalForm(['type' => 'blood_bank']));
        $this->assertSame('blood_bank', $hospital->refresh()->type);

        $this->actingAs($this->admin)->delete(route('admin.hospitals.destroy', $hospital));
        $this->assertModelMissing($hospital);
    }

    public function test_hospital_validation(): void
    {
        Hospital::factory()->create(['name' => 'Taken Name']);

        $this->actingAs($this->admin)->post(route('admin.hospitals.store'), $this->hospitalForm([
            'name' => 'Taken Name', 'type' => 'clinic', 'latitude' => '40.7', 'address' => '',
        ]))->assertSessionHasErrors(['name', 'type', 'latitude', 'address']);
    }

    public function test_a_hospital_with_requests_cannot_be_deleted(): void
    {
        $hospital = Hospital::factory()->create();
        BloodRequest::factory()->create(['hospital_id' => $hospital->id]);

        $this->actingAs($this->admin)->delete(route('admin.hospitals.destroy', $hospital))->assertSessionHas('error');
        $this->assertModelExists($hospital);
    }

    public function test_the_report_summarises_requests_in_the_date_range(): void
    {
        BloodRequest::factory()->count(2)->create(['blood_type' => 'O+', 'status' => 'open']);
        $old = BloodRequest::factory()->create(['blood_type' => 'O+']);
        $old->forceFill(['created_at' => now()->subDays(60)])->save();

        $this->actingAs($this->admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSeeInOrder(['<strong>2</strong>', 'blood request(s)'], false);

        $this->actingAs($this->admin)->get(route('admin.reports.index', ['from' => '2026-12-31', 'to' => '2026-01-01']))
            ->assertSessionHasErrors('to');
    }

    public function test_the_requests_csv_export(): void
    {
        $bloodRequest = BloodRequest::factory()->create(['patient_name' => 'Hidden Patient']);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.requests'));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('"Reference number",Submitted,Status', $csv);
        $this->assertStringContainsString($bloodRequest->reference_number, $csv);
        $this->assertStringNotContainsString('Hidden Patient', $csv);
    }

    public function test_the_donors_csv_has_no_contact_details_and_neutralises_formulas(): void
    {
        $donor = Donor::factory()->create(['city' => '=HYPERLINK("x")']);

        $csv = $this->actingAs($this->admin)->get(route('admin.reports.donors'))->assertOk()->streamedContent();

        $this->assertStringNotContainsString($donor->user->email, $csv);
        $this->assertStringNotContainsString($donor->user->mobile_number, $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_reports_are_admin_only(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))->get(route('admin.reports.requests'))->assertForbidden();
    }
}
