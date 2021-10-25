<?php

declare(strict_types = 1);

namespace App\Weather\Forecaster;

use App\Common\City;
use App\Weather\MeasurementScale;

final class Prediction
{
    public function __construct(
        public City               $city,
        public \DateTimeImmutable $date,
        public MeasurementScale   $scale,
        public array              $collection
    )
    {
    }
}