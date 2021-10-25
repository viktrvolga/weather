<?php

declare(strict_types = 1);

namespace App\Weather\Provider\WeatherCom;

use App\Common\City;
use App\Weather\MeasurementScale;
use App\Weather\Provider\Exceptions\LoadPredictionsFailed;
use App\Weather\Temperature;
use App\Weather\Weather;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class WeatherComClient
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
                $csvData = \explode("\r\n", (string) $response->getBody());

                if(\count($csvData) > 2)
                {
                    return $this->parseResponseData($csvData);
                }

                throw new \RuntimeException('Incorrect csv response');
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
                'Unable receive predictions from Weather.com api. Response code: %d', $response->getStatusCode()
            )
        );
    }

    public function parseResponseData(array $lines): \Iterator
    {
        /** headers line */
        unset($lines[0]);

        foreach($lines as $line)
        {
            try
            {
                [, $city, $date, $time, $value] = \explode(',', \str_replace('"', '', $line));

                $city  = new City($city);
                $scale = MeasurementScale::celsius();

                /** Some values may be incorrect. As I understand it, we just need to skip them */
                if($scale !== null && !empty($time) && !empty($date))
                {
                    yield new Weather(
                        city: $city,
                        date: self::buildDatetime($date, $time),
                        temperature: new Temperature(
                            measurementScale: $scale, temperature: $value
                        )
                    );
                }

            }
            catch(\Throwable)
            {
                $this->logger->debug(
                    \sprintf(
                        'An incorrectly formed line or line with incorrect data was received in the response from Weather: %s',
                        $line
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
