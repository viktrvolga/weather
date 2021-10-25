<?php

declare(strict_types = 1);

namespace App\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ThrowableListener
{
    public function __construct(
        private Presenter $presenter,
        private LoggerInterface $logger
    )
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = match (\get_class($exception))
        {
            BadRequestHttpException::class => $this->presenter->error(['message' => $exception->getMessage()], 400),
            NotFoundHttpException::class => $this->presenter->error(['message' => $exception->getMessage()], 404),
            default => $this->presenter->error(['message' => $exception->getMessage()])
        };

        $event->setResponse($response);
    }
}
