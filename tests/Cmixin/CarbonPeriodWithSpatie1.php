<?php

namespace Tests\Cmixin;

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use InvalidArgumentException;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;

class PeriodWithError extends Period
{
    public function overlapAll(Period ...$periods): Period
    {
        if (count($periods) === 1) {
            throw new InvalidArgumentException('Fake error');
        }

        $a = null;

        return $a->overlapSingle();
    }
}

class CarbonPeriodWithSpatie1 extends CarbonPeriod
{
    use EnhancedPeriod;

    protected function isPeriodCallableMethod($method)
    {
        return $method !== 'duration';
    }

    public function toEnhancedPeriod()
    {
        $mask = static::convertDateIntervalToPrecisionMask($this->getDateInterval());
        /** @var CarbonPeriod $period */
        $period = $this->copy()->floor();
        $end = $period->calculateEnd();

        return new PeriodWithError(
            $period->getStartDate()->toImmutable(),
            $end ? $end->toImmutable() : null,
            $mask,
            ($period->isStartExcluded() ? Boundaries::EXCLUDE_START : 0) |
            ($period->isEndExcluded() ? Boundaries::EXCLUDE_END : 0)
        );
    }
}
