<?php

// LifeLine rules that the team may tune. Eligibility numbers must trace to a cited source (DEC-008).
return [

    // Design B2: the last donation "must be 3+ months ago to be eligible".
    // TODO(team): confirm against a Philippine Red Cross or DOH source before the defense.
    'donation_interval_months' => 3,

    // How many of the nearest eligible donors one matching run adds (DEC-016).
    'match_limit' => 10,

];
