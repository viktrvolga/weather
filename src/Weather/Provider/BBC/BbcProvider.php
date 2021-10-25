<?php

declare(strict_types = 1);

namespace App\Weather\Provider\BBC;

use App\Common\City;
use App\Weather\MeasurementScale;
use App\Weather\Provider\WeatherProvider;
use App\Weather\ScaleConvertor;
use App\Weather\Weather;

/**
 * @todo: cache results
 */
final class BbcProvider implements WeatherProvider
{
    public function __construct(
        private BbcClient      $client,
        private ScaleConvertor $scaleConvertor
    )
    {
    }

    public function name(): string
    {
        return 'bbc';
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
