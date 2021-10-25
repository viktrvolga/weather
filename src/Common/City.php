<?php

declare(strict_types = 1);

namespace App\Common;

/**
 * @todo: support city code
 */
final class City
{
    public function __construct(
        public string $code
    )
    {

    }
}
