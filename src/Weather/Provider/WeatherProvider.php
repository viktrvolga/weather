<?php

declare(strict_types = 1);

namespace App\Weather\Provider;

use App\Common\City;
use App\Weather\MeasurementScale;
use App\Weather\Weather;

interface WeatherProvider
{
    public function name(): string;

    /**
     * @return Weather[]
     *
     * @throws \App\Weather\Provider\Exceptions\LoadPredictionsFailed
     */
    public function loadPredictions(\DateTimeImmutable $forDate, City $forCity, MeasurementScale $inScale): array;
}
