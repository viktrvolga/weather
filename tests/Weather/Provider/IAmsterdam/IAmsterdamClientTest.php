<?php

declare(strict_types = 1);

namespace App\Tests\Weather\Provider\IAmsterdam;

use App\Common\City;
use App\Tests\Weather\Provider\ApiClientTestCase;
use App\Weather\Provider\Exceptions\LoadPredictionsFailed;
use App\Weather\Provider\IAmsterdam\IAmsterdamClient;

final class IAmsterdamClientTest extends ApiClientTestCase
{
    /**
     * @test
     */
    public function successLoad(): void
    {
        $httpClient = self::createClient(
            200,
            \file_get_contents(__DIR__ . '/../../../../data-sources/iamsterdam.json')
        );

        $client = new IAmsterdamClient($httpClient);

        $result = \iterator_to_array($client->load(new \DateTimeImmutable('now'), new City('Amsterdam')));

        self::assertCount(11, $result);
    }

    /**
     * @test
     */
    public function failedLoad(): void
    {
        $this->expectException(LoadPredictionsFailed::class);
        $this->expectExceptionMessage('Unable receive predictions from IAmsterdam api. Response code: 500');

        $httpClient = self::createClient(
            500,
            'Bad response'
        );

        $client = new IAmsterdamClient($httpClient);

        $client->load(new \DateTimeImmutable('now'), new City('Amsterdam'));
    }

    /**
     * @test
     */
    public function withIncorrectPayload(): void
    {
        $this->expectException(LoadPredictionsFailed::class);
        $this->expectExceptionMessage('Incorrect response details: Syntax error');

        $httpClient = self::createClient(
            200,
            'Some unexpected body'
        );

        $client = new IAmsterdamClient($httpClient);

        $client->load(new \DateTimeImmutable('now'), new City('Amsterdam'));
    }
}
