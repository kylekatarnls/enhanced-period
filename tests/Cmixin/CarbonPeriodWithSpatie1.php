<?php

namespace Tests\Cmixin;

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;

class CarbonPeriodWithSpatie1 extends CarbonPeriod
{
    use EnhancedPeriod;

    protected function isPeriodCallableMethod($method)
    {
        return $method !== 'duration';
    }
}
