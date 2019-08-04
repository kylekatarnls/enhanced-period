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
$a = CarbonPeriod::create('2018-01-01', '2018-01-10')->toEnhancedPeriod();
$b = CarbonPeriod::create('2018-01-15', '2018-01-31')->toEnhancedPeriod();

// Use fromEnhancedPeriod to convert `Spatie\Period\Period` objects to `Carbon\CarbonPeriod` ones
echo CarbonPeriod::fromEnhancedPeriod($a->gap($b));

// Or you can directly call gap() or most of the other `Spatie\Period\Period` methods directly on `Carbon\CarbonPeriod`:
$a = CarbonPeriod::create('2018-01-01', '2018-01-10');
$b = CarbonPeriod::create('2018-01-15', '2018-01-31');

// It will use `Spatie\Period\Period::gap` and automatically convert the result to `Carbon\CarbonPeriod`
echo $a->gap($b);

```

Both output (using the default `Carbon\CarbonPeriod` cast to string):
```
Every 1 day from 2018-01-11 to 2018-01-14
```

[See all methods you can call on
`Spatie\Period\Period` objects.](https://github.com/spatie/period)

And here are the methods you can call directly on `CarbonPeriod` instances:

### length
```php
length(): int
```

```php
CarbonPeriod::create('2019-08-20', '2019-09-01')->length();
```

### overlapsWith
```php
overlapsWith($period, ...$arguments): bool
```

```php
CarbonPeriod::create('2019-08-20', '2019-09-01')->overlapsWith('2019-08-28', '2019-09-03');
```

You can pass to `overlapsWith`: `Spatie\Period\Period`, `CarbonPeriod`, `DatePeriod` or
arguments to construct a `CarbonPeriod`.

Note: `->overlapsWith` will give different results from `->overlaps` because it use internally
`Spatie\Period\Period` and its precision mask (use floor rounding).

### touchesWith
```php
touchesWith($period, ...$arguments): bool
```

```php
CarbonPeriod::create('2019-08-20', '2019-09-01')->touchesWith('2019-09-02', '2019-09-06');
```

You can pass to `overlapsWith`: `Spatie\Period\Period`, `CarbonPeriod`, `DatePeriod` or
arguments to construct a `CarbonPeriod`.

Note: `->touchesWith` will give different results from `->isConsecutiveWith` because it use internally
`Spatie\Period\Period` and its precision mask (use floor rounding).

### duration
Warning: this method requires spatie/period >= 2.0
```php
duration(): Spatie\Period\PeriodDuration
```

```php
CarbonPeriod::create('2019-08-20', '2019-09-01')->duration();
```

Get a representation of the iteration duration.

### overlap
Warning: does not match spatie/period 1 overlap() method because of
[upcoming renaming](https://github.com/spatie/period/pull/33), it's
for now called `overlapSingle` in spatie/period.
```php
overlap($period, ...$arguments): ?CarbonPeriod
```

```php
CarbonPeriod::create('2019-08-20', '2019-09-01')->overlap('2019-08-25', '2019-09-05'); // [2019-08-25, 2019-09-01]
```
Return overlap period between current and a given other period.

### overlapAny
Warning: due to
[upcoming renaming](https://github.com/spatie/period/pull/33), it's
for now called `overlap` in spatie/period.
```php
overlapAny($period, ...$arguments): CarbonPeriod[]
```

```php
$a = CarbonPeriod::create('2018-01-01', '2018-01-31');
$b = CarbonPeriod::create('2018-02-10', '2018-02-20');
$c = CarbonPeriod::create('2018-03-01', '2018-03-31');
$d = CarbonPeriod::create('2018-01-20', '2018-03-10');

foreach ($d->overlapAny($a, $b, $c) as $period) {
  echo $period."\n";
}
```

Return overlap chunks that are present in at least 2 periods.
```
A       [========]
B                    [==]
C                            [=====]
CURRENT        [===============]

