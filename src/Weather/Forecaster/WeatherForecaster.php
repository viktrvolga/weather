<?php

declare(strict_types = 1);

namespace App\Weather\Forecaster;

use App\Common\City;
use App\Weather\MeasurementScale;

interface WeatherForecaster
{
    public function predict(\DateTimeImmutable $forDate, City $forCity, MeasurementScale $inScale);
}
