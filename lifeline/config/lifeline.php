<?php

// LifeLine rules that the team may tune. Eligibility numbers must trace to a cited source (DEC-008).
return [

    // Design B2: the last donation "must be 3+ months ago to be eligible".
    // Source: Philippine Red Cross, "Give blood" (https://redcross.org.ph/give-blood/, read 2026-11-17):
    // "A healthy individual may donate every three months." Source S05 in docs/thesis/source-matrix.md.
    'donation_interval_months' => 3,

    // How many of the nearest eligible donors one matching run adds (DEC-016).
    'match_limit' => 10,

    // Donor locations must fall inside this box (the Philippines), rounded to 2 decimals (~1 km, DEC-004).
    'location_bounds' => [
        'latitude' => [4.5, 21.5],
        'longitude' => [116.0, 127.0],
    ],

    // Password for the sample and demo accounts made by the seeder. Locally it is "password"; online,
    // set SEED_PASSWORD so strangers cannot log in as the demo admin (docs/deployment.md).
    'seed_password' => env('SEED_PASSWORD', 'password'),

];
