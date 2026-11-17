<?php

namespace Database\Seeders;

use App\Models\Administrator;
use App\Models\BloodRequest;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /** Sample records for local testing and the demo. Login password for every sample user: "password". */
    public function run(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'first_name' => 'LifeLine',
            'last_name' => 'Admin',
            'email' => 'admin@lifeline.test',
        ]);
        Administrator::create(['user_id' => $admin->id, 'position' => 'System Administrator']);

        // Clearly fake hospitals until the team supplies verified names (DEC-010).
        $hospitals = collect([
            ['name' => 'Sample General Hospital', 'type' => 'hospital', 'latitude' => 8.947500, 'longitude' => 125.540600],
            ['name' => 'Sample Medical Center', 'type' => 'hospital', 'latitude' => 8.955000, 'longitude' => 125.528000],
            ['name' => 'Sample City Blood Bank', 'type' => 'blood_bank', 'latitude' => 8.940000, 'longitude' => 125.550000],
        ])->map(fn (array $hospital) => Hospital::create($hospital + [
            'address' => 'Sample address (replace with real data)',
            'city' => 'Butuan City',
        ]));

        $donors = Donor::factory()->count(30)->create();
        Donor::where('verification_status', 'verified')->update(['verified_by' => $admin->id, 'verified_at' => now()]);

        foreach ($donors as $donor) {
            if (fake()->boolean(60)) {
                Donation::create([
                    'donor_id' => $donor->id,
                    'donated_on' => fake()->dateTimeBetween('-2 years', '-1 month')->format('Y-m-d'),
                    'is_self_reported' => true,
                ]);
            }
        }

        $requesters = User::factory()->count(5)->create(['role' => 'requester']);

        foreach ($requesters as $index => $requester) {
            $hospital = $hospitals[$index % $hospitals->count()];
            $request = BloodRequest::factory()->create([
                'requester_id' => $requester->id,
                'hospital_id' => $hospital->id,
                'blood_type' => $donors->random()->blood_type,
            ]);

            RequestHistory::create([
                'blood_request_id' => $request->id,
                'changed_by' => $requester->id,
                'old_status' => null,
                'new_status' => 'open',
                'remarks' => 'Request submitted',
            ]);

            // Sample matches and their alerts come from the real matching and notification rules (M2.3, M2.5).
            $request->matchDonors();
        }

        $this->demoAccounts($admin);

        // Online the demo accounts get a private password (config lifeline.seed_password, from SEED_PASSWORD).
        if (config('lifeline.seed_password') !== 'password') {
            User::query()->update(['password' => Hash::make(config('lifeline.seed_password'))]);
        }
    }

    /**
     * Fixed accounts for the demo script (docs/testing/demo-script.md), added last so the sample
     * requests above do not match them. Password: "password", like every sample user.
     */
    private function demoAccounts(User $admin): void
    {
        User::factory()->create([
            'role' => 'requester',
            'first_name' => 'Demo',
            'last_name' => 'Requester',
            'email' => 'requester@lifeline.test',
            'mobile_number' => '09170000001',
        ]);

        // Verified, available O- donor (can give to every type) close to Sample General Hospital, no recent donation.
        $donor = Donor::factory()->for(User::factory()->state([
            'role' => 'donor',
            'first_name' => 'Demo',
            'last_name' => 'Donor',
            'email' => 'donor@lifeline.test',
            'mobile_number' => '09170000002',
        ]))->create([
            'blood_type' => 'O-',
            'approx_latitude' => 8.95,
            'approx_longitude' => 125.54,
            'is_available' => true,
            'location_sharing' => true,
            'verification_status' => 'verified',
        ]);
        $donor->forceFill(['verified_by' => $admin->id, 'verified_at' => now()])->save();

        // Waits in the admin verification queue.
        Donor::factory()->for(User::factory()->state([
            'role' => 'donor',
            'first_name' => 'Pending',
            'last_name' => 'Donor',
            'email' => 'pending@lifeline.test',
            'mobile_number' => '09170000003',
        ]))->create(['blood_type' => 'A+', 'verification_status' => 'pending']);
    }
}
