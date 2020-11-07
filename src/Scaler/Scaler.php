<?php

namespace HelloNico\ImageFactory\Scaler;

interface Scaler
{
    /**
     * Scaler constructor.
     */
    public function __construct();

    /**
     * @param SplFileInfo $sourceFile
     * @param Image       $imageObject
     * @param mixed       $image
     *
     * @return array
     */
    public function scale($image);

    /**
     * @param $minWidth
     *
     * @return mixed
     */
    public function setMinWidth(int $minWidth);

    /**
     * @param $maxWidth
     *
     * @return mixed
     */
    public function setMaxWidth(int $maxWidth);

    /**
     * @param $stepModifier
     *
     * @return mixed
     */
    public function setStepModifier(int $step);

    /**
     * @param $includeSource
     *
     * @return mixed
     */
    public function setIncludeSource(bool $includeSource): Scaler;
}
