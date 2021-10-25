<?php

declare(strict_types = 1);

namespace App\Tests\Weather;

use App\Weather\MeasurementScale;
use App\Weather\ScaleConvertor;
use App\Weather\Temperature;
use PHPUnit\Framework\TestCase;

final class ScaleConvertorTest extends TestCase
{
    /**
     * @test
     */
    public function convertCelsiusToFahrenheit(): void
    {
        $temperature = new Temperature(MeasurementScale::celsius(), '78.0');

        $converted = (new ScaleConvertor())->process($temperature, MeasurementScale::fahrenheit());

        self::assertEquals('172.4', $converted->temperature);
        self::assertEquals('fahrenheit', $converted->measurementScale->toString());
    }

    /**
     * @test
     */
    public function convertFahrenheitToCelsius(): void
    {
        $temperature = new Temperature(MeasurementScale::fahrenheit(), '90');

        $converted = (new ScaleConvertor())->process($temperature, MeasurementScale::celsius());

        self::assertEquals('32.22', $converted->temperature);
        self::assertEquals('celsius', $converted->measurementScale->toString());
    }

    /**
     * @test
     */
    public function conversionFlow(): void
    {
        $temperature = new Temperature(MeasurementScale::celsius(), '78.00');

        $converted = (new ScaleConvertor())->process($temperature, MeasurementScale::fahrenheit());

        $reverted = (new ScaleConvertor())->process($converted, MeasurementScale::celsius());

        self::assertEquals('78.00', $reverted->temperature);
    }
}
