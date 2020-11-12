<?php

namespace HelloNico\ImageFactory\Scaler;

abstract class AbstractScaler implements ScalerInterface
{
    /**
     * @var int
     */
    protected $minWidth;

    /**
     * @var int
     */
    protected $maxWidth;

    /**
     * @var int
     */
    protected $step;

    /**
     * @var bool
     */
    protected $includeSource;

    /**
     * @param int $minWidth
     *
     * @return ScalerInterface
     */
    public function setMinWidth(int $minWidth) :ScalerInterface
    {
        $this->minWidth = $minWidth;

        return $this;
    }

    /**
     * @param int $maxWidth
     *
     * @return ScalerInterface
     */
    public function setMaxWidth(int $maxWidth) :ScalerInterface
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    /**
     * @param int $step
     *
     * @return ScalerInterface
     */
    public function setStep(int $step) :ScalerInterface
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Include source
     *
     * @param bool $includeSource
     * @return ScalerInterface
     */
    public function setIncludeSource(bool $includeSource) :ScalerInterface
    {
        $this->includeSource = $includeSource;

        return $this;
    }
}
