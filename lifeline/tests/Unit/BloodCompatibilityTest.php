<?php

namespace Tests\Unit;

use App\Models\BloodRequest;
use App\Models\Donor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Red-cell (whole blood) ABO/Rh compatibility, all 8 x 8 pairs (M2.3 completion criterion).
 * The expected grid is written out independently of the table in BloodRequest.
 */
class BloodCompatibilityTest extends TestCase
{
    private const DONOR_ORDER = ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'];

    // Row = recipient, column = donor in DONOR_ORDER. 1 = the recipient may receive red cells from that donor.
    private const GRID = [
        'O-' => [1, 0, 0, 0, 0, 0, 0, 0],
        'O+' => [1, 1, 0, 0, 0, 0, 0, 0],
        'A-' => [1, 0, 1, 0, 0, 0, 0, 0],
        'A+' => [1, 1, 1, 1, 0, 0, 0, 0],
        'B-' => [1, 0, 0, 0, 1, 0, 0, 0],
        'B+' => [1, 1, 0, 0, 1, 1, 0, 0],
        'AB-' => [1, 0, 1, 0, 1, 0, 1, 0],
        'AB+' => [1, 1, 1, 1, 1, 1, 1, 1],
    ];

    public static function allPairs(): array
    {
        $pairs = [];

        foreach (self::GRID as $recipient => $row) {
            foreach (self::DONOR_ORDER as $column => $donor) {
                $pairs["{$recipient} receives from {$donor}"] = [$recipient, $donor, $row[$column] === 1];
            }
        }

        return $pairs;
    }

    #[DataProvider('allPairs')]
    public function test_compatibility(string $recipient, string $donor, bool $compatible): void
    {
        $this->assertSame($compatible, in_array($donor, BloodRequest::COMPATIBLE_DONORS[$recipient], true));
    }

    public function test_the_table_covers_exactly_the_eight_blood_types(): void
    {
        $this->assertEqualsCanonicalizing(Donor::BLOOD_TYPES, array_keys(BloodRequest::COMPATIBLE_DONORS));

        foreach (BloodRequest::COMPATIBLE_DONORS as $donorTypes) {
            $this->assertSame([], array_diff($donorTypes, Donor::BLOOD_TYPES));
        }
    }
}
