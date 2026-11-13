<?php

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MatchesDonationsNotificationsTest extends TestCase
{
    use InsertsRows;
    use RefreshDatabase;

    public function test_donor_is_matched_at_most_once_per_request(): void
    {
        $requestId = $this->insertBloodRequest();
        $donorId = $this->insertDonor();
        $match = ['blood_request_id' => $requestId, 'donor_id' => $donorId, 'distance_km' => 1.2];
        DB::table('donor_matches')->insert($match);

        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('donor_matches')->insert($match);
    }

    public function test_new_match_is_notified(): void
    {
        DB::table('donor_matches')->insert([
            'blood_request_id' => $this->insertBloodRequest(),
            'donor_id' => $this->insertDonor(),
            'distance_km' => 3.5,
        ]);

        $this->assertSame('notified', DB::table('donor_matches')->value('status'));
    }

    public function test_invalid_match_status_is_rejected(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage("column 'status'");
        DB::table('donor_matches')->insert([
            'blood_request_id' => $this->insertBloodRequest(),
            'donor_id' => $this->insertDonor(),
            'distance_km' => 3.5,
            'status' => 'maybe',
        ]);
    }

    public function test_donation_requires_existing_donor(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('foreign key constraint fails');
        DB::table('donations')->insert(['donor_id' => 999999, 'donated_on' => '2026-01-15']);
    }

    public function test_self_reported_donation_needs_no_request_or_hospital(): void
    {
        DB::table('donations')->insert([
            'donor_id' => $this->insertDonor(),
            'donated_on' => '2026-01-15',
            'is_self_reported' => true,
        ]);

        $this->assertSame(1, DB::table('donations')->whereNull('blood_request_id')->whereNull('hospital_id')->count());
    }

    public function test_donor_with_donation_history_cannot_be_deleted(): void
    {
        $donorId = $this->insertDonor();
        DB::table('donations')->insert(['donor_id' => $donorId, 'donated_on' => '2026-01-15']);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('foreign key constraint fails');
        DB::table('donors')->where('id', $donorId)->delete();
    }

    public function test_deleting_user_removes_their_notifications(): void
    {
        $userId = $this->insertUser();
        DB::table('notifications')->insert([
            'user_id' => $userId,
            'title' => 'Urgent: O- blood needed',
            'message' => 'Sample General Hospital needs 2 units.',
        ]);

        DB::table('users')->where('id', $userId)->delete();

        $this->assertSame(0, DB::table('notifications')->count());
    }

    public function test_notification_starts_unread_with_timestamp(): void
    {
        DB::table('notifications')->insert([
            'user_id' => $this->insertUser(),
            'title' => 'Thank you for donating!',
            'message' => 'Your donation was recorded.',
        ]);

        $row = DB::table('notifications')->first();
        $this->assertNull($row->read_at);
        $this->assertNotNull($row->created_at);
    }
}
