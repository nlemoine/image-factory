<?php

namespace HelloNico\ImageFactory\Scaler;

interface ScalerInterface
{
    /**
     * Generate sizes.
     */
    public function scale(): array;

    public function setMinWidth(int $minWidth): ScalerInterface;

    public function setMaxWidth(int $maxWidth): ScalerInterface;

    public function setStep(int $step): ScalerInterface;

    public function setIncludeSource(bool $includeSource): ScalerInterface;
}
