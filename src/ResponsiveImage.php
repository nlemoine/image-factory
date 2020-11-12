<?php

namespace HelloNico\ImageFactory;

use HelloNico\ImageFactory\Scaler\RangeScaler;
use HelloNico\ImageFactory\Scaler\SizesScaler;
use HelloNico\ImageFactory\Scaler\ScalerInterface;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Spatie\Image\GlideConversion;

class ResponsiveImage extends Image
{

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Source path
     *
     * @var string
     */
    private $sourcePath;

    /**
     * Cache path
     *
     * @var string
     */
    private $cachePath;

    /**
     * Public path
     *
     * @var string
     */
    private $publicPath;

    /**
     * Base URL
     *
     * @var string
     */
    private $baseUrl;

    /**
     * Rebase
     *
     * @var bool
     */
    private $rebase;

    /**
     * Max memory limit
     *
     * @var string
     */
    private $maxMemoryLimit;

    /**
     * Optimize
     *
     * @var bool
     */
    private $optimize;

    /**
     * Optimization options
     *
     * @var array
     */
    private $optimizationOptions;

    /**
     * Has srcset
     *
     * @var boolean
     */
    private $hasSrcSet = false;

    /**
     * Has datauri
     *
     * @var boolean
     */
    private $hasDataUri = false;

    /**
     * Original path
     *
     * @var string
     */
    private $originalImagePath;

    /**
     * Scaler
     *
     * @var ScalerInterface|string
     */
    private $scaler;

    /**
     * Max width
     *
     * @var int
     */
    private $minWidth;

    /**
     * Max width
     *
     * @var int
     */
    private $maxWidth;

    /**
     * Step
     *
     * @var int
     */
    private $step;

    /**
     * Sizes
     *
     * @var array
     */
    private $sizes = [];

    /**
     * Batch
     *
     * @var int
     */
    private $batch = 3;

    public function __construct(string $pathToImage)
    {
        $this->pathToImage = $pathToImage;

        $this->manipulations = new Manipulations();

        $this->filesystem = new Filesystem();
    }

    /**
     * Undocumented function.
     *
     * @return string
     */
    public function __toString() :string
    {
        if ($this->getHasSrcset()) {
            return $this->getSrcSet();
        }
        return $this->getSrc();
    }

    /**
     * Get src
     *
     * @return string
     */
    public function getSrc() :string
    {
        $imagePath = $this->generateImage();

        // Return data uri
        if ($this->getHasDataUri()) {
            return $this->getBase64($imagePath);
        }
        // Return URL
        return $this->resolveUrl($imagePath);
    }

