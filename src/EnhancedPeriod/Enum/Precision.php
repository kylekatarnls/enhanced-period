<?php

declare(strict_types=1);

namespace EnhancedPeriod\Enum;

use Spatie\Period\Precision as SpatiePrecision;

class Precision extends AbstractEnum
{
    protected $wrap = SpatiePrecision::class;

    public static function YEAR(): self
    {
        return new self('YEAR');
    }

    public static function MONTH(): self
    {
        return new self('MONTH');
    }

    public static function DAY(): self
    {
        return new self('DAY');
    }

    public static function HOUR(): self
    {
        return new self('HOUR');
    }

    public static function MINUTE(): self
    {
        return new self('MINUTE');
    }

    public static function SECOND(): self
    {
        return new self('SECOND');
    }
}
