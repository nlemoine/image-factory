<?php

namespace HelloNico\ImageFactory\Scaler;

interface ScalerInterface
{
    /**
     * Generate sizes.
     */
    public function scale(): array;

    public function setMinWidth(int $minWidth): self;

    public function setMaxWidth(int $maxWidth): self;

    public function setStep(int $step): self;

    public function setIncludeSource(bool $includeSource): self;
}
