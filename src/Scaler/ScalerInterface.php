<?php

namespace HelloNico\ImageFactory\Scaler;

interface ScalerInterface
{

    /**
     * Generate sizes
     *
     * @return array
     */
    public function scale() :array;

    /**
     * @param int $minWidth
     *
     * @return ScalerInterface
     */
    public function setMinWidth(int $minWidth) :ScalerInterface;

    /**
     * @param int $maxWidth
     *
     * @return ScalerInterface
     */
    public function setMaxWidth(int $maxWidth) :ScalerInterface;

    /**
     * @param int $step
     *
     * @return ScalerInterface
     */
    public function setStep(int $step) :ScalerInterface;

    /**
     * @param bool $includeSource
     *
     * @return ScalerInterface
     */
    public function setIncludeSource(bool $includeSource): ScalerInterface;
}
