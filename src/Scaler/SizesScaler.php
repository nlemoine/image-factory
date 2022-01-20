<?php

namespace HelloNico\ImageFactory\Scaler;

class SizesScaler extends AbstractScaler
{
    public const NAME = 'sizes';

    private array $sizes = [];

    /**
     * Set sizes.
     */
    public function setSizes(array $sizes): SizesScaler
    {
        $this->sizes = $sizes;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function scale(): array
    {
        return $this->sizes;
    }
}