    /**
     * Get srcset
     *
     * @return string
     */
    public function getSrcSet() :string
    {
        $srcset = $this->getSrcSetSources();

        if (empty($srcset)) {
            return '';
        }

        \ksort($srcset);

        return implode(',', array_map(function ($width, $path) {
            return sprintf('%s %dw', $this->resolveUrl($path), $width);
        }, array_keys($srcset), $srcset));
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getSrcSetSources(): array
    {
        $sizes = $this->scaler->scale();

        // start by wider images
        rsort($sizes);

        $originalWidth = $this->manipulations->getManipulationArgument('width');
        $originalHeight = $this->manipulations->getManipulationArgument('height');

        $srcset = [];
        $batch = !empty($this->getBatch()) ? $this->getBatch() : 0;
        foreach ($sizes as $i => $width) {
            if ($this->hasManipulation('width')) {
                $this->manipulations->removeManipulation('width');
            }
            if ($this->hasManipulation('height')) {
                $this->manipulations->removeManipulation('height');
            }

            // Set width
            $this->width($width);

            // Set height while keeping aspect ratio
            // @todo List manipulations that needs recalculating height
            if ($this->hasManipulation('crop') && $originalWidth && $originalHeight) {
                $height = intval(round(($originalHeight / $originalWidth) * $width));
                $this->height($height);
            }

            $imageCachePath = $this->getCacheFilePath();

            if ($batch) {
                $exists = $this->filesystem->exists($imageCachePath);
                if ($exists) {
                    $batch += 1;
                }
                if (!$exists && ($i + 1) > $batch) {
                    continue;
                }
            }

            $path = $this->generateImage($imageCachePath);
            if ($path) {
                $srcset[(int) $width] = $path;
            }
        }

        return $srcset;
    }

    /**
     * Set srcset scaler
     *
     * @return ResponsiveImage
     */
    public function srcset() :ResponsiveImage
    {
        $args = func_get_args();

        $this->setHasSrcset(true);

        // sizes scaler
        if (count($args) === 1 && is_array($args[0])) {
            $this->setSizes($args[0]);
            $this->setScaler('sizes');
        }

        // range scaler
        if (in_array(count($args), [2, 3], true)) {
            $minWidth = $args[0];
            $maxWidth = $args[1];
            $step = $args[2] ?? null;

            if ($minWidth >= $maxWidth) {
                throw new \InvalidArgumentException(sprintf('min width (%d) must be greater than maw width (%d)', $minWidth, $maxWidth));
            }

            $this->setMinWidth($minWidth);
            $this->setMaxWidth($maxWidth);
            if (!empty($step)) {
                $this->setStep($step);
            }
            $this->setScaler('range');
        }

        return $this;
    }

    /**
     * Manipulate image
     *
     * @param array|callable|Manipulations $manipulations
     *
     * @return ResponsiveImage
     */
    public function manipulate($manipulations): ResponsiveImage
    {
        if (!is_array($manipulations)) {
            parent::manipulate($manipulations);
        }
        if (isset($manipulations['srcset'])) {
            $args = [];
            // range scaler
            if (!empty($manipulations['srcset']['min']) && !empty($manipulations['srcset']['max'])) {
                $args = array_values($manipulations['srcset']);
            // sizes scaler
            } elseif (is_array($manipulations['srcset'])) {
                $args = [$manipulations['srcset']];
            }
            \call_user_func_array([$this, 'srcset'], $args);
        }

        if (!empty($manipulations['datauri'])) {
            $this->setHasDataUri(true);
        }

        unset($manipulations['srcset'], $manipulations['datauri']);

        if (\is_array($manipulations)) {
            $manipulations = new Manipulations([$manipulations]);
        }
        parent::manipulate($manipulations);

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    private function resolveImagePath() :string
    {
        // Store original provided path and don't modify it
        if (!$this->originalImagePath) {
            $this->originalImagePath = $this->pathToImage;
        }

        $imagePath = $this->originalImagePath;
        if ($this->filesystem->isAbsolutePath($imagePath)) {
            if (false === strpos($imagePath, $this->getSourcePath())) {
                return pathinfo($imagePath, PATHINFO_BASENAME);
            }
            // Image is in source path, make it relative
            $imagePath = ltrim(str_replace($this->getSourcePath(), '', $imagePath), '/');
        }

        $this->pathToImage = $this->getSourcePath().'/'.$imagePath;

        return $imagePath;
    }

    /**
     * Get cache filename
     *
     * @param string $imagePath Relative path to image
     *
     * @return string
     */
    private function getCacheFilename($imagePath) :string
    {
        $parts = [
            \pathinfo($imagePath, PATHINFO_FILENAME),
        ];

        // Filename suffix
        $suffix = '';
        if (
            $this->manipulations->hasManipulation('width') ||
            $this->manipulations->hasManipulation('height')
        ) {
            $suffix .= \sprintf('%dx%d', $this->manipulations->getManipulationArgument('width') ?? 0, $this->manipulations->getManipulationArgument('height') ?? 0);
        }
        if ($this->manipulations->hasManipulation('devicePixelRatio')) {
            $suffix .= \sprintf('@%dx', $this->manipulations->getManipulationArgument('devicePixelRatio'));
        }

        $parts[] = $suffix;

        // Sort manipulations to avoid different hash for identical manipulations
        $manipulations = array_map(function ($m) {
            ksort($m);
            return $m;
        }, $this->manipulations->toArray());

        // Create a unique hash based on file path and manipulations
        // @todo if file is an absolute path, rebased -> conflict
        $parts[] = \substr(\md5(\json_encode($manipulations).$imagePath), 0, 8);

        $extension = \pathinfo($imagePath, PATHINFO_EXTENSION);

        // Change output extension
        if ($this->manipulations->hasManipulation('format')) {
            $extension = $this->manipulations->getManipulationArgument('format');
        }

        return implode('-', array_filter($parts)) . '.' . $extension;
    }

    /**
     * Get cache file path
     *
     * @return string
     */
    private function getCacheFilePath() :string
    {
        // Per image optimize parameter
        if ($this->manipulations->hasManipulation('optimize')) {
            $optimize = (bool) $this->manipulations->getFirstManipulationArgument('optimize');
            $this->setOptimize($optimize);
            if (!$optimize) {
                $this->manipulations->removeManipulation('optimize');
            }
        }

        // Optimize
        if ($this->getOptimize()) {
            $this->optimize($this->getOptimizationOptions());
        }

        // Resolve image path
        $imagePath = $this->resolveImagePath();

        $dirname = \pathinfo($imagePath, PATHINFO_DIRNAME);
        $dirname = '.' === $dirname ? '' : $dirname;

        if (!empty($dirname)) {
            $dirname = $this->getRebase() ? '' : $dirname . '/';
        }

        return $this->getCachePath() . '/' . $dirname . $this->getCacheFilename($imagePath);
    }

    /**
     * Resolve URL
     *
     * @param string $imageCachePath
     *
     * @return string
     */
    private function resolveUrl($imageCachePath) :string
    {
        $imageRelativeUrl = str_replace($this->getPublicPath(), '', $imageCachePath);
        return $this->getBaseUrl() ? $this->getBaseUrl() . $imageRelativeUrl : $imageRelativeUrl;
    }

    /**
     * Validate manipulations
     *
     * @return void
     */
    private function validateManipulations()
    {
        if (
            $this->getHasSrcset()
            && $this->getHasDataUri()
        ) {
            throw new \Exception('srcset and datauri can’t be used together');
        }
    }

    /**
     * Generate image
     *
     * @param string $imageCachePath
     *
     * @return string
     */
    public function generateImage(string $imageCachePath = '') :string
    {
        // Prevent datauri / srcset together
        $this->validateManipulations();

        // Get cached file path
        $imageCachePath = $imageCachePath ?: $this->getCacheFilePath();

        // Cache image exists
        if ($this->filesystem->exists($imageCachePath)) {
            return $imageCachePath;
        }

        // Raise memory limit
        // @todo make setting configurable
        // increase if larger than current setting
        // if( \ini_get('memory_limit') < $this->getMaxMemoryLimit() ) {
        // \ini_set('memory_limit', '512M');
        // }

        // Create directory if missing
        $cacheDir = \pathinfo($imageCachePath, PATHINFO_DIRNAME);
        if (!$this->filesystem->exists($cacheDir)) {
            $this->filesystem->mkdir($cacheDir);
        }

        // Create manipulated image
        try {
            parent::save($imageCachePath);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $imageCachePath;
    }

    /**
     * Set source path
     *
     * @param string $sourcePath
     *
     * @return ResponsiveImage
     */
    public function setSourcePath($sourcePath) :ResponsiveImage
    {
        $this->sourcePath = \rtrim($sourcePath, '/');

        return $this;
    }

    /**
     * Get source path
     *
     * @return string $sourcePath
     */
    public function getSourcePath() :string
    {
        return $this->sourcePath;
    }

    /**
     * Set cache path
     *
     * @param string $cachePath
     *
     * @return ResponsiveImage
     */
    public function setCachePath($cachePath) :ResponsiveImage
    {
        $this->cachePath = \rtrim($cachePath, '/');

        return $this;
    }

    /**
     * Get cache path
     *
     * @return string $cachePath
     */
    public function getCachePath() :string
    {
        return $this->cachePath;
    }

    /**
     * Set public path
     *
     * @param string $publicPath
     *
     * @return ResponsiveImage
     */
    public function setPublicPath($publicPath) :ResponsiveImage
    {
        $this->publicPath = \rtrim($publicPath, '/');

        return $this;
    }

    /**
     * Get public path
     *
     * @return string $publicPath
     */
    public function getPublicPath() :string
    {
        return $this->publicPath;
    }

    /**
     * Set rebase
     *
     * @param bool $rebase
     *
     * @return ResponsiveImage
     */
    public function setRebase($rebase) :ResponsiveImage
    {
        $this->rebase = $rebase;

        return $this;
    }

    /**
     * Get rebase
     *
     * @return bool
     */
    public function getRebase() :bool
    {
        return $this->rebase;
    }

    /**
     * Set optimize
     *
     * @param bool  $optimize
     *
     * @return ResponsiveImage
     */
    public function setOptimize($optimize) :ResponsiveImage
    {
        $this->optimize = $optimize;

        return $this;
    }

    /**
     * Get optimize
     *
     * @return bool
     */
    public function getOptimize() :bool
    {
        return $this->optimize;
    }

    /**
     * Set optimization options
     *
     * @see https://docs.spatie.be/image/v1/image-manipulations/optimizing-images/
     *
     * @return ResponsiveImage
     */
    public function setOptimizationOptions(array $options)
    {
        $this->optimizationOptions = $options;

        return $this;
    }

    /**
     * Get optimization options
     *
     * @return array
     */
    public function getOptimizationOptions() :array
    {
        return $this->optimizationOptions;
    }

    /**
     * Set base URL
     *
     * @param string $baseUrl
     *
     * @return ResponsiveImage
     */
    public function setBaseUrl($baseUrl) :ResponsiveImage
    {
        $this->baseUrl = \rtrim($baseUrl, '/');

        return $this;
    }

    /**
     * Get base URL
     *
     * @return string $baseUrl
     */
    public function getBaseUrl() :string
    {
        return $this->baseUrl;
    }

    /**
     * Set scaler
     *
     * @param string $scaler
     *
     * @return ResponsiveImage
     */
    public function setScaler($scaler) :ResponsiveImage
    {
        switch ($scaler) {
            case 'range':
                $scaler = new RangeScaler();
                $scaler->setMinWidth($this->getMinWidth());
                $scaler->setMaxWidth($this->getMaxWidth());
                $scaler->setStep($this->getStep());
                break;
            case 'sizes':
                $scaler = new SizesScaler();
                $scaler->setSizes($this->getSizes());
                break;
        }

        $this->scaler = $scaler;

        return $this;
    }

    /**
     * Get scaler
     *
     * @return ScalerInterface
     */
    public function getScaler() :ScalerInterface
    {
        return $this->scaler;
    }

    /**
     * Set min width
     *
     * @param int $minWidth
     *
     * @return ResponsiveImage
     */
    public function setMinWidth(int $minWidth) :ResponsiveImage
    {
        $this->minWidth = $minWidth;

        return $this;
    }

    /**
     * Get min width
     *
     * @return int $minWidth
     */
    public function getMinWidth() :int
    {
        return $this->minWidth;
    }

    /**
     * Set max width
     *
     * @param int $maxWidth
     * @return ResponsiveImage
     */
    public function setMaxWidth(int $maxWidth) :ResponsiveImage
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    /**
     * Get max width
     *
     * @return int $maxWidth
     */
    public function getMaxWidth() :int
    {
        return $this->maxWidth;
    }

    /**
     * Set step
     *
     * @param int $step
     *
     * @return ResponsiveImage
     */
    public function setStep(int $step) :ResponsiveImage
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return int $step
     */
    public function getStep() :int
    {
        return $this->step;
    }

    /**
     * Set sizes
     *
     * @param array $sizes
     *
     * @return ResponsiveImage
     */
    public function setSizes(array $sizes) :ResponsiveImage
    {
        $this->sizes = $sizes;

        return $this;
    }

    /**
     * Get sizes
     *
     * @return array $sizes
     */
    public function getSizes() :array
    {
        return $this->sizes;
    }

    /**
     * Set max memory limit
     *
     * @param string $limit
     *
     * @return ResponsiveImage
     */
    public function setMaxMemoryLimit($limit) :ResponsiveImage
    {
        $this->maxMemoryLimit = $limit;

        return $this;
    }

    /**
     * Get base URL.
     *
     * @return string $baseUrl
     */
    public function getMaxMemoryLimit() :string
    {
        return $this->maxMemoryLimit;
    }

    /**
     * Set datauri
     *
     * @param bool $hasDataUri
     *
     * @return ResponsiveImage
     */
    public function setHasDataUri(bool $hasDataUri) :ResponsiveImage
    {
        $this->hasDataUri = $hasDataUri;

        return $this;
    }

    /**
     * Get datauri
     *
     * @return bool
     */
    public function getHasDataUri() :bool
    {
        return $this->hasDataUri;
    }

    /**
     * Enable datauri
     *
     * @return ResponsiveImage
     */
    public function datauri() :ResponsiveImage
    {
        $this->setHasDataUri(true);

        return $this;
    }

    /**
     * Set has srcset
     *
     * @param bool $bool
     *
     * @return ResponsiveImage
     */
    public function setHasSrcset(bool $bool) :ResponsiveImage
    {
        $this->hasSrcSet = $bool;

        return $this;
    }

    /**
     * Get has srcset
     *
     * @return bool
     */
    public function getHasSrcset() :bool
    {
        return $this->hasSrcSet;
    }

    /**
     * Set batch
     *
     * @param int $batch
     *
     * @return ResponsiveImage
     */
    public function setBatch(int $batch) :ResponsiveImage
    {
        $this->batch = $batch;

        return $this;
    }

    /**
     * Get batch
     *
     * @return int
     */
    public function getBatch() :int
    {
        return $this->batch;
    }

    /**
     * Get base64 encoded image datauri
     *
     * @param string $path
     *
     * @return string
     */
    private function getBase64($path) :string
    {
        $data = \file_get_contents($path);
        if (!$data) {
            // @todo throw
            return '';
        }

        $mimeTypes = new MimeTypes();
        $mimes = $mimeTypes->getMimeTypes(\pathinfo($path, PATHINFO_EXTENSION));

        return $this->formatDataUri($data, $mimes[0]);
    }

    /**
     * Creates a data URI (RFC 2397).
     *
     * @return string The generated data URI
     */
    private function formatDataUri(string $data, string $mime, array $parameters = []): string
    {
        $repr = 'data:';
        $repr .= $mime;

        foreach ($parameters as $key => $value) {
            $repr .= ';'.$key.'='.\rawurlencode($value);
        }

        if (0 === \strpos($mime, 'text/')) {
            $repr .= ','.\rawurlencode($data);
        } else {
            $repr .= ';base64,'.\base64_encode($data);
        }

        return $repr;
    }

    /**
     * @param int $width
     * @param int $height
     * @param string $cropMethod
     *
     * @return ResponsiveImage
     *
     * @throws InvalidManipulation
     */
    public function crop(int $width, int $height, string $cropMethod = Manipulations::CROP_CENTER) :ResponsiveImage
    {
        return parent::crop($cropMethod, $width, $height);
    }

    /**
     * @param int $width
     * @param int $height
     * @param string $fitMethod
     *
     * @return ResponsiveImage
     *
     * @throws InvalidManipulation
     */
    public function fit(int $width, int $height, string $fitMethod = Manipulations::FIT_FILL) :ResponsiveImage
    {
        return parent::fit($fitMethod, $width, $height);
    }

    /**
     * @inheritDoc
     */
    protected function performOptimization($path, array $optimizerChainConfiguration)
    {
        $optimizerChain = OptimizerChainFactory::create();

        $optimizers = $this->getOptimizationOptions();
        if (!empty($optimizers)) {
            $optimizerChain->setOptimizers($optimizers);
        }

        $optimizerChain->optimize($path);
    }

    /**
     * @inheritDoc
     */
    public function save($outputPath = '')
    {
        if ($outputPath == '') {
            $outputPath = $this->pathToImage;
        }

        $this->addFormatManipulation($outputPath);

        $glideConversion = GlideConversion::create($this->pathToImage)->useImageDriver($this->imageDriver);

        if (! is_null($this->temporaryDirectory)) {
            $glideConversion->setTemporaryDirectory($this->temporaryDirectory);
        }

        $glideConversion->performManipulations($this->manipulations);

        $glideConversion->save($outputPath);

        if ($this->shouldOptimize()) {
            $this->performOptimization($outputPath, []);
        }
    }
}
