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
     * Set min width.
     */
    public function setMinWidth(int $minWidth): ScalerInterface
    {
        $this->minWidth = $minWidth;

        return $this;
    }

    /**
     * Set max width.
     */
    public function setMaxWidth(int $maxWidth): ScalerInterface
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    /**
     * Set step.
     */
    public function setStep(int $step): ScalerInterface
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Include source.
     */
    public function setIncludeSource(bool $includeSource): ScalerInterface
    {
        $this->includeSource = $includeSource;

        return $this;
    }
}
