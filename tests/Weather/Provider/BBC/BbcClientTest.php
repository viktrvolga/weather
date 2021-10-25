<?php

declare(strict_types = 1);

namespace App\Tests\Weather\Provider\BBC;

use App\Common\City;
use App\Tests\Weather\Provider\ApiClientTestCase;
use App\Weather\Provider\BBC\BbcClient;
use App\Weather\Provider\Exceptions\LoadPredictionsFailed;

final class BbcClientTest extends ApiClientTestCase
{
    /**
     * @test
     */
    public function successLoad(): void
    {
        $httpClient = self::createClient(
            200,
            \file_get_contents(__DIR__ . '/../../../../data-sources/bbc.xml')
        );

        $bbcClient = new BbcClient($httpClient);

        $result = \iterator_to_array($bbcClient->load(new \DateTimeImmutable('now'), new City('Amsterdam')));

        self::assertCount(11, $result);
    }

    /**
     * @test
     */
    public function failedLoad(): void
    {
        $this->expectException(LoadPredictionsFailed::class);
        $this->expectExceptionMessage('Unable receive predictions from BBC api. Response code: 500');

        $httpClient = self::createClient(
            500,
            'Bad response'
        );

        $bbcClient = new BbcClient($httpClient);

        $bbcClient->load(new \DateTimeImmutable('now'), new City('Amsterdam'));
    }

    /**
     * @test
     */
    public function withIncorrectPayload(): void
    {
        $this->expectException(LoadPredictionsFailed::class);
        $this->expectExceptionMessage('Incorrect response details: Unable to parse xml response: Start tag expected, \'<\' not found');

        $httpClient = self::createClient(
            200,
            'Some unexpected body'
        );

        $bbcClient = new BbcClient($httpClient);

       $bbcClient->load(new \DateTimeImmutable('now'), new City('Amsterdam'));
    }
}
