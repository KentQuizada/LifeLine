<?php

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UsersAndHospitalsTest extends TestCase
{
    use InsertsRows;
    use RefreshDatabase;

    public function test_duplicate_email_is_rejected(): void
    {
        $this->insertUser(['email' => 'juan@example.test']);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->insertUser(['email' => 'juan@example.test']);
    }

    public function test_duplicate_mobile_number_is_rejected(): void
    {
        $this->insertUser(['mobile_number' => '09171234567']);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->insertUser(['mobile_number' => '09171234567']);
    }

    public function test_invalid_role_is_rejected(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage("column 'role'");
        $this->insertUser(['role' => 'superuser']);
    }

    public function test_user_has_at_most_one_administrator_profile(): void
    {
        $userId = $this->insertUser(['role' => 'admin']);
        DB::table('administrators')->insert(['user_id' => $userId, 'position' => 'Admin']);

        $this->expectException(UniqueConstraintViolationException::class);
        DB::table('administrators')->insert(['user_id' => $userId, 'position' => 'Admin']);
    }

    public function test_administrator_requires_existing_user(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('foreign key constraint fails');
        DB::table('administrators')->insert(['user_id' => 999999, 'position' => 'Admin']);
    }

    public function test_duplicate_hospital_name_is_rejected(): void
    {
        $this->insertHospital(['name' => 'Sample General Hospital']);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->insertHospital(['name' => 'Sample General Hospital']);
    }

    public function test_invalid_hospital_type_is_rejected(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage("column 'type'");
        $this->insertHospital(['type' => 'clinic']);
    }
}
