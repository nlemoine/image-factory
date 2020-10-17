<?php

namespace HelloNico\ImageFactory;

use HelloNico\ImageFactory\Scaler\WidthScaler;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;
use Spatie\Image\Exceptions\InvalidManipulation;

class ResponsiveImage extends Image
{
    private $srcset = [];
    private $imagesSet = [];
    private $scaler;
    private $sourcePath;
    private $cachePath;
    private $baseUrl;
    private $rebase;
    private $optimize;
    private $optimizationOptions;
    private $hasSrcSet = false;
    private $dataUri = false;
    private $originalImagePath;

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
    public function __toString()
    {
        if ($this->hasSrcSet) {
            $this->saveSrcSet();

            return $this->getSrcSet();
        }

        return $this->src();
    }

    /**
     * Return image src
     *
     * @return string
     */
    public function src()
    {
        $imagePath = $this->saveCache();
        // Return data uri
        if ($this->dataUri) {
            return $this->getBase64($imagePath);
        }
        // Return URL
        return $this->resolveUrl($imagePath);
    }

    /**
     * Manipulate image with an array.
     *
     * @param array|callable|Manipulations $manipulations
     */
    public function manipulate($manipulations)
    {
        if (isset($manipulations['srcset'])) {
            \call_user_func_array([$this, 'srcset'], $manipulations['srcset']);
            unset($manipulations['srcset']);
        }
        if (isset($manipulations['datauri'])) {
            $this->datauri();
            unset($manipulations['datauri']);
            // Remove srcset
            if (isset($manipulations['srcset'])) {
                unset($manipulations['srcset']);
            }
        }

        if (\is_array($manipulations)) {
            $manipulations = new Manipulations([$manipulations]);
        }
        parent::manipulate($manipulations);

        return $this;
    }

    /**
     * Undocumented function.
     *
     * @param [type] $mime
     * @param mixed  $bool
     */
    public function setDataUri($bool)
    {
        $this->dataUri = $bool;

        return $this;
    }

    public function datauri()
    {
        $this->setDataUri(true);

        return $this;
    }

    private function resolveImagePath()
    {

        // Store original provided path and don't modify it
        if (!$this->originalImagePath) {
            $this->originalImagePath = $this->pathToImage;
        }

        $imagePath = $this->originalImagePath;
        if ($this->filesystem->isAbsolutePath($imagePath)) {
            return pathinfo($imagePath, PATHINFO_BASENAME);
        }

        $this->pathToImage = $this->getSourcePath().'/'.$imagePath;

        return $imagePath;
    }

    private function getCacheFilename($imagePath)
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
        $parts[] = \substr(\md5(\json_encode($manipulations).$this->pathToImage), 0, 8);

        $extension = \pathinfo($imagePath, PATHINFO_EXTENSION);

        // Change output extension
        if ($this->manipulations->hasManipulation('format')) {
            $extension = $this->manipulations->getManipulationArgument('format');
        }

