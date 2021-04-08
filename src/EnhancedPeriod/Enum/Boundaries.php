<?php

declare(strict_types=1);

namespace EnhancedPeriod\Enum;

use Spatie\Period\Boundaries as SpatieBoundaries;

class Boundaries extends AbstractEnum
{
    protected $wrap = SpatieBoundaries::class;

    public static function EXCLUDE_NONE(): self
    {
        return new self('EXCLUDE_NONE');
    }

    public static function EXCLUDE_START(): self
    {
        return new self('EXCLUDE_START');
    }

    public static function EXCLUDE_END(): self
    {
        return new self('EXCLUDE_END');
    }

    public static function EXCLUDE_ALL(): self
    {
        return new self('EXCLUDE_ALL');
    }
}
