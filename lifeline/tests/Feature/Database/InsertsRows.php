<?php

namespace Tests\Feature\Database;

use Illuminate\Support\Facades\DB;

trait InsertsRows
{
    private int $rowCounter = 0;

    protected function insertUser(array $overrides = []): int
    {
        $this->rowCounter++;

        return DB::table('users')->insertGetId(array_merge([
            'role' => 'requester',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => "user{$this->rowCounter}@example.test",
            'mobile_number' => '0917'.str_pad((string) $this->rowCounter, 7, '0', STR_PAD_LEFT),
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function insertHospital(array $overrides = []): int
    {
        $this->rowCounter++;

        return DB::table('hospitals')->insertGetId(array_merge([
            'name' => "Sample Hospital {$this->rowCounter}",
            'type' => 'hospital',
            'address' => 'Sample address',
            'city' => 'Butuan City',
            'latitude' => 8.947500,
            'longitude' => 125.540600,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function insertDonor(array $overrides = []): int
    {
        $userId = $overrides['user_id'] ?? $this->insertUser(['role' => 'donor']);

        return DB::table('donors')->insertGetId(array_merge([
            'user_id' => $userId,
            'blood_type' => 'O+',
            'birthdate' => '2000-01-15',
            'weight_kg' => 60,
            'city' => 'Butuan City',
            'approx_latitude' => 8.95,
            'approx_longitude' => 125.54,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function insertBloodRequest(array $overrides = []): int
    {
        $this->rowCounter++;
        $reference = sprintf('LL-261113-TST%03d', $this->rowCounter);

        return DB::table('blood_requests')->insertGetId(array_merge([
            'reference_number' => $reference,
            'requester_id' => $overrides['requester_id'] ?? $this->insertUser(),
            'hospital_id' => $overrides['hospital_id'] ?? $this->insertHospital(),
            'patient_name' => 'Test Patient',
            'blood_type' => 'O+',
            'units' => 2,
            'urgency' => 'critical',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
