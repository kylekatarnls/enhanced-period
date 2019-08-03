<?php

namespace EnhancedPeriod\Laravel;

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;

/**
 * @property \App $app
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        CarbonPeriod::mixin(EnhancedPeriod::class);
    }

    public function register()
    {
        // Needed for Laravel < 5.3 compatibility
    }
}
