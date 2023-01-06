<?php

namespace HelloNico\ImageFactory;

use Spatie\Image\Manipulations as SpatieManipulations;

class Manipulations extends SpatieManipulations
{
    public const FORMAT_AVIF = 'avif';

    public const FILTER_DITHERING = 'dithering';

    /**
     * @throws InvalidManipulation
     */
    public function dithering(): static
    {
        return $this->filter(self::FILTER_DITHERING);
    }
}
