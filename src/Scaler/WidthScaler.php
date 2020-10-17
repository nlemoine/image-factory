<?php

namespace HelloNico\ImageFactory\Scaler;

class WidthScaler extends AbstractScaler
{
    /**
     * @param SplFileInfo $sourceFile
     * @param Image       $imageObject
     * @param mixed       $image
     *
     * @return array
     */
    public function scale($image)
    {
        $sizes = [];

        $width = $this->minWidth;
        while ($width >= $this->minWidth && $width <= $this->maxWidth) {
            if (!$this->maxWidth || $width <= $this->maxWidth) {
                $sizes[] = (int) $width;
            }
            $width = $width + $this->stepModifier;
        }

        return $sizes;
    }
}
