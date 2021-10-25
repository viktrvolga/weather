<?php

declare(strict_types = 1);

namespace App\Tests\Weather\Forecaster;

use App\Common\City;
use App\Tests\Weather\Provider\ApiClientTestCase;
use App\Weather\Forecaster\MeanForecast;
use App\Weather\MeasurementScale;
use App\Weather\Provider\BBC\BbcClient;
use App\Weather\Provider\BBC\BbcProvider;
use App\Weather\Provider\IAmsterdam\IAmsterdamClient;
use App\Weather\Provider\IAmsterdam\IAmsterdamProvider;
use App\Weather\Provider\WeatherCom\WeatherComClient;
use App\Weather\Provider\WeatherCom\WeatherComProvider;
use App\Weather\Provider\WeatherProvider;
use App\Weather\ScaleConvertor;
use App\Weather\Forecaster\WeatherForecaster;

final class MeanForecastTest extends ApiClientTestCase
{
    /**
     * @var WeatherProvider[]
     */
    private static $providers;

    /**
     * @var WeatherForecaster
     */
    private static $weatherForecaster;

    public static function setUpBeforeClass(): void
    {
        $scaleConvertor = new ScaleConvertor();

        $bbcHttpClient = self::createClient(
            200,
            \file_get_contents(__DIR__ . '/../../../data-sources/bbc.xml')
        );

        $weatherComHttpClient = self::createClient(
            200,
            \file_get_contents(__DIR__ . '/../../../data-sources/weather_com.csv')
        );

        $amsterdamHttpClient = self::createClient(
            200,
            \file_get_contents(__DIR__ . '/../../../data-sources/iamsterdam.json')
        );

        self::$providers = [
            new BbcProvider(new BbcClient($bbcHttpClient), $scaleConvertor),
            new WeatherComProvider(new WeatherComClient($weatherComHttpClient), $scaleConvertor),
            new IAmsterdamProvider(new IAmsterdamClient($amsterdamHttpClient), $scaleConvertor)
        ];

        self::$weatherForecaster = new MeanForecast(self::$providers);
    }

    /**
     * @test
     */
    public function predict(): void
    {
        $result = self::$weatherForecaster->predict(
            new \DateTimeImmutable('now'),
            new City('Vegas'),
            MeasurementScale::celsius()
        );

        self::assertCount(24, $result->collection);
    }
}
