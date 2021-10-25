<?php

declare(strict_types = 1);

namespace App\Api\Weather\Controller\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PredictWeather
{
    /**
     * @Assert\NotNull(message="`date` parameter is required")
     * @Assert\NotBlank(message="`date` parameter is required")
     *
     * @var string
     */
    public $date;

    /**
     * @Assert\NotNull(message="`city` parameter is required")
     * @Assert\NotBlank(message="`city` parameter is required")
     *
     * @var string
     */
    public $city;

    /**
     * @Assert\NotNull(message="`scale` parameter is required")
     * @Assert\NotBlank(message="`scale` parameter is required")
     * @Assert\Choice({"celsius", "fahrenheit"})
     *
     * @var string
     */
    public $scale;

    /**
     * @Assert\Callback
     */
    public function validateDate(ExecutionContextInterface $context)
    {
        $requestedDate = new \DateTimeImmutable($this->date);
        $currentDate = new \DateTimeImmutable('today');
        $maxDate     = $currentDate->modify('+10 days');

        if($requestedDate >= $maxDate || $requestedDate < $currentDate)
        {
            $context->addViolation("You cannot see the weather forecast for this date");
        }
    }
}
