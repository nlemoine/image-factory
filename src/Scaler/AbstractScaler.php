<?php

namespace HelloNico\ImageFactory\Scaler;

abstract class AbstractScaler implements Scaler
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
     * @var float
     */
    protected $stepModifier;

    /**
     * @var bool
     */
    protected $includeSource;

    public function __construct()
    {
    }

    /**
     * @param mixed $minWidth
     *
     * @return AbstractScaler
     */
    public function setMinWidth(int $minWidth)
    {
        $this->minWidth = $minWidth;

        return $this;
    }

    /**
     * @param mixed $maxWidth
     *
     * @return AbstractScaler
     */
    public function setMaxWidth(int $maxWidth)
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    /**
     * @param mixed $stepModifier
     *
     * @return AbstractScaler
     */
    public function setStepModifier(int $stepModifier)
    {
        $this->stepModifier = $stepModifier;

        return $this;
    }

    public function setIncludeSource(bool $includeSource): Scaler
    {
        $this->includeSource = $includeSource;

        return $this;
    }
}
