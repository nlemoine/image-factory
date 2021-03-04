<?php

namespace HelloNico\ImageFactory;

use Spatie\ImageOptimizer\OptimizerChain;

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
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string  $imagePath
     *
     * @return ResponsiveImage|bool
     */
    public function create($imagePath)
    {
        if (!\is_string($imagePath)) {
            return false;
        }

        $image = new ResponsiveImage($imagePath, $this->getSourcePath());
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
        $optimizerChain = $this->getOptimizerChain();
        if ($optimizerChain instanceof OptimizerChain) {
            $image->setOptimizeChain($optimizerChain);
        }
        $image->setOptimizationOptions($this->getOptimizationOptions());
        $image->setMaxMemoryLimit($this->getMaxMemoryLimit());
        $image->setMaxExecutionTime($this->getMaxExecutionTime());
        $image->setBatch($this->getBatch());
        $image->useImageDriver($this->getDriver());

        return $image;
    }

    /**
     * Get driver
     *
     * @return string
     */
    public function getDriver() :string
    {
        return $this->config['driver'] ?? 'gd';
    }

    /**
     * Get source path
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getSourcePath() :string
    {
        if (!isset($this->config['sourcePath'])) {
            throw new \InvalidArgumentException('A "sourcePath" must be set.');
        }

        return $this->config['sourcePath'];
    }

    /**
     * Get cache path
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getCachePath() :string
    {
        if (!isset($this->config['cachePath'])) {
            throw new \InvalidArgumentException('A "cachePath" must be set.');
        }

        return $this->config['cachePath'];
    }

    /**
     * Get public path
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getPublicPath() :string
    {
        if (!isset($this->config['publicPath'])) {
            throw new \InvalidArgumentException('A "publicPath" must be set.');
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
     * Get optimizater chain
     *
     * @return array
     */
    public function getOptimizerChain() :array
    {
        if (isset($this->config['optimizerChain'])) {
            return $this->config['optimizerChain'];
        }

        return [];
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
     * Get max memory limit
     *
     * @return string
     */
    public function getMaxMemoryLimit() :string
    {
        return $this->config['maxMemoryLimit'] ?? '';
    }

    /**
     * Get max execution time
     *
     * @return int
     */
    public function getMaxExecutionTime() :int
    {
        return $this->config['maxExecutionTime'] ?? 120;
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
     * @return int
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

    /**
     * Get batch
     *
     * @return int
     */
    public function getBatch() :int
    {
        return $this->config['batch'] ?? 3;
    }
}
