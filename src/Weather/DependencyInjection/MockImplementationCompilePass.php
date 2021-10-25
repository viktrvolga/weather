<?php

declare(strict_types = 1);

namespace App\Weather\DependencyInjection;

use App\Weather\Provider\BBC\BbcClient;
use App\Weather\Provider\IAmsterdam\IAmsterdamClient;
use App\Weather\Provider\WeatherCom\WeatherComClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Substitution of a real client to work with http requests
 */
final class MockImplementationCompilePass implements CompilerPassInterface
{
    private const DATA_SOURCE_DIRECTORY = __DIR__ . '/../../../data-sources';

    public function process(ContainerBuilder $container): void
    {
        $this->mockIAmsterdamClient($container);
        $this->mockBbcClient($container);
        $this->mockWeatherClient($container);
    }

    private function mockIAmsterdamClient(ContainerBuilder $container): void
    {
        $this->registerClientDefinition(
            container: $container,
            withId: 'iamsterdam_mocked_http_client',
            withMockedResponse: \file_get_contents(self::DATA_SOURCE_DIRECTORY . '/iamsterdam.json'),
            withContentType: 'application/json'
        );

        $providerDefinition = $container->getDefinition(IAmsterdamClient::class);
        $providerDefinition->addArgument(new Reference('iamsterdam_mocked_http_client'));
    }

    private function mockBbcClient(ContainerBuilder $container): void
    {
        $this->registerClientDefinition(
            container: $container,
            withId: 'bbc_mocked_http_client',
            withMockedResponse: \file_get_contents(self::DATA_SOURCE_DIRECTORY . '/bbc.xml'),
            withContentType: 'text/xml'
        );

        $providerDefinition = $container->getDefinition(BbcClient::class);
        $providerDefinition->addArgument(new Reference('bbc_mocked_http_client'));
    }

    private function mockWeatherClient(ContainerBuilder $container): void
    {
        $this->registerClientDefinition(
            container: $container,
            withId: 'weather_mocked_http_client',
            withMockedResponse: \file_get_contents(self::DATA_SOURCE_DIRECTORY . '/weather_com.csv'),
            withContentType: 'text/csv'
        );

        $providerDefinition = $container->getDefinition(WeatherComClient::class);
        $providerDefinition->addArgument(new Reference('weather_mocked_http_client'));
    }

    private function registerClientDefinition(
        ContainerBuilder $container,
        string           $withId,
        string           $withMockedResponse,
        string           $withContentType
    ): void
    {
        $responseDefinitionId     = \sha1(\sprintf('%s_%s', $withId, 'response'));
        $handlerDefinitionId      = \sha1(\sprintf('%s_%s', $withId, 'handler'));
        $handlerStackDefinitionId = \sha1(\sprintf('%s_%s', $withId, 'handlerStack'));

        $responseDefinition = new Definition(
            Response::class,
            [
                200,
                ['Content-Type' => $withContentType],
                $withMockedResponse
            ]
        );

        $container->addDefinitions(
            [$responseDefinitionId => $responseDefinition]
        );

        $handlerDefinition = new Definition(
            MockHandler::class,
            [
                [new Reference($responseDefinitionId)]
            ]
        );

        $container->addDefinitions(
            [$handlerDefinitionId => $handlerDefinition]
        );

        $handlerStackDefinition = new Definition(
            HandlerStack::class,
            [new Reference($handlerDefinitionId)]
        );

        $container->addDefinitions(
            [$handlerStackDefinitionId => $handlerStackDefinition]
        );

        $clientDefinition = new Definition(
            Client::class,
            [
                '$config' => [
                    'handler' => new Reference($handlerStackDefinitionId)
                ]
            ]
        );

        $container->addDefinitions([$withId => $clientDefinition]);
    }
}
