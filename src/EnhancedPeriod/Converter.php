<?php

declare(strict_types=1);

namespace EnhancedPeriod;

use BadMethodCallException;
use Carbon\CarbonInterval;
use DateInterval;
use Generator;
use RuntimeException;
use Spatie\Period\Period;
use Spatie\Period\Precision;

final class Converter
{
    private static $maskUnits = [
        'year'   => 0b100000,
        'month'  => 0b110000,
        'day'    => 0b111000,
        'hour'   => 0b111100,
        'minute' => 0b111110,
        'second' => 0b111111,
    ];

    private static $unitCode = [
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    /**
     * Convert spatie/period precision mask into DateInterval.
     *
     * @param Precision|string|int $precision
     *
     * @return CarbonInterval
     */
    public function convertPrecisionToDateInterval($precision): CarbonInterval
    {
        if (is_object($precision) && method_exists($precision, 'intervalName')) {
            $precision = $precision->intervalName();
        }

        if (is_string($precision)) {
            if (isset(self::$unitCode[$precision])) {
                $precision = self::$unitCode[$precision];
            }

            try {
                return CarbonInterval::$precision();
            } catch (BadMethodCallException $exception) {
                // try int mask
            }
        }

        return $this->convertPrecisionMaskToDateInterval((int) $precision);
    }

    /**
     * Convert spatie/period precision mask into DateInterval.
     *
     * @param int $precisionMask
     *
     * @return CarbonInterval
     */
    public function convertPrecisionMaskToDateInterval(int $precisionMask): CarbonInterval
    {
        foreach (self::$maskUnits as $method => $mask) {
            if ($precisionMask === $mask) {
                return CarbonInterval::$method();
            }
        }

        return CarbonInterval::day();
    }

    /**
     * Convert DateInterval into spatie/period precision mask.
     *
     * @param DateInterval $interval
     *
     * @return string
     */
    public static function convertDateIntervalToUnit(DateInterval $interval): string
    {
        $maskUnit = null;

        foreach (self::getIntervalUnits($interval) as $unit => $quantity) {
            if ($quantity !== 1 || $maskUnit) {
                throw new RuntimeException(
                    'Only periods with 1 year, 1 month, 1 day, 1 hour, 1 minute or 1 second interval can be'.
                    ' converted to '.Period::class
                );
            }

            $maskUnit = $unit;
        }

        return $maskUnit ?? 'day';
    }

    /**
     * Convert DateInterval into spatie/period precision mask.
     *
     * @param DateInterval $interval
     *
     * @return int
     */
    public static function convertDateIntervalToPrecisionMask(DateInterval $interval): int
    {
        return self::$maskUnits[static::convertDateIntervalToUnit($interval)];
    }

    /**
     * Convert DateInterval into spatie/period precision mask.
     *
     * @param DateInterval $interval
     *
     * @return int|Precision
     */
    public static function convertDateIntervalToPrecision(DateInterval $interval)
    {
        return static::convertUnitToPrecision(static::convertDateIntervalToUnit($interval));
    }

    /**
     * Convert unit string into spatie/period precision mask.
     *
     * @param string $maskUnit
     *
     * @return int|Precision
     */
    private static function convertUnitToPrecision(string $maskUnit)
    {
        // @codeCoverageIgnoreStart
        if (class_exists(Precision::class)) {
            return call_user_func([Precision::class, strtoupper($maskUnit)]);
        }

        return self::$maskUnits[$maskUnit];
        // @codeCoverageIgnoreEnd
    }

    private static function getIntervalUnits(DateInterval $interval): Generator
    {
        $intervals = array_combine(
            array_keys(self::$maskUnits),
            array_map('intval', explode(' ', CarbonInterval::instance($interval)
                ->format('%y %m %d %h %i %s')))
        );

        foreach ($intervals as $unit => $quantity) {
            if ($quantity !== 0) {
                yield $unit => $quantity;
            }
        }
    }
}
