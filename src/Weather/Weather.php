<?php

declare(strict_types = 1);

namespace App\Weather;

use App\Common\City;

final class Weather
{
    public function __construct(
        public City               $city,
        public \DateTimeImmutable $date,
        public Temperature        $temperature
    )
    {
    }

    public function convert(ScaleConvertor $scaleConvertor, MeasurementScale $to): self
    {
        if($this->temperature->measurementScale->equals($to) === false)
        {
            return new self(
                city: $this->city,
                date: $this->date,
                temperature: $scaleConvertor->process($this->temperature, $to)
            );
        }

        return $this;
    }
}
