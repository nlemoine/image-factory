<?php

namespace HelloNico\ImageFactory;

use Spatie\ImageOptimizer\OptimizerChain;

class Factory
{
    /**
     * Configuration parameters.
     *
     * @var array
     */
    protected $config;

    /**
     * ImageFactory constructor.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $imagePath
     */
    public function create($imagePath): ?ResponsiveImage
    {
        if (!\is_string($imagePath)) {
            return null;
        }

        $image = new ResponsiveImage(
            $imagePath,
            $this->getSourcePath(),
            $this->getCachePath(),
            $this->getPublicPath()
        );
        $image->setRebase($this->getRebase());
        $image->setMinWidth($this->getMinWidth());
        $image->setMaxWidth($this->getMaxWidth());
        $image->setStep($this->getStep());
        $image->setSizes($this->getSizes());
        $image->setScaler($this->getScaler());
        $image->setBaseUrl($this->getBaseUrl());
        $image->setOptimize($this->getOptimize());
        $image->setFilenameFormat($this->getFilenameFormat());
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
     * Get driver.
     */
    public function getDriver(): string
    {
        return $this->config['driver'] ?? 'gd';
    }

    /**
     * Get source path.
     *
     * @throws \InvalidArgumentException
     */
    public function getSourcePath(): string
    {
        if (!isset($this->config['sourcePath'])) {
            throw new \InvalidArgumentException('A "sourcePath" must be set.');
        }

        return $this->config['sourcePath'];
    }

    /**
     * Get cache path.
     *
     * @throws \InvalidArgumentException
     */
    public function getCachePath(): string
    {
        if (!isset($this->config['cachePath'])) {
            throw new \InvalidArgumentException('A "cachePath" must be set.');
        }

        return $this->config['cachePath'];
    }

    /**
     * Get public path.
     *
     * @throws \InvalidArgumentException
     */
    public function getPublicPath(): string
    {
        if (!isset($this->config['publicPath'])) {
            throw new \InvalidArgumentException('A "publicPath" must be set.');
        }

        return $this->config['publicPath'];
    }

    /**
     * Get rebase.
     */
    public function getRebase(): bool
    {
        if (isset($this->config['rebase'])) {
            return $this->config['rebase'];
        }

        return false;
    }

    /**
     * Get optimize.
     */
    public function getOptimize(): bool
    {
        if (isset($this->config['optimize'])) {
            return $this->config['optimize'];
        }

        return false;
    }

    /**
     * Get optimizater chain.
     */
    public function getOptimizerChain(): array
    {
        if (isset($this->config['optimizerChain'])) {
            return $this->config['optimizerChain'];
        }

        return [];
    }

    /**
     * Get optimization options.
     */
    public function getOptimizationOptions(): array
    {
        if (isset($this->config['optimizationOptions'])) {
            return $this->config['optimizationOptions'];
        }

        return [];
    }

    /**
     * Get base URL.
     */
    public function getBaseUrl(): ?string
    {
        return $this->config['baseUrl'] ?? null;
    }

    /**
     * Get max memory limit.
     */
    public function getMaxMemoryLimit(): ?string
    {
        return $this->config['maxMemoryLimit'] ?? null;
    }

    /**
     * Get max execution time.
     */
    public function getMaxExecutionTime(): int
    {
        return $this->config['maxExecutionTime'] ?? 120;
    }

    /**
     * Get scaler.
     */
    public function getScaler(): string
    {
        return $this->config['scaler'] ?? 'range';
    }

    /**
     * Get min width.
     */
    public function getMinWidth(): int
    {
        return $this->config['minWidth'] ?? 300;
    }

    /**
     * Get max width.
     */
    public function getMaxWidth(): int
    {
        return $this->config['maxWidth'] ?? 1000;
    }

    /**
     * Get step modifier.
     */
    public function getStep(): int
    {
        return $this->config['step'] ?? 100;
    }

    /**
     * Get sizes.
     */
    public function getSizes(): array
    {
        return $this->config['sizes'] ?? [300, 768, 1024, 1200];
    }

    /**
     * Get batch.
     */
    public function getBatch(): int
    {
        return $this->config['batch'] ?? 3;
    }

    /**
     * Get filename format function.
     *
     * @return null|callable|string
     */
    public function getFilenameFormat()
    {
        return $this->config['filenameFormat'] ?? null;
    }
}
