<?php

namespace Tests\Feature\Database;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_data_loads(): void
    {
        $this->seed();

        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(5, User::where('role', 'requester')->count());
        $this->assertSame(30, Donor::count());
        $this->assertSame(3, Hospital::count());
        $this->assertSame(5, BloodRequest::count());
        $this->assertSame(0, BloodRequest::doesntHave('history')->count());
        $this->assertSame(0, Donor::where('verification_status', 'verified')->whereNull('verified_at')->count());
    }

    public function test_sample_hospitals_are_clearly_fake(): void
    {
        $this->seed();

        $this->assertSame(0, Hospital::where('name', 'not like', 'Sample %')->count());
    }

    public function test_reference_number_format(): void
    {
        $reference = BloodRequest::generateReferenceNumber();

        $this->assertMatchesRegularExpression('/^LL-\d{6}-[A-HJ-NP-Z2-9]{6}$/', $reference);
        $this->assertSame(16, strlen($reference));
    }

    public function test_relationships_connect_the_tables(): void
    {
        $this->seed();

        $request = BloodRequest::first();
        $this->assertSame('requester', $request->requester->role);
        $this->assertNotNull($request->hospital);
        $this->assertTrue($request->history->isNotEmpty());

        $donor = Donor::first();
        $this->assertSame($donor->id, $donor->user->donor->id);
    }
}
