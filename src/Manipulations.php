<?php

namespace HelloNico\ImageFactory;

use Spatie\Image\Manipulations as SpatieManipulations;

class Manipulations extends SpatieManipulations
{
    public const FORMAT_AVIF = 'avif';

    /**
     * @throws InvalidManipulation
     */
    public function dither(): static
    {
        return $this->addManipulation('dither', (string) true);
    }
}
