<?php

declare(strict_types = 1);

namespace App\Weather\Provider\BBC;

use App\Common\City;
use App\Weather\MeasurementScale;
use App\Weather\Provider\Exceptions\LoadPredictionsFailed;
use App\Weather\Temperature;
use App\Weather\Weather;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Expected content type: xml
 * Expected scale: celsius
 */
final class BbcClient
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
                $xmlReader = XmlReader::read((string) $response->getBody());

                return $this->parseResponseBody($xmlReader);
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
                'Unable receive predictions from BBC api. Response code: %d', $response->getStatusCode()
            )
        );
    }

    private function parseResponseBody(XmlReader $xmlReader): \Iterator
    {
        $scale = MeasurementScale::celsius();

        $city = $xmlReader->readPropertyAsString('city');
        $date = $xmlReader->readPropertyAsString('date');

        /** @var \SimpleXMLElement|null $predictions */
        $predictions = $xmlReader->readProperty('prediction');

        if(!empty($city) && !empty($date) && $predictions !== null)
        {
            foreach($predictions as $prediction)
            {
                $predictionElement = XmlReader::wrap($prediction);

                $time  = $predictionElement->readPropertyAsString('time');
                $value = $predictionElement->readPropertyAsString('value');

                if(!empty($time) && !empty($value))
                {
                    yield new Weather(
                        city: new City($city),
                        date: self::buildDatetime($date, $time),
                        temperature: new Temperature(
                            measurementScale: $scale, temperature: $value
                        )
                    );
                }
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
