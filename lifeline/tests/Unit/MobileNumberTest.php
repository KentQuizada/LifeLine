<?php

namespace Tests\Unit;

use App\Support\MobileNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MobileNumberTest extends TestCase
{
    public static function validNumbers(): array
    {
        return [
            'local' => ['09171234567'],
            'international' => ['+639171234567'],
            'international without plus' => ['639171234567'],
            'without leading zero' => ['9171234567'],
            'with spaces and dashes' => ['0917 123-4567'],
            'with brackets' => ['(0917) 123 4567'],
        ];
    }

    #[DataProvider('validNumbers')]
    public function test_valid_numbers_normalize_to_local_format(string $input): void
    {
        $this->assertSame('09171234567', MobileNumber::normalize($input));
    }

    public static function invalidNumbers(): array
    {
        return [
            'empty' => [''],
            'too short' => ['0917123456'],
            'too long' => ['091712345678'],
            'landline' => ['0851234567'],
            'letters' => ['0917abc4567'],
            'foreign' => ['+15551234567'],
        ];
    }

    #[DataProvider('invalidNumbers')]
    public function test_invalid_numbers_return_null(string $input): void
    {
        $this->assertNull(MobileNumber::normalize($input));
    }

    public function test_null_returns_null(): void
    {
        $this->assertNull(MobileNumber::normalize(null));
    }
}
