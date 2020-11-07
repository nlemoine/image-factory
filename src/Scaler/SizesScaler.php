<?php

namespace HelloNico\ImageFactory\Scaler;

class SizesScaler extends AbstractScaler
{

    /**
     * @var array
     */
    private $sizes = [];

    /**
     * @param array $sizes
     *
     * @return SizesScaler
     */
    public function setSizes(array $sizes) : SizesScaler
    {
        $this->sizes = $sizes;

        return $this;
    }

    /**
     * @param Image       $imageObject
     *
     * @return array
     */
    public function scale($image) : array
    {
        return $this->sizes;
    }
}
