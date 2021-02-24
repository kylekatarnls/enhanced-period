<?php

namespace Cmixin;

use BadMethodCallException;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateInterval;
use DatePeriod;
use Generator;
use RuntimeException;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\PeriodDuration;
use Spatie\Period\Precision;
use Throwable;

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

    /**
     * Convert the current CarbonPeriod instance into Spatie\Period\Period instance.
     *
     * @return Period
     */
    public function toEnhancedPeriod(): Period
    {
        $mask = static::convertDateIntervalToPrecisionMask($this->getDateInterval());
        /** @var CarbonPeriod $period */
        $period = $this->copy()->floor();
        $end = $period->calculateEnd();

        return Period::make(
            $period->getStartDate()->toImmutable(),
            $end ? $end->toImmutable() : null,
            $mask,
            ($period->isStartExcluded() ? Boundaries::EXCLUDE_START : 0) |
            ($period->isEndExcluded() ? Boundaries::EXCLUDE_END : 0)
        );
    }

    /**
     * Convert Spatie\Period\Period instance into CarbonPeriod instance.
     *
     * @param Period|PeriodCollection $period
     * @param bool                    $mutable Force dates to be mutable by passing true
     *
     * @return CarbonPeriod|EnhancedPeriod
     */
    public static function fromEnhancedPeriod($period, $mutable = false)
    {
        if ($period instanceof PeriodCollection) {
            $collection = $period;

            foreach ($collection as $period) {
                break;
            }
        }

        return new static(
            $period->getStart(),
            $period->getEnd(),
            static::convertPrecisionMaskToDateInterval($period->getPrecisionMask()),
            ($mutable ? 0 : static::IMMUTABLE) |
            ($period->startExcluded() ? static::EXCLUDE_START_DATE : 0) |
            ($period->endExcluded() ? static::EXCLUDE_END_DATE : 0)
        );
    }

    /**
     * Convert null into null and Spatie\Period\Period instance into CarbonPeriod instance.
     *
     * @param Period|PeriodCollection|null $period
     * @param bool                         $mutable Force dates to be mutable by passing true
     *
     * @return CarbonPeriod|EnhancedPeriod|null
     */
    public static function fromNullableEnhancedPeriod($period = null, $mutable = false)
    {
        return $period
            ? static::fromEnhancedPeriod($period, $mutable)
            : null;
    }

    /**
     * Convert a PeriodCollection object into an array of CarbonPeriod instances.
     *
     * @param PeriodCollection $periods
     * @param bool             $mutable Force dates to be mutable by passing true
     *
     * @return CarbonPeriod[]
     */
    public static function fromPeriodCollection(PeriodCollection $periods, $mutable = false): array
    {
        $result = [];

        foreach ($periods as $key => $period) {
            $result[$key] = static::fromEnhancedPeriod($period, $mutable);
        }

        return $result;
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

    /**
     * Convert spatie/period precision mask into DateInterval.
     *
     * @param int $precisionMask
     *
     * @return CarbonInterval
     */
    public static function convertPrecisionMaskToDateInterval(int $precisionMask): CarbonInterval
    {
        foreach (self::$maskUnits as $method => $mask) {
            if ($precisionMask === $mask) {
                return CarbonInterval::$method();
            }
        }

        return CarbonInterval::day();
    }

    /**
     * Returns the Spatie\Period\Period length.
     *
     * @return int
     */
    public function length(): int
    {
        return (int) $this->toEnhancedPeriod()->length();
    }

    /**
     * Returns true if the current period overlaps with a given other.
     *
     * @param mixed $period
     * @param mixed ...$arguments
     *
     * @return bool
     */
    public function overlapsWith($period, ...$arguments): bool
    {
        return $this->toEnhancedPeriod()->overlapsWith($this->resolveEnhancedPeriod($period, ...$arguments));
    }

    /**
     * Returns true if the current period bounds touches a given other ones.
     *
     * @param mixed $period
     * @param mixed ...$arguments
     *
     * @return bool
     */
    public function touchesWith($period, ...$arguments): bool
    {
        return $this->toEnhancedPeriod()->touchesWith($this->resolveEnhancedPeriod($period, ...$arguments));
    }

    /**
     * Returns the matching Spatie\Period\PeriodDuration instance.
     *
     * @return PeriodDuration
     */
    public function duration(): PeriodDuration
    {
        if (!$this->isPeriodCallableMethod('duration')) {
            throw new BadMethodCallException(
                'duration() method is only available since spatie/period 2.0.'
            );
        }

        return $this->toEnhancedPeriod()->duration();
    }

    /**
     * Return overlap period between current and a given other period.
     *
     * @param mixed $period
     * @param mixed ...$arguments
     *
     * @return CarbonPeriod
     */
    public function overlap($period, ...$arguments): ?CarbonPeriod
    {
        return static::fromNullableEnhancedPeriod(
            $this->callEnhancedPeriodMethods(
                $this->toEnhancedPeriod(),
                ['overlapSingle', 'overlap'],
                [$this->resolveEnhancedPeriod($period, ...$arguments)]
            ),
            !($this->getOptions() & CarbonPeriod::IMMUTABLE)
        );
    }

    /**
     * Return overlap chunks that are present in at least 2 periods.
     *
     * A       [========]
     * B                    [==]
     * C                            [=====]
     * CURRENT        [===============]
     *
     * OVERLAP        [=]   [==]    [=]
     *
     * @param mixed ...$periods
     *
     * @return CarbonPeriod[]
     */
    public function overlapAny(...$periods): array
    {
        return static::fromPeriodCollection(
            $this->toEnhancedPeriod()->overlap(
                ...$this->resolvePeriodArgumentsList($periods)
            ),
            !($this->getOptions() & CarbonPeriod::IMMUTABLE)
        );
    }

    /**
     * Return the merged overlap of all periods.
     *
     * A              [============]
     * B                   [==]
     * C                  [=======]
     *
     * OVERLAP             [==]
     *
     * @param mixed ...$periods
     *
     * @return CarbonPeriod
     */
    public function overlapAll(...$periods): ?CarbonPeriod
    {
        try {
            return static::fromNullableEnhancedPeriod(
                $this->toEnhancedPeriod()->overlapAll(
                    ...$this->resolvePeriodArgumentsList($periods)
                ),
                !($this->getOptions() & CarbonPeriod::IMMUTABLE)
            );
        } catch (Throwable $e) {
            if ($e->getMessage() !== 'Call to a member function overlapSingle() on null') {
                throw $e;
            }
        }

        return null;
    }

    /**
     * Returns the difference between the current period and an other given one.
     *
     * @param mixed $period
     * @param mixed ...$arguments
     *
     * @return CarbonPeriod[]
     */
    public function diffAny($period, ...$arguments): array
    {
        return static::fromPeriodCollection(
            $this->callEnhancedPeriodMethods(
                $this->toEnhancedPeriod(),
                ['diffAny', 'diffSingle'],
                [$this->resolveEnhancedPeriod($period, ...$arguments)]
            ),
            !($this->getOptions() & CarbonPeriod::IMMUTABLE)
        );
    }

    /**
     * Returns periods in the current ones that are not covered by periods passed as arguments.
     *
     * A                   [====]
     * B                               [========]
     * C         [=====]
     * CURRENT      [========================]
     *
     * DIFF             [=]      [====]
     *
     * @param mixed ...$periods
     *
     * @return CarbonPeriod[]
     */
    public function diff(...$periods): array
    {
        return static::fromPeriodCollection(
            $this->toEnhancedPeriod()->diff(
                ...$this->resolvePeriodArgumentsList($periods)
            ),
            !($this->getOptions() & CarbonPeriod::IMMUTABLE)
        );
    }

    /**
     * Returns the gap period between the current one and the one passed as argument.
     *
     * A       [========]
     * B                     [===========]
     *
     * GAP               [==]
     *
     * @param mixed $period
     * @param mixed ...$arguments
     *
     * @throws \Exception
     *
     * @return CarbonPeriod|null
     */
    public function gap($period, ...$arguments): ?CarbonPeriod
    {
        return static::fromNullableEnhancedPeriod(
            $this->toEnhancedPeriod()->gap($this->resolveEnhancedPeriod($period, ...$arguments)),
            !($this->getOptions() & CarbonPeriod::IMMUTABLE)
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

    private function resolveEnhancedPeriod($period, ...$arguments): Period
    {
        if ($period instanceof Period) {
            return $period;
        }

        if (!($period instanceof CarbonPeriod)) {
            /** @var CarbonPeriod $carbonPeriod */
            $period = $period instanceof DatePeriod
                ? static::instance($period)
                : static::create($period, ...$arguments);
        }

        return $period->toEnhancedPeriod();
    }

    private function isPeriodCallableMethod($method): bool
    {
        return method_exists(Period::class, $method) && is_callable([Period::class, $method]);
    }

    private function callEnhancedPeriodMethods(Period $period, array $methods, array $arguments = [])
    {
        $lastMethod = array_pop($methods);

        return $period->$lastMethod(...$arguments);
    }

    /**
     * @param array $periods
     *
     * @return Period[]
     */
    private function resolvePeriodArgumentsList(array $periods): array
    {
        return array_map(function ($period) {
            return $this->resolveEnhancedPeriod(...(is_array($period) ? $period : [$period]));
        }, $periods);
    }
}
