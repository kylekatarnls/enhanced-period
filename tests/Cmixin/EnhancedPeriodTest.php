<?php

namespace Tests\Cmixin;

use BadMethodCallException;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Spatie\Period\Period;
use Spatie\Period\PeriodDuration;
use Spatie\Period\Precision;

class EnhancedPeriodTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        CarbonPeriod::mixin(EnhancedPeriod::class);
    }

    public function testReadmeExample()
    {
        $a = CarbonPeriod::create('2018-01-01', '2018-01-10')->toEnhancedPeriod();
        $b = CarbonPeriod::create('2018-01-15', '2018-01-31')->toEnhancedPeriod();

        $output = (string) CarbonPeriod::fromEnhancedPeriod($a->gap($b));

        $this->assertSame('Every 1 day from 2018-01-11 to 2018-01-14', $output);
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

    public function testNullableEnhancedPeriod()
    {
        $period = Period::make('2019-09-01', '2019-09-12');
        $period = CarbonPeriod::fromNullableEnhancedPeriod($period);

        $this->assertSame('2019-09-01 00:00:00', $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame('2019-09-12 00:00:00', $period->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertSame('00-00-01 00:00:00', $period->getDateInterval()->format('%Y-%M-%D %H:%I:%S'));

        $this->assertNull(CarbonPeriod::fromNullableEnhancedPeriod(null));
    }

    public function testDurationException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('duration() method is only available since spatie/period 2.0.');

        CarbonPeriodWithSpatie1::create('2019-09-01', '2019-09-12')->duration();
    }

    public function testDuration()
    {
        $this->assertInstanceOf(
            PeriodDuration::class,
            CarbonPeriodWithSpatie2::create('2019-09-01', '2019-09-12')->duration()
        );
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
            CarbonPeriod::days()
                ->since('2019-09-01 08:02')
                ->until('2019-09-08 15:03')
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

    public function testOverlap()
    {
        $a = CarbonPeriod::create('2018-01-01', '2018-01-15');
        $b = CarbonPeriod::create('2018-01-10', '2018-01-30');
        $overlapPeriod = CarbonPeriod::create('2018-01-10', '2018-01-15');
        $result = $a->overlap($b);

        $this->assertSame(0, $result->getOptions());
        $this->assertTrue($result->equalTo($overlapPeriod));

        $a = CarbonPeriod::create('2018-01-01', '2018-01-15', CarbonPeriod::IMMUTABLE);
        $b = CarbonPeriod::create('2018-01-10', '2018-01-30', CarbonPeriod::IMMUTABLE);
        $overlapPeriod = CarbonPeriod::create('2018-01-10', '2018-01-15', CarbonPeriod::IMMUTABLE);
        $result = $a->overlap($b);

        $this->assertSame(CarbonPeriod::IMMUTABLE, $result->getOptions());
        $this->assertTrue($result->equalTo($overlapPeriod));
    }

    public function testOverlapAny()
    {
        $a = CarbonPeriod::create('2018-01-01', '2018-01-31');
        $b = CarbonPeriod::create('2018-02-10', '2018-02-20');
        $c = CarbonPeriod::create('2018-03-01', '2018-03-31');
        $d = CarbonPeriod::create('2018-01-20', '2018-03-10');

        $overlapPeriods = $d->overlapAny($a, $b, $c);

        $this->assertCount(3, $overlapPeriods);

        $this->assertTrue($overlapPeriods[0]->equalTo(CarbonPeriod::create('2018-01-20', '2018-01-31')));
        $this->assertTrue($overlapPeriods[1]->equalTo(CarbonPeriod::create('2018-02-10', '2018-02-20')));
        $this->assertTrue($overlapPeriods[2]->equalTo(CarbonPeriod::create('2018-03-01', '2018-03-10')));
    }

    public function testOverlapAll()
    {
        $a = CarbonPeriod::create('2018-01-01', '2018-01-31');
        $b = CarbonPeriod::create('2018-01-10', '2018-01-15');
        $c = CarbonPeriod::create('2018-01-10', '2018-01-31');

        $overlap = $a->overlapAll($b, $c);

        $this->assertTrue($overlap->equalTo(CarbonPeriod::create('2018-01-10', '2018-01-15')));

        $a = CarbonPeriod::create('2018-01-01', '2018-02-01');
        $b = CarbonPeriod::create('2018-05-10', '2018-06-01');
        $c = CarbonPeriod::create('2018-01-10', '2018-02-01');

        $overlap = $a->overlapAll($b, $c);

        $this->assertNull($overlap);
    }

    public function testOverlapAllVersion1()
    {
        $a = CarbonPeriodWithSpatie1::create('2018-01-01', '2018-02-01');
        $b = CarbonPeriodWithSpatie1::create('2018-05-10', '2018-06-01');
        $c = CarbonPeriodWithSpatie1::create('2018-01-10', '2018-02-01');

        $overlap = $a->overlapAll($b, $c);

        $this->assertNull($overlap);
    }

    public function testOverlapAllVersion1Exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fake error');

        $a = CarbonPeriodWithSpatie1::create('2018-01-01', '2018-02-01');
        $b = CarbonPeriodWithSpatie1::create('2018-05-10', '2018-06-01');

        $a->overlapAll($b);
    }

    public function testDiffAny()
    {
        $a = CarbonPeriod::create('2018-01-01', '2018-01-15');
        $b = CarbonPeriod::create('2018-01-10', '2018-01-30');

        $diffs = $a->diffAny($b);

        $this->assertTrue($diffs[0]->equalTo(CarbonPeriod::create('2018-01-01', '2018-01-09')));
        $this->assertTrue($diffs[1]->equalTo(CarbonPeriod::create('2018-01-16', '2018-01-30')));
    }

    public function testDiff()
    {
        $a = CarbonPeriod::create('2018-01-01', '2018-01-31');
        $b = CarbonPeriod::create('2018-02-10', '2018-02-20');
        $c = CarbonPeriod::create('2018-02-11', '2018-03-31');

        $current = CarbonPeriod::create('2018-01-20', '2018-03-15');

        $diff = $current->diff($a, $b, $c);

        $this->assertCount(1, $diff);

        $this->assertTrue($diff[0]->equalTo(CarbonPeriod::create('2018-02-01', '2018-02-09')));
    }

    public function testGap()
    {
        $a = CarbonPeriod::create('2018-01-01', '2018-01-10');
        $b = CarbonPeriod::create('2018-01-15', '2018-01-31');

        $gap = $a->gap($b);

        $this->assertTrue($gap->equalTo(CarbonPeriod::create('2018-01-11', '2018-01-14')));

        $a = CarbonPeriod::create('2018-01-15', '2018-01-31');
        $b = CarbonPeriod::create('2018-01-01', '2018-01-10');

        $gap = $a->gap($b);

        $this->assertTrue($gap->equalTo(CarbonPeriod::create('2018-01-11', '2018-01-14')));

        $a = CarbonPeriod::create('2018-01-15', '2018-01-31');
        $b = CarbonPeriod::create('2018-02-01', '2018-02-01');

        $gap = $a->gap($b);

        $this->assertNull($gap);

        $a = CarbonPeriod::create('2018-01-15', '2018-01-31');
        $b = CarbonPeriod::create('2018-01-28', '2018-02-01');

        $gap = $a->gap($b);

        $this->assertNull($gap);
    }
}
