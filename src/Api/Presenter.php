<?php

declare(strict_types = 1);

namespace App\Api;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

final class Presenter
{
    public function __construct(
        private Serializer $serializer,
        private string     $format
    )
    {
    }

    public function success(array|object $projection = []): Response
    {
        return $this->render(
            $this->serializer->serialize(['success' => true, 'data' => $projection], $this->format),
            200
        );
    }

    public function error(array|object $projection, int $httpCode = 500): Response
    {
        return $this->render(
            $this->serializer->serialize(['success' => false, 'data' => $projection], $this->format),
            $httpCode
        );
    }

    /**
     * @todo: support Accept header
     */
    private function render(string $data, int $responseCode): Response
    {
        $contentTypeHeader = match ($this->format)
        {
            'json' => 'application\json',
            'xml' => 'text\xml'
        };

        return new Response($data, $responseCode, ['Content-Type' => $contentTypeHeader]);
    }
}
