<?php

namespace HelloNico\ImageFactory\Scaler;

class SizesScaler extends AbstractScaler
{
    public const NAME = 'sizes';

    private array $sizes = [];

    /**
     * Set sizes.
     */
    public function setSizes(array $sizes): self
    {
        $this->sizes = $sizes;

        return $this;
    }

    public function scale(): array
    {
        return $this->sizes;
    }
}
