<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

if (class_exists(\Spatie\Period\Precision::class)) {
    require __DIR__.'/Cmixin/CarbonPeriodWithSpatie1PHP8.php';
}
