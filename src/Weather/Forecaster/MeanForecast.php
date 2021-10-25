<?php

declare(strict_types = 1);

namespace App\Weather\Forecaster;

use App\Common\City;
use App\Weather\MeasurementScale;
use App\Weather\Provider\WeatherProvider;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class MeanForecast implements WeatherForecaster
{
    /**
     * @var WeatherProvider[]
     */
    private $providers;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(array $providers, LoggerInterface $logger = null)
    {
        $this->providers = $providers;
        $this->logger    = $logger ?? new NullLogger();
    }

    public function predict(\DateTimeImmutable $forDate, City $forCity, MeasurementScale $inScale)
    {
        $structure = $this->prepareRangeStructure($forDate);

        foreach($this->providers as $provider)
        {
            foreach($provider->loadPredictions($forDate, $forCity, $inScale) as $prediction)
            {
                $structure[$prediction->date->format('H')][] = $prediction->temperature->temperature;
            }
        }

        $collection = \array_map(
            static function(array $predictions): ?string
            {
                if(\count($predictions) === 0)
                {
                    return null;
                }

                return (string) (\array_sum($predictions) / \count($predictions));
            },
            $structure
        );

        return new Prediction(
            city: $forCity,
            date: $forDate,
            scale: $inScale,
            collection: $collection
        );
    }

    private function prepareRangeStructure(\DateTimeImmutable $forDate): array
    {
        $result = [];

        $startOfTheDay = $forDate->modify('today');
        $endOfTheDay   = $forDate->modify('tomorrow');

        $datePeriod = new \DatePeriod($startOfTheDay, new \DateInterval('PT1H'), $endOfTheDay);

        foreach($datePeriod as $date)
        {
            $result[$date->format("H")] = [];
        }

        return $result;
    }
}
