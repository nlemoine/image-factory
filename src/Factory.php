<?php

namespace HelloNico\ImageFactory;

use InvalidArgumentException;

class Factory
{
    /**
     * Configuration parameters
     *
     * @var array
     */
    protected $config;

    /**
     * ImageFactory constructor
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $src
     * @param mixed  $imagePath
     *
     * @return Image
     */
    public function create($imagePath)
    {
        if ($imagePath instanceof ResponsiveImage) {
            return $imagePath;
        }

        if (!\is_string($imagePath)) {
            return false;
        }

        $image = new ResponsiveImage($imagePath);
        $image->setSourcePath($this->getSourcePath());
        $image->setCachePath($this->getCachePath());
        $image->setPublicPath($this->getPublicPath());
        $image->setRebase($this->getRebase());
        $image->setMinWidth($this->getMinWidth());
        $image->setMaxWidth($this->getMaxWidth());
        $image->setStep($this->getStep());
        $image->setSizes($this->getSizes());
        $image->setScaler($this->getScaler());
        $image->setBaseUrl($this->getBaseUrl());
        $image->setOptimize($this->getOptimize());
        $image->setOptimizationOptions($this->getOptimizationOptions());
        $image->setMaxMemoryLimit($this->getMaxMemoryLimit());
        $image->useImageDriver($this->getDriver());

        return $image;
    }

    /**
     * Get driver
     *
     * @return null|string
     */
    public function getDriver() :string
    {
        return $this->config['driver'] ?? 'gd';
    }

    /**
     * Get source path
     *
     * @return null|string
     */
    public function getSourcePath() :string
    {
        if (!isset($this->config['sourcePath'])) {
            throw new InvalidArgumentException('A "sourcePath" must be set.');
        }

        return $this->config['sourcePath'];
    }

    /**
     * Get cache path
     *
     * @return null|string
     */
    public function getCachePath() :string
    {
        if (!isset($this->config['cachePath'])) {
            throw new InvalidArgumentException('A "cachePath" must be set.');
        }

        return $this->config['cachePath'];
    }

    /**
     * Get public path
     *
     * @return null|string
     */
    public function getPublicPath() :bool
    {
        if (!isset($this->config['publicPath'])) {
            throw new InvalidArgumentException('A "publicPath" must be set.');
        }

        return $this->config['publicPath'];
    }

    /**
     * Get rebase
     *
     * @return bool
     */
    public function getRebase() :bool
    {
        if (isset($this->config['rebase'])) {
            return $this->config['rebase'];
        }

        return false;
    }

    /**
     * Get optimize
     *
     * @return bool
     */
    public function getOptimize() :bool
    {
        if (isset($this->config['optimize'])) {
            return $this->config['optimize'];
        }

        return false;
    }

    /**
     * Get optimization options
     *
     * @return array
     */
    public function getOptimizationOptions() :array
    {
        if (isset($this->config['optimizationOptions'])) {
            return $this->config['optimizationOptions'];
        }

        return [];
    }

    /**
     * Get base URL
     *
     * @return string
     */
    public function getBaseUrl() :string
    {
        return $this->config['baseUrl'] ?? '';
    }

    /**
     * Get base URL
     *
     * @return string
     */
    public function getMaxMemoryLimit() :string
    {
        return $this->config['maxMemoryLimit'] ?? '';
    }

    /**
     * Get scaler
     *
     * @return string
     */
    public function getScaler() :string
    {
        return $this->config['scaler'] ?? 'range';
    }

    /**
     * Get min width
     *
     * @return int
     */
    public function getMinWidth() :int
    {
        return $this->config['minWidth'] ?? 300;
    }

    /**
     * Get max width
     *
     * @return int
     */
    public function getMaxWidth() :int
    {
        return $this->config['maxWidth'] ?? 1000;
    }

    /**
     * Get step modifier
     *
     * @return string
     */
    public function getStep() :int
    {
        return $this->config['step'] ?? 100;
    }

    /**
     * Get sizes
     *
     * @return array
     */
    public function getSizes() :array
    {
        return $this->config['sizes'] ?? [];
    }
}
