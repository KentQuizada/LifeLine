<?php

namespace Tests\Unit;

use App\Support\Distance;
use PHPUnit\Framework\TestCase;

class DistanceTest extends TestCase
{
    public function test_the_same_point_is_zero_km_away(): void
    {
        $this->assertSame(0.0, Distance::km(8.95, 125.54, 8.95, 125.54));
    }

    public function test_one_degree_of_longitude_on_the_equator_is_about_111_km(): void
    {
        // 2 * pi * 6371 km / 360 = 111.19 km.
        $this->assertEqualsWithDelta(111.19, Distance::km(0, 0, 0, 1), 0.01);
    }

    public function test_distance_is_the_same_in_both_directions(): void
    {
        $this->assertEqualsWithDelta(
            Distance::km(8.9475, 125.5406, 8.99, 125.6),
            Distance::km(8.99, 125.6, 8.9475, 125.5406),
            0.000001
        );
    }
}
