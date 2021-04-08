<?php

namespace Tests\Carbon\Enum;

use Spatie\Period\Precision as SpatiePrecision;
use EnhancedPeriod\Enum\Precision;
use PHPUnit\Framework\TestCase;

class PrecisionTest extends TestCase
{
    public function testEquals(): void
    {
        $year = Precision::YEAR();

        $this->assertTrue($year->equals($year));
        $this->assertTrue($year->equals(Precision::YEAR()));
        $this->assertTrue($year->equals(Precision::YEAR()->value()));
        $this->assertFalse($year->equals(Precision::MONTH()));
        $this->assertFalse($year->equals(Precision::DAY()->value()));
        $this->assertFalse($year->equals(-1));
    }

    public function testPrecisionInteger(): void
    {
        if (class_exists(SpatiePrecision::class)) {
            $this->markTestSkipped('Test for spatie/period 1');
        }

        $this->assertSame(0b100000, Precision::YEAR()->value());
        $this->assertSame(0b110000, Precision::MONTH()->value());
        $this->assertSame(0b111000, Precision::DAY()->value());
        $this->assertSame(0b111100, Precision::HOUR()->value());
        $this->assertSame(0b111110, Precision::MINUTE()->value());
        $this->assertSame(0b111111, Precision::SECOND()->value());
    }

    public function testPrecisionClass(): void
    {
        if (interface_exists(SpatiePrecision::class)) {
            $this->markTestSkipped('Test for spatie/period 2');
        }

        $this->assertEquals(SpatiePrecision::YEAR(), Precision::YEAR()->value());
        $this->assertEquals(SpatiePrecision::MONTH(), Precision::MONTH()->value());
        $this->assertEquals(SpatiePrecision::DAY(), Precision::DAY()->value());
        $this->assertEquals(SpatiePrecision::HOUR(), Precision::HOUR()->value());
        $this->assertEquals(SpatiePrecision::MINUTE(), Precision::MINUTE()->value());
        $this->assertEquals(SpatiePrecision::SECOND(), Precision::SECOND()->value());
    }
}
