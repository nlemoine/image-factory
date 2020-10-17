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
     * @param $minFileSize
     *
     * @return mixed
     */
    public function setMinFileSize($minFileSize);

    /**
     * @param $minWidth
     *
     * @return mixed
     */
    public function setMinWidth($minWidth);

    /**
     * @param $maxFileSize
     *
     * @return mixed
     */
    public function setMaxFileSize($maxFileSize);

    /**
     * @param $maxWidth
     *
     * @return mixed
     */
    public function setMaxWidth($maxWidth);

    /**
     * @param $stepModifier
     *
     * @return mixed
     */
    public function setStepModifier($stepModifier);

    /**
     * @param $includeSource
     *
     * @return mixed
     */
    public function setIncludeSource(bool $includeSource): Scaler;
}
