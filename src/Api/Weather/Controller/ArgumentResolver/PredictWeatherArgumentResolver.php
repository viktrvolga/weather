<?php

declare(strict_types = 1);

namespace App\Api\Weather\Controller\ArgumentResolver;

use App\Api\Weather\Controller\Request\PredictWeather;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PredictWeatherArgumentResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private Serializer         $serializer,
        private ValidatorInterface $validator
    )
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return PredictWeather::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        $data = $this->serializer->denormalize($request->query->all(), PredictWeather::class);

        $violations = $this->validator->validate($data);

        if($violations->count() === 0)
        {
            return yield $data;
        }

        /** @var ConstraintViolation $firstViolation * */
        $firstViolation = $violations[0];

        throw new BadRequestHttpException((string) $firstViolation);
    }
}
