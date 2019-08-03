# enhanced-period

[Carbon](https://carbon.nesbot.com/) mixin to convert `Carbon\CarbonPeriod` to `Spatie\Period\Period` and vice versa.

[![Latest Stable Version](https://poser.pugx.org/cmixin/enhanced-period/v/stable.png)](https://packagist.org/packages/cmixin/enhanced-period)
[![Build Status](https://travis-ci.org/kylekatarnls/enhanced-period.svg?branch=master)](https://travis-ci.org/kylekatarnls/enhanced-period)
[![Code Climate](https://codeclimate.com/github/kylekatarnls/enhanced-period/badges/gpa.svg)](https://codeclimate.com/github/kylekatarnls/enhanced-period)
[![Test Coverage](https://codeclimate.com/github/kylekatarnls/enhanced-period/badges/coverage.svg)](https://codeclimate.com/github/kylekatarnls/enhanced-period/coverage)
[![Issue Count](https://codeclimate.com/github/kylekatarnls/enhanced-period/badges/issue_count.svg)](https://codeclimate.com/github/kylekatarnls/enhanced-period)
[![StyleCI](https://styleci.io/repos/200379769/shield?branch=master&style=flat)](https://styleci.io/repos/200379769)

[Professionally supported nesbot/carbon is now available](https://tidelift.com/subscription/pkg/packagist-nesbot-carbon?utm_source=packagist-nesbot-carbon&utm_medium=referral&utm_campaign=readme)

## Install

```shell
composer require cmixin/enhanced-period
```

## Usage

```php
<?php

use Carbon\CarbonPeriod;
use Cmixin\EnhancedPeriod;

CarbonPeriod::mixin(EnhancedPeriod::class); // This line is not needed if you use Laravel default auto-discovery.

// Use toEnhancedPeriod to convert `Carbon\CarbonPeriod` objects to `Spatie\Period\Period` ones
$period1 = CarbonPeriod::hours()
    ->since('2019-09-01 08:00')
    ->until('2019-09-01 15:00')
    ->toEnhancedPeriod();

$period2 = CarbonPeriod::hours()
    ->since('2019-09-01 10:00')
    ->until('2019-09-01 18:00')
    ->toEnhancedPeriod();

foreach ($period1->overlap($period2) as $period) {
    // Use fromEnhancedPeriod to convert `Spatie\Period\Period` objects to `Carbon\CarbonPeriod` ones
    echo CarbonPeriod::fromEnhancedPeriod($period);
}
```

Output:
```
Every 1 hour from 2019-09-01 10:00:00 to 2019-09-01 15:00:00
```

See methods available on `Spatie\Period\Period` class here:
https://github.com/spatie/period
