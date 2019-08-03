<?php

namespace Cmixin;

use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateInterval;
use Generator;
use RuntimeException;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

trait EnhancedPeriod
{
    private static $maskUnits = [
        'year'   => Precision::YEAR,
        'month'  => Precision::MONTH,
        'day'    => Precision::DAY,
        'hour'   => Precision::HOUR,
        'minute' => Precision::MINUTE,
        'second' => Precision::SECOND,
    ];

    public function toEnhancedPeriod(): Period
    {
        $mask = static::convertDateIntervalToPrecisionMask($this->getDateInterval());
        /** @var CarbonPeriod $period */
        $period = $this->copy()->floor();
        $end = $period->calculateEnd();

        return new Period(
            $period->getStartDate()->toImmutable(),
            $end ? $end->toImmutable() : null,
            $mask,
            ($period->isStartExcluded() ? Boundaries::EXCLUDE_START : 0) |
            ($period->isEndExcluded() ? Boundaries::EXCLUDE_END : 0)
        );
    }

    public static function fromEnhancedPeriod(Period $period)
    {
        return new static(
            $period->getStart(),
            $period->getEnd(),
            static::convertPrecisionMaskToDateInterval($period->getPrecisionMask()),
            static::IMMUTABLE |
            ($period->startExcluded() ? static::EXCLUDE_START_DATE : 0) |
            ($period->endExcluded() ? static::EXCLUDE_END_DATE : 0)
        );
    }

    private static function getIntervalUnits(DateInterval $interval): Generator
    {
        $intervals = [];
        [
            $intervals[Precision::YEAR],
            $intervals[Precision::MONTH],
            $intervals[Precision::DAY],
            $intervals[Precision::HOUR],
            $intervals[Precision::MINUTE],
            $intervals[Precision::SECOND],
        ] = array_map('intval', explode(' ', CarbonInterval::instance($interval)
            ->format('%y %m %d %h %i %s')));
        $mask = null;

        foreach ($intervals as $unit => $quantity) {
            if ($quantity !== 0) {
                yield $unit => $quantity;
            }
        }
    }

    public static function convertDateIntervalToPrecisionMask(DateInterval $interval): int
    {
        $mask = null;

        foreach (self::getIntervalUnits($interval) as $unit => $quantity) {
            if ($quantity !== 1 || $mask) {
                throw new RuntimeException(
                    'Only periods with 1 year, 1 month, 1 day, 1 hour, 1 minute or 1 second interval can be'.
                    ' converted to '.Period::class
                );
            }

            $mask = $unit;
        }

        return $mask ?: Precision::DAY;
    }

    public static function convertPrecisionMaskToDateInterval(int $precisionMask): CarbonInterval
    {
        foreach (self::$maskUnits as $method => $mask) {
            if ($precisionMask === $mask) {
                return CarbonInterval::$method();
            }
        }

        return CarbonInterval::day();
    }
}
