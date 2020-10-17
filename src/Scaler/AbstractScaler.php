<?php

namespace HelloNico\ImageFactory\Scaler;

abstract class AbstractScaler implements Scaler
{
    /**
     * @var int
     */
    protected $minFileSize;

    /**
     * @var int
     */
    protected $minWidth;

    /**
     * @var int
     */
    protected $maxFileSize;

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
     * @param mixed $minFileSize
     *
     * @return AbstractScaler
     */
    public function setMinFileSize($minFileSize)
    {
        $this->minFileSize = $minFileSize;

        return $this;
    }

    /**
     * @param mixed $minWidth
     *
     * @return AbstractScaler
     */
    public function setMinWidth($minWidth)
    {
        $this->minWidth = $minWidth;

        return $this;
    }

    /**
     * @param mixed $maxFileSize
     *
     * @return AbstractScaler
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * @param mixed $maxWidth
     *
     * @return AbstractScaler
     */
    public function setMaxWidth($maxWidth)
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    /**
     * @param mixed $stepModifier
     *
     * @return AbstractScaler
     */
    public function setStepModifier($stepModifier)
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
