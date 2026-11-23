<?php

namespace Tests\Unit;

use App\Support\EmailTypo;
use PHPUnit\Framework\TestCase;

class EmailTypoTest extends TestCase
{
    public function test_common_provider_typos_get_a_suggestion(): void
    {
        $this->assertSame('juan@gmail.com', EmailTypo::suggestion('juan@gmail.con'));
        $this->assertSame('juan@gmail.com', EmailTypo::suggestion('juan@gmial.com'));
        $this->assertSame('juan@gmail.com', EmailTypo::suggestion('juan@GMAIL.CON'));
        $this->assertSame('juan@yahoo.com', EmailTypo::suggestion('juan@yahoo.con'));
        $this->assertSame('juan@outlook.com', EmailTypo::suggestion('juan@outlok.com'));
    }

    public function test_any_dot_con_becomes_dot_com(): void
    {
        $this->assertSame('juan@carsu.com', EmailTypo::suggestion('juan@carsu.con'));
    }

    public function test_real_addresses_pass(): void
    {
        $this->assertNull(EmailTypo::suggestion('juan@gmail.com'));
        $this->assertNull(EmailTypo::suggestion('juan@carsu.edu.ph'));
        $this->assertNull(EmailTypo::suggestion('juan@yahoo.com.ph'));
        $this->assertNull(EmailTypo::suggestion('juan@lifeline.test'));
        $this->assertNull(EmailTypo::suggestion('no-at-sign'));
    }
}
