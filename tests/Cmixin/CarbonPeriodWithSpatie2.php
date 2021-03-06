<?php

namespace Tests\Cmixin;

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use EnhancedPeriod\Enum\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodDuration;

class PeriodWithDuration extends Period
{
    public function duration(): PeriodDuration
    {
        if (!class_exists(PeriodDuration::class)) {
            include_once __DIR__.'/PeriodDuration.php';

            return new PeriodDuration();
        }

        return parent::duration();
    }
}

class CarbonPeriodWithSpatie2 extends CarbonPeriod
{
    use EnhancedPeriod;

    protected function isPeriodCallableMethod($method)
    {
        return $method === 'duration';
    }

    public function toEnhancedPeriod()
    {
        $mask = static::convertDateIntervalToPrecision($this->getDateInterval());
        /** @var CarbonPeriod $period */
        $period = $this->copy()->floor();
        $end = $period->calculateEnd();

        return new PeriodWithDuration(
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