        return implode('-', array_filter($parts)) . '.' . $extension;
    }

    /**
     * Undocumented function
     *
     * @param string $imageRelativePath
     * @return string
     */
    private function getCacheFilePath($imagePath)
    {
        $dirname = \pathinfo($imagePath, PATHINFO_DIRNAME);
        $dirname = '.' === $dirname ? null : $dirname;

        if (!empty($dirname)) {
            $dirname = $this->getRebase() ? '' : $dirname . '/';
        }

        return $this->getCachePath() . '/' . $dirname . $this->getCacheFilename($imagePath);
    }

    /**
     * Resolve URL
     *
     * @param string $imageCachePath
     * @return string
     */
    private function resolveUrl($imageCachePath)
    {
        $imageRelativeUrl = str_replace($this->getPublicPath(), '', $imageCachePath);
        return $this->getBaseUrl() ? $this->getBaseUrl() . $imageRelativeUrl : $imageRelativeUrl;
    }

    /**
     * Undocumented function.
     *
     * @param string $return
     */
    private function saveCache($return = 'url')
    {

        // @todo sanitize manipulations
        // Prevent datauri / srcset both

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

        // Get cached file path
        $imageCachePath = $this->getCacheFilePath($imagePath);

        // Cache image exists
        if ($this->filesystem->exists($imageCachePath)) {
            return $imageCachePath;
        }

        // Create manipulated image

        // Raise memory limit
        // @todo make setting configurable
        \ini_set('memory_limit', '512M');

        // Create directory if missing
        $cacheDir = \pathinfo($imageCachePath, PATHINFO_DIRNAME);
        if (!$this->filesystem->exists($cacheDir)) {
            $this->filesystem->mkdir($cacheDir);
        }

        try {
            parent::save($imageCachePath);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $imageCachePath;
    }

    /**
     * Set source path.
     *
     * @param string $sourcePath
     */
    public function setSourcePath($sourcePath)
    {
        $this->sourcePath = \rtrim($sourcePath, '/');
    }

    /**
     * Get source path.
     *
     * @return string $sourcePath
     */
    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    /**
     * Set cache path.
     *
     * @param string $cachePath
     */
    public function setCachePath($cachePath)
    {
        $this->cachePath = \rtrim($cachePath, '/');
    }

    /**
     * Get cache path.
     *
     * @return string $cachePath
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * Set public path.
     *
     * @param string $publicPath
     */
    public function setPublicPath($publicPath)
    {
        $this->publicPath = \rtrim($publicPath, '/');
    }

    /**
     * Get public path.
     *
     * @return string $publicPath
     */
    public function getPublicPath()
    {
        return $this->publicPath;
    }

    /**
     * Set rebase.
     *
     * @param bool $rebase
     */
    public function setRebase($rebase)
    {
        $this->rebase = $rebase;
    }

    /**
     * Get rebase.
     *
     * @return bool
     */
    public function getRebase()
    {
        return $this->rebase;
    }

    /**
     * Set optimize.
     *
     * @param string $cachePath
     * @param mixed  $optimize
     */
    public function setOptimize($optimize)
    {
        $this->optimize = $optimize;
    }

    /**
     * Get optimize.
     *
     * @return bool
     */
    public function getOptimize()
    {
        return $this->optimize;
    }

    /**
     * Set optimization options.
     *
     * @see https://docs.spatie.be/image/v1/image-manipulations/optimizing-images/
     */
    public function setOptimizationOptions(array $options)
    {
        $this->optimizationOptions = $options;
    }

    /**
     * Get optimization options.
     *
     * @return array
     */
    public function getOptimizationOptions()
    {
        return $this->optimizationOptions;
    }

    /**
     * Set base URL.
     *
     * @param string $baseUrl
     */
    public function setbaseUrl($baseUrl)
    {
        $this->baseUrl = \rtrim($baseUrl, '/');
    }

    /**
     * Get base URL.
     *
     * @return string $baseUrl
     */
    public function getbaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get scaler.
     *
     * @return AbstractScaler
     */
    public function getScaler()
    {
        return $this->scaler;
    }

    /**
     * Set scaler.
     *
     * @param string $scaler
     *
     * @return AbstractScaler
     */
    public function setScaler($scaler)
    {
        switch ($scaler) {
            case 'width':
                $scaler = new WidthScaler($this);

                break;
        }
        $this->scaler = $scaler;
    }

    /**
     * Get base64 encoded image datauri
     */
    private function getBase64($path)
    {
        $data = \file_get_contents($path);
        if (!$data) {
            // @todo throw
            return;
        }

        $mimeTypes = new MimeTypes();
        $mimes = $mimeTypes->getMimeTypes(\pathinfo($path, PATHINFO_EXTENSION));

        return $this->formatDataUri($data, $mimes[0]);
    }

    /**
     * Creates a data URI (RFC 2397).
     *
     * Length validation is not perfomed on purpose, validation should
     * be done before calling this filter.
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
     * Undocumented function.
     *
     * @param int $minWidth
     * @param int $maxWidth
     * @param int    $step
     * @param string $scaler
     */
    public function srcset(
        int $minWidth,
        int $maxWidth,
        int $step = 100,
        $scaler = ''
    ) {
        $this->hasSrcSet = true;
        $this->scaler->setMinWidth($minWidth);
        $this->scaler->setMaxWidth($maxWidth);
        $this->scaler->setStepModifier($step);

        return $this;
    }

    /**
     * @param      $sources
     * @param null $value
     *
     * @return Image
     */
    public function addSource($sources, $value = null)
    {
        if (!\is_array($sources) && $value) {
            $sources = [$sources => $value];
        } elseif (!\is_array($sources)) {
            return $this;
        }

        foreach ($sources as $url => $width) {
            $width = \str_replace('px', '', $width);

            $this->srcset[(int) $width] = $url;
        }

        return $this;
    }

    /**
     * Undocumented function.
     */
    public function saveSrcSet()
    {
        $sizes = $this->scaler->scale($this);

        $original_width = $this->manipulations->getManipulationArgument('width');
        $original_height = $this->manipulations->getManipulationArgument('height');

        foreach ($sizes as $width) {
            if ($this->hasManipulation('width')) {
                $this->manipulations->removeManipulation('width');
            }
            if ($this->hasManipulation('height')) {
                $this->manipulations->removeManipulation('height');
            }

            // Set width
            $this->width($width);

            // Set height while keeping aspect ratio
            if ($this->hasManipulation('crop') && $original_width && $original_height) {
                $height = ($original_height / $original_width) * $width;
                $this->height($height);
            }

            $url = $this->saveCache();
            if ($url) {
                $this->addSource($url, $width);
            }
        }

        return $this;
    }

    public function getSrcSetUrls()
    {
        return array_values($this->srcset);
    }

    /**
     * Undocumented function.
     */
    public function getSrcSet()
    {
        $srcset = [];

        \ksort($this->srcset);

        foreach ($this->srcset as $w => $url) {
            $srcset[] = "{$url} {$w}w";
        }

        return \implode(',', $srcset);
    }

    /**
     * @param int $width
     * @param int $height
     * @param string $cropMethod
     *
     * @return $this
     *
     * @throws InvalidManipulation
     */
    public function crop(int $width, int $height, string $cropMethod = Manipulations::CROP_CENTER)
    {
        return parent::crop($cropMethod, $width, $height);
    }

    /**
     * @param int $width
     * @param int $height
     * @param string $fitMethod
     *
     * @return $this
     *
     * @throws InvalidManipulation
     */
    public function fit(int $width, int $height, string $fitMethod = Manipulations::FIT_CONTAIN)
    {
        return parent::fit($fitMethod, $width, $height);
    }
}
