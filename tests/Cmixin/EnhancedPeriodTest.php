<?php

namespace Tests\Cmixin;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class EnhancedPeriodTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        CarbonPeriod::mixin(EnhancedPeriod::class);
        Carbon::mixin(EnhancedPeriod::class);
    }

    public function testReadmeExample()
    {
        $period1 = CarbonPeriod::hours()
            ->since('2019-09-01 08:00')
            ->until('2019-09-01 15:00')
            ->toEnhancedPeriod();

        $period2 = CarbonPeriod::hours()
            ->since('2019-09-01 10:00')
            ->until('2019-09-01 18:00')
            ->toEnhancedPeriod();

        $output = [];

        foreach ($period1->overlap($period2) as $period) {
            $output[] = (string) CarbonPeriod::fromEnhancedPeriod($period);
        }

        $this->assertSame(['Every 1 hour from 2019-09-01 10:00:00 to 2019-09-01 15:00:00'], $output);
    }

    public function testToEnhancedPeriodException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only periods with 1 year, 1 month, 1 day, 1 hour, 1 minute or 1 second '.
            'interval can be converted to Spatie\Period\Period');

        CarbonPeriod::hours(2)->minutes(40)->toEnhancedPeriod();
    }

    public function testToEnhancedPeriod()
    {
        $period = CarbonPeriod::hours()
            ->since('2019-09-01 08:02')
            ->until('2019-09-01 15:03')
            ->toEnhancedPeriod();

        $this->assertInstanceOf(Period::class, $period);
        $this->assertSame(Precision::HOUR, $period->getPrecisionMask());
        $this->assertSame('2019-09-01 08:00', $period->getStart()->format('Y-m-d H:i'));
        $this->assertSame('2019-09-01 15:00', $period->getEnd()->format('Y-m-d H:i'));
        $this->assertTrue($period->startIncluded());
        $this->assertTrue($period->endIncluded());

        $period = CarbonPeriod::hours()
            ->since('2019-09-01 08:02')
            ->until('2019-09-01 15:03')
            ->excludeStartDate()
            ->toEnhancedPeriod();

        $this->assertFalse($period->startIncluded());
        $this->assertTrue($period->endIncluded());

        $period = CarbonPeriod::hours()
            ->since('2019-09-01 08:02')
            ->until('2019-09-01 15:03')
            ->excludeEndDate()
            ->toEnhancedPeriod();

        $this->assertTrue($period->startIncluded());
        $this->assertFalse($period->endIncluded());

        $period = CarbonPeriod::hours()
            ->since('2019-09-01 08:02')
            ->until('2019-09-01 15:03')
            ->excludeStartDate()
            ->excludeEndDate()
            ->toEnhancedPeriod();

        $this->assertFalse($period->startIncluded());
        $this->assertFalse($period->endIncluded());
    }

    public function testFromEnhancedPeriod()
    {
        $period = Period::make('2019-09-01', '2019-09-12');
        $period = CarbonPeriod::fromEnhancedPeriod($period);

        $this->assertSame('2019-09-01 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2019-09-12 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertSame('00-00-01 00:00:00', $period->getDateInterval()->format('%Y-%M-%D %H:%I:%S'));
    }

    public function getMaskAndIntervalParis()
    {
        return [
            [
                Precision::YEAR,
                CarbonInterval::year(),
            ],
            [
                Precision::MONTH,
                CarbonInterval::month(),
            ],
            [
                Precision::DAY,
                CarbonInterval::day(),
            ],
            [
                Precision::HOUR,
                CarbonInterval::hour(),
            ],
            [
                Precision::MINUTE,
                CarbonInterval::minute(),
            ],
            [
                Precision::SECOND,
                CarbonInterval::second(),
            ],
        ];
    }

    /**
     * @dataProvider getMaskAndIntervalParis
     */
    public function testConvertPrecisionMaskToDateInterval(int $mask, CarbonInterval $interval)
    {
        $this->assertSame($interval->totalSeconds, CarbonPeriod::convertPrecisionMaskToDateInterval($mask)->totalSeconds);
    }

    /**
     * @dataProvider getMaskAndIntervalParis
     */
    public function testConvertDateIntervalToPrecisionMask(int $mask, CarbonInterval $interval)
    {
        $this->assertSame($mask, CarbonPeriod::convertDateIntervalToPrecisionMask($interval));
    }

    public function testDefaultConversions()
    {
        $this->assertSame(24 * 3600, CarbonPeriod::convertPrecisionMaskToDateInterval(0b101)->totalSeconds);
        $now = Carbon::now();
        $this->assertSame(Precision::DAY, CarbonPeriod::convertDateIntervalToPrecisionMask($now->diff($now)));
    }

    public function testLength()
    {
        $this->assertSame(
            8,
            CarbonPeriod::hours()
                ->since('2019-09-01 08:02')
                ->until('2019-09-01 15:03')
                ->length()
        );
    }

    public function testOverlapsWith()
    {
        $this->assertTrue(
            CarbonPeriod::hours()
                ->since('2019-09-01 08:02')
                ->until('2019-09-01 15:03')
                ->overlapsWith(
                    CarbonPeriod::hours()
                        ->since('2019-09-01 15:10')
                        ->until('2019-09-01 18:03')
                )
        );

        $this->assertTrue(
            CarbonPeriod::hours()
                ->since('2019-09-01 08:02')
                ->until('2019-09-01 15:03')
                ->overlapsWith(
                    '2019-09-01 15:10', '2019-09-01 18:03', '1 hour'
                )
        );

        $this->assertTrue(
            CarbonPeriod::hours()
                ->since('2019-09-01 08:02')
                ->until('2019-09-01 15:03')
                ->overlapsWith(
                    Period::make('2019-09-01 15:10:00', '2019-09-01 18:03:00', Precision::HOUR)
                )
        );

        $this->assertTrue(
            CarbonPeriod::hours()
                ->since('2019-09-01 08:02')
                ->until('2019-09-01 15:03')
                ->overlapsWith(
                    new DatePeriod(
                        new DateTimeImmutable('2019-09-01 15:10:00'),
                        new DateInterval('PT1H'),
                        new DateTimeImmutable('2019-09-01 18:03:00')
                    )
                )
        );
    }

    public function testTouchesWith()
    {
        $this->assertTrue(
            CarbonPeriod::hours()
                ->since('2019-09-01 08:02')
                ->until('2019-09-01 15:03')
                ->touchesWith(
                    CarbonPeriod::hours()
                        ->since('2019-09-01 16:00')
                        ->until('2019-09-01 18:03')
                )
        );

        $this->assertTrue(
            CarbonPeriod::hours()
                ->since('2019-09-01 08:02')
                ->until('2019-09-01 15:03')
                ->touchesWith(
                    '2019-09-01 16:00', '2019-09-01 18:03', '1 hour'
                )
        );

        $this->assertTrue(
            CarbonPeriod::hours()
                ->since('2019-09-01 08:02')
                ->until('2019-09-01 15:03')
                ->touchesWith(
                    Period::make('2019-09-01 16:00:00', '2019-09-01 18:03:00', Precision::HOUR)
                )
        );

        $this->assertTrue(
            CarbonPeriod::hours()
                ->since('2019-09-01 08:02')
                ->until('2019-09-01 15:03')
                ->touchesWith(
                    new DatePeriod(
                        new DateTimeImmutable('2019-09-01 16:00:00'),
                        new DateInterval('PT1H'),
                        new DateTimeImmutable('2019-09-01 18:03:00')
                    )
                )
        );
    }
}
