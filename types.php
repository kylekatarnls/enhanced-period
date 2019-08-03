<?php

include __DIR__.'/vendor/autoload.php';
include __DIR__.'/vendor/cmixin/business-day/src/Types/Generator.php';

$generator = new \Types\Generator();
$generator->writeHelpers(\Cmixin\EnhancedPeriod::class, __DIR__.'/src', __DIR__.'/types', '_ide_business_time', function () {
    \Cmixin\EnhancedPeriod::enable(\Carbon\Carbon::class);
});
