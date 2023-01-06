<?php

namespace HelloNico\ImageFactory\Scaler;

class RangeScaler extends AbstractScaler
{
    public const NAME = 'range';


    public function scale(): array
    {
        $sizes = [];

        $width = $this->minWidth;
        while ($width >= $this->minWidth && $width <= $this->maxWidth) {
            if (!$this->maxWidth || $width <= $this->maxWidth) {
                $sizes[] = (int) $width;
            }
            $width = $width + $this->step;
        }

        return $sizes;
    }
}
