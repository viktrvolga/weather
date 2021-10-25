<?php

declare(strict_types = 1);

namespace App\Api\Weather\Controller;

use App\Api\Presenter;
use App\Api\Weather\Controller\Request\PredictWeather;
use App\Common\City;
use App\Weather\Forecaster\WeatherForecaster;
use App\Weather\MeasurementScale;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
final class PredictionsController
{
    public function __construct(
        private Presenter $presenter
    )
    {
    }

    /**
     * @Route(
     *     "/weather/predict",
     *     methods={"GET"},
     *     name="predict_weather"
     * )
     */
    public function predictions(PredictWeather $request, WeatherForecaster $weatherForecaster): Response
    {
        $collection = $weatherForecaster->predict(
            forDate: new \DateTimeImmutable($request->date),
            forCity: new City($request->city),
            inScale: MeasurementScale::fromString($request->scale)
        )->collection;

        return $this->presenter->success($collection);
    }
}
