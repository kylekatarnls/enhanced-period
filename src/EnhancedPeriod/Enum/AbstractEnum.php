<?php

declare(strict_types=1);

namespace EnhancedPeriod\Enum;

abstract class AbstractEnum
{
    private $name;

    private $value;

    protected $wrap;

    public function __construct(string $name)
    {
        $this->name = strtoupper($name);
    }

    public function value()
    {
        if (!isset($this->value)) {
            $method = [$this->wrap, $this->name];
            $this->value = is_callable($method) ? $method() : constant($this->wrap.'::'.$this->name);
        }

        return $this->value;
    }

    public function equals($other): bool
    {
        $value = $this->value();

        if ($other instanceof self) {
            $other = $other->value();
        }

        if (is_object($other) && method_exists($other, 'equals')) {
            return $other->equals($value);
        }

        return $value === $other;
    }
}
