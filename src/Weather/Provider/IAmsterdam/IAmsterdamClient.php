<?php

declare(strict_types = 1);

namespace App\Weather\Provider\IAmsterdam;

use App\Common\City;
use App\Weather\MeasurementScale;
use App\Weather\Provider\Exceptions\LoadPredictionsFailed;
use App\Weather\Temperature;
use App\Weather\Weather;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class IAmsterdamClient
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        private Client  $httpClient,
        LoggerInterface $logger = null
    )
    {
        $this->logger = $logger ?? new NullLogger();
    }

    public function load(\DateTimeImmutable $forDate, City $forCity): \Iterator
    {
        $response = $this->httpClient->request('GET', '', ['http_errors' => false]);

        if($response->getStatusCode() === 200)
        {
            try
            {
                $payload = \json_decode(
                    (string) $response->getBody(), true, 512, \JSON_THROW_ON_ERROR
                );

                return $this->parseResponseBody($payload);
            }
            catch(\Throwable $throwable)
            {
                throw new LoadPredictionsFailed(
                    \sprintf(
                        'Incorrect response details: %s',
                        $throwable->getMessage()
                    )
                );
            }
        }

        throw new LoadPredictionsFailed(
            \sprintf(
                'Unable receive predictions from IAmsterdam api. Response code: %d', $response->getStatusCode()
            )
        );
    }

    private function parseResponseBody(array $responseData): \Iterator
    {
        $scale = MeasurementScale::fahrenheit();
        $city  = new City($responseData['predictions']['city'] ?: throw new \RuntimeException('Incorrect city'));

        foreach($responseData['predictions']['prediction'] ?? [] as $prediction)
        {
            /** Some values may be incorrect. As I understand it, we just need to skip them */
            if(!empty($prediction['time']) && !empty($prediction['value']))
            {
                yield new Weather(
                    city: $city,
                    date: self::buildDatetime($responseData['predictions']['date'], $prediction['time']),
                    temperature: new Temperature(
                        measurementScale: $scale, temperature: $prediction['value']
                    )
                );
            }
            else
            {
                $this->logger->debug(
                    \sprintf(
                        'An incorrectly formed line or line with incorrect data was received in the response from IAmsterdam: %s',
                        var_export($prediction, true)
                    )
                );
            }
        }
    }

    private static function buildDatetime(string $date, string $time): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat(
            'Ymd H:m',
            \sprintf('%s %s', $date, $time)
        );
    }
}