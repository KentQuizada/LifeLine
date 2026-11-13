<?php

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DonorsTableTest extends TestCase
{
    use InsertsRows;
    use RefreshDatabase;

    public function test_invalid_blood_type_is_rejected(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage("column 'blood_type'");
        $this->insertDonor(['blood_type' => 'C+']);
    }

    public function test_weight_must_be_positive(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('chk_donors_weight');
        $this->insertDonor(['weight_kg' => 0]);
    }

    public function test_user_has_at_most_one_donor_profile(): void
    {
        $userId = $this->insertUser(['role' => 'donor']);
        $this->insertDonor(['user_id' => $userId]);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->insertDonor(['user_id' => $userId]);
    }

    public function test_exact_coordinates_are_rounded_to_about_one_kilometre(): void
    {
        $donorId = $this->insertDonor(['approx_latitude' => 8.947412, 'approx_longitude' => 125.540617]);

        $row = DB::table('donors')->where('id', $donorId)->first();
        $this->assertSame('8.95', (string) $row->approx_latitude);
        $this->assertSame('125.54', (string) $row->approx_longitude);
    }

    public function test_new_donor_defaults(): void
    {
        $donorId = $this->insertDonor();

        $row = DB::table('donors')->where('id', $donorId)->first();
        $this->assertSame('pending', $row->verification_status);
        $this->assertEquals(1, $row->is_available);
        $this->assertEquals(0, $row->location_sharing);
    }

    public function test_deleting_user_removes_donor_profile(): void
    {
        $userId = $this->insertUser(['role' => 'donor']);
        $this->insertDonor(['user_id' => $userId]);

        DB::table('users')->where('id', $userId)->delete();

        $this->assertSame(0, DB::table('donors')->where('user_id', $userId)->count());
    }
}
