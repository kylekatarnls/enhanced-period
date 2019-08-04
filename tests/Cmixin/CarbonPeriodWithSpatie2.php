<?php

namespace Tests\Cmixin;

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodDuration;

class PeriodWithDuration extends Period
{
    public function duration(): PeriodDuration
    {
        if (!class_exists(PeriodDuration::class)) {
            include_once __DIR__.'/PeriodDuration.php';
        }

        return parent::duration();
    }
}

class CarbonPeriodWithSpatie2 extends CarbonPeriod
{
    use EnhancedPeriod;

    protected function toEnhancedPeriod()
    {
        $mask = static::convertDateIntervalToPrecisionMask($this->getDateInterval());
        /** @var CarbonPeriod $period */
        $period = $this->copy()->floor();
        $end = $period->calculateEnd();

        return new PeriodWithDuration(
            $period->getStartDate()->toImmutable(),
            $end ? $end->toImmutable() : null,
            $mask,
            ($period->isStartExcluded() ? Boundaries::EXCLUDE_START : 0) |
            ($period->isEndExcluded() ? Boundaries::EXCLUDE_END : 0)
        );
    }
}
