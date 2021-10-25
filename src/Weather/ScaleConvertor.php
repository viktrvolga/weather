<?php

declare(strict_types = 1);

namespace App\Weather;

final class ScaleConvertor
{
    /**
     * @todo: support any other scales
     */
    public function process(Temperature $temperature, MeasurementScale $to): Temperature
    {
        if($temperature->measurementScale->equals($to) === false)
        {
            $value = match ($temperature->measurementScale->toString())
            {
                "celsius" => \bcadd(\bcmul($temperature->temperature, '1.8', 1), '32', 1),
                "fahrenheit" => \bcdiv(\bcsub($temperature->temperature, '32', 2), '1.8', 2)
            };

            return new Temperature($to, $value);
        }

        return $temperature;
    }
}
