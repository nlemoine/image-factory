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
     * @inheritDoc
     */
    public function scale() : array
    {
        return $this->sizes;
    }
}
