<?php

namespace Cmixin;

use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateInterval;
use RuntimeException;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

trait EnhancedPeriod
{
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

    public static function convertDateIntervalToPrecisionMask(DateInterval $interval): int
    {
        $intervals = [];
        [
            $intervals[Precision::YEAR],
            $intervals[Precision::MONTH],
            $intervals[Precision::DAY],
            $intervals[Precision::HOUR],
            $intervals[Precision::MINUTE],
            $intervals[Precision::SECOND],
        ] = explode(' ', CarbonInterval::instance($interval)->format('%y %m %d %h %i %s'));
        $mask = null;

        foreach ($intervals as $unit => $quantity) {
            if ($quantity === '0') {
                continue;
            }

            if ($quantity !== '1' || $mask) {
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
        switch ($precisionMask) {
            case Precision::YEAR:
                return CarbonInterval::year();
            case Precision::MONTH:
                return CarbonInterval::month();
            case Precision::DAY:
                return CarbonInterval::day();
            case Precision::HOUR:
                return CarbonInterval::hour();
            case Precision::MINUTE:
                return CarbonInterval::minute();
            case Precision::SECOND:
                return CarbonInterval::second();
        }

        return CarbonInterval::day();
    }
}
