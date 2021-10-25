<?php

declare(strict_types = 1);

namespace App\Tests\Weather\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

abstract class ApiClientTestCase extends TestCase
{
    final protected static function createClient(int $withResponseCode, string $withResponsePayload): Client
    {
        $mockHandler = new MockHandler([new Response($withResponseCode, [], $withResponsePayload)]);

        $handlerStack = HandlerStack::create($mockHandler);

        return new Client(['handler' => $handlerStack]);
    }
}