OVERLAP        [=]   [==]    [=]
```

### overlapAll
```php
overlapAll(...$periods): ?CarbonPeriod
```

```php
$a = CarbonPeriod::create('2018-01-01', '2018-01-31');
$b = CarbonPeriod::create('2018-01-10', '2018-01-15');
$c = CarbonPeriod::create('2018-01-10', '2018-01-31');

echo $a->overlapAll($b, $c);
```

Return the merged overlap of all periods.
```
A              [============]
B                   [==]
C                  [=======]

OVERLAP             [==]
```

### diffAny
Warning: due to
[upcoming renaming](https://github.com/spatie/period/pull/33), it's
for now called `diffSingle` in spatie/period.
```php
diffAny($period, ...$arguments): CarbonPeriod[]
```

```php
$a = CarbonPeriod::create('2018-01-01', '2018-01-15');
$b = CarbonPeriod::create('2018-01-10', '2018-01-30');

foreach ($a->diffAny($b) as $period) {
  echo $period."\n";
}
```

Returns the difference between the current period and an other given one.

### diff
```php
diff(...$periods): CarbonPeriod[]
```

```php
$a = CarbonPeriod::create('2018-01-01', '2018-01-31');
$b = CarbonPeriod::create('2018-02-10', '2018-02-20');
$c = CarbonPeriod::create('2018-02-11', '2018-03-31');

$current = CarbonPeriod::create('2018-01-20', '2018-03-15');

foreach ($current->diff($a, $b, $c) as $period) {
  echo $period."\n";
}
```

Returns periods in the current ones that are not covered by periods passed as arguments.

```
A                   [====]
B                               [========]
C         [=====]
CURRENT      [========================]

DIFF             [=]      [====]
```

### gap
```php
gap($period, ...$arguments): ?CarbonPeriod
```

```php
$a = CarbonPeriod::create('2018-01-01', '2018-01-10');
$b = CarbonPeriod::create('2018-01-15', '2018-01-31');

echo $a->gap($b);
```

Returns the gap period between the current one and the one passed as argument.

```
A       [========]
B                     [===========]

GAP               [==]
```

### fromEnhancedPeriod

```php
fromEnhancedPeriod(Period $period, bool $mutable = false): CarbonPeriod
```

```php
$period = CarbonPeriod::fromEnhancedPeriod(Period::make('2018-01-01', '2018-01-10'));
```

Convert a Spatie period into a Carbon period, you can pass `true` as a second argument
to select `Carbon` as date class rather than `CarbonImmutable` (by default).

### fromNullableEnhancedPeriod

```php
fromNullableEnhancedPeriod(Period|null $period, bool $mutable = false): CarbonPeriod|null
```

```php
$period = CarbonPeriod::fromNullableEnhancedPeriod($spatiePeriod);
```

Same as `fromEnhancedPeriod` but allow null value.

### fromPeriodCollection

```php
fromPeriodCollection(PeriodCollection $periods, $mutable = false): CarbonPeriod[]
```

```php
$periods = CarbonPeriod::fromPeriodCollection(new PeriodCollection(
  Period::make('2018-01-01', '2018-01-10'),
  Period::make('2018-01-15', '2018-01-31')
));

foreach ($periods as $period) {
  echo $period; // $period is a CarbonPeriod instance
}
```

Convert `PeriodCollection` object into an array of `CarbonPeriod` instances.

You can pass `true` as a second argument to select `Carbon` as date class rather
than `CarbonImmutable` (by default).

### convertDateIntervalToPrecisionMask

```php
convertDateIntervalToPrecisionMask(DateInterval $interval): int
```

```php
$precision = CarbonPeriod::convertDateIntervalToPrecisionMask(CarbonInterval::day()); // Precision::DAY
```

Convert `DateInterval` objects (such as `CarbonInterval`) into a Spatie precision mask
if it exists, throws an `RuntimeException` if it does not match any mask.

### convertDateIntervalToPrecisionMask

```php
convertPrecisionMaskToDateInterval(int $precisionMask): CarbonInterval
```

```php
$interval = CarbonPeriod::convertPrecisionMaskToDateInterval(Precision::DAY); // CarbonInterval::day()
```

Convert Spatie precision mask into a `CarbonInterval`.
