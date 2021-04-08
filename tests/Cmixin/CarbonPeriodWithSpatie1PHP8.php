<?php

namespace Tests\Cmixin;

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use EnhancedPeriod\Enum\Boundaries;
use InvalidArgumentException;
use Spatie\Period\Period;

class PeriodWithError extends Period
{
    public function overlapAll(Period ...$periods): ?static
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
        $mask = static::convertDateIntervalToPrecision($this->getDateInterval());
        /** @var CarbonPeriod $period */
        $period = $this->copy()->floor();
        $end = $period->calculateEnd();

        return new PeriodWithError(
            $period->getStartDate()->toImmutable(),
            $end ? $end->toImmutable() : null,
            $mask,
            $this->getBoundaries($period)
        );
    }

    private function getBoundaries(CarbonPeriod $period)
    {
        $startExcluded = $period->isStartExcluded();
        $endExcluded = $period->isEndExcluded();

        if ($startExcluded && $endExcluded) {
            return Boundaries::EXCLUDE_ALL()->value();
        }

        if ($startExcluded) {
            return Boundaries::EXCLUDE_START()->value();
        }

        if ($endExcluded) {
            return Boundaries::EXCLUDE_END()->value();
        }

        return Boundaries::EXCLUDE_NONE()->value();
    }
}
