<?php

declare(strict_types = 1);

namespace App\Weather\Provider\WeatherCom;

use App\Common\City;
use App\Weather\MeasurementScale;
use App\Weather\Provider\WeatherProvider;
use App\Weather\ScaleConvertor;
use App\Weather\Weather;

final class WeatherComProvider implements WeatherProvider
{
    public function __construct(
        private WeatherComClient $client,
        private ScaleConvertor   $scaleConvertor
    )
    {
    }

    public function name(): string
    {
        return 'weather.com';
    }

    public function loadPredictions(\DateTimeImmutable $forDate, City $forCity, MeasurementScale $inScale): array
    {
        $predictions = [];

        /** @var Weather $weatherPrediction */
        foreach($this->client->load($forDate, $forCity) as $weatherPrediction)
        {
            $predictions[] = $weatherPrediction->convert($this->scaleConvertor, $inScale);
        }

        return $predictions;
    }
}
