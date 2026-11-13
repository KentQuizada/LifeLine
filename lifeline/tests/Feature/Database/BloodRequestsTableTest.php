<?php

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BloodRequestsTableTest extends TestCase
{
    use InsertsRows;
    use RefreshDatabase;

    public function test_duplicate_reference_number_is_rejected_by_database(): void
    {
        $this->insertBloodRequest(['reference_number' => 'LL-261113-ABC234']);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->insertBloodRequest(['reference_number' => 'LL-261113-ABC234']);
    }

    public function test_units_between_one_and_ten_are_accepted(): void
    {
        $this->insertBloodRequest(['units' => 1]);
        $this->insertBloodRequest(['units' => 10]);

        $this->assertSame(2, DB::table('blood_requests')->count());
    }

    public function test_zero_units_are_rejected(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('chk_blood_requests_units');
        $this->insertBloodRequest(['units' => 0]);
    }

    public function test_more_than_ten_units_are_rejected(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('chk_blood_requests_units');
        $this->insertBloodRequest(['units' => 11]);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage("column 'status'");
        $this->insertBloodRequest(['status' => 'approved']);
    }

    public function test_new_request_is_open(): void
    {
        $id = $this->insertBloodRequest();

        $this->assertSame('open', DB::table('blood_requests')->where('id', $id)->value('status'));
    }

    public function test_request_requires_existing_hospital(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('foreign key constraint fails');
        $this->insertBloodRequest(['hospital_id' => 999999]);
    }

    public function test_request_with_history_cannot_be_deleted(): void
    {
        $id = $this->insertBloodRequest();
        DB::table('request_history')->insert([
            'blood_request_id' => $id,
            'old_status' => null,
            'new_status' => 'open',
            'remarks' => 'Request submitted',
        ]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('foreign key constraint fails');
        DB::table('blood_requests')->where('id', $id)->delete();
    }

    public function test_requester_with_requests_cannot_be_deleted(): void
    {
        $userId = $this->insertUser();
        $this->insertBloodRequest(['requester_id' => $userId]);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('foreign key constraint fails');
        DB::table('users')->where('id', $userId)->delete();
    }

    public function test_history_rows_get_a_timestamp_automatically(): void
    {
        $id = $this->insertBloodRequest();
        DB::table('request_history')->insert(['blood_request_id' => $id, 'new_status' => 'open']);

        $this->assertNotNull(DB::table('request_history')->value('created_at'));
    }
}
