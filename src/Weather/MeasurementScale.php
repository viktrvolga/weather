<?php

declare(strict_types = 1);

namespace App\Weather;

final class MeasurementScale
{
    private const CELSIUS    = 'celsius';
    private const FAHRENHEIT = 'fahrenheit';

    private const SUPPORTED = [self::FAHRENHEIT, self::CELSIUS];

    public static function fromString(string $value)
    {
        if(\in_array($value, self::SUPPORTED, true))
        {
            return new self($value);
        }

        throw new \InvalidArgumentException(\sprintf('Incorrect scale `%s`', $value));
    }

    public static function celsius(): self
    {
        return new self(self::CELSIUS);
    }

    public static function fahrenheit(): self
    {
        return new static(self::FAHRENHEIT);
    }

    public function equals(MeasurementScale $to): bool
    {
        return $this->value === $to->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    private function __construct(private string $value)
    {
    }
}