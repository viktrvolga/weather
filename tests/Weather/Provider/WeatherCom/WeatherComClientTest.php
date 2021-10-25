<?php

declare(strict_types = 1);

namespace App\Tests\Weather\Provider\WeatherCom;

use App\Common\City;
use App\Tests\Weather\Provider\ApiClientTestCase;
use App\Weather\Provider\Exceptions\LoadPredictionsFailed;
use App\Weather\Provider\WeatherCom\WeatherComClient;

final class WeatherComClientTest extends ApiClientTestCase
{
    /**
     * @test
     */
    public function successLoad(): void
    {
        $httpClient = self::createClient(
            200,
            \file_get_contents(__DIR__ . '/../../../../data-sources/weather_com.csv')
        );

        $client = new WeatherComClient($httpClient);

        $result = \iterator_to_array($client->load(new \DateTimeImmutable('now'), new City('Amsterdam')));

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function failedLoad(): void
    {
        $this->expectException(LoadPredictionsFailed::class);
        $this->expectExceptionMessage('Unable receive predictions from Weather.com api. Response code: 500');

        $httpClient = self::createClient(
            500,
            'Bad response'
        );

        $client = new WeatherComClient($httpClient);

        $client->load(new \DateTimeImmutable('now'), new City('Amsterdam'));
    }

    /**
     * @test
     */
    public function withIncorrectPayload(): void
    {
        $this->expectException(LoadPredictionsFailed::class);
        $this->expectExceptionMessage('Incorrect response details: Incorrect csv response');

        $httpClient = self::createClient(
            200,
            'Some unexpected body'
        );

        $client = new WeatherComClient($httpClient);

        $client->load(new \DateTimeImmutable('now'), new City('Amsterdam'));
    }
}
