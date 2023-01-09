<?php

namespace HelloNico\ImageFactory;

use HelloNico\ImageFactory\Manipulators\Dither;
use HelloNico\ImageFactory\Scaler\RangeScaler;
use HelloNico\ImageFactory\Scaler\ScalerInterface;
use HelloNico\ImageFactory\Scaler\SizesScaler;
use Intervention\Image\ImageManager;
use League\Glide\Api\Api;
use League\Glide\Manipulators;
use loophp\phposinfo\Enum\FamilyName;
use loophp\phposinfo\OsInfo;
use Spatie\Image\Exceptions\CouldNotConvert;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Image;
use Spatie\Image\Manipulations as SpatieManipulations;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ResponsiveImage extends Image
{
    /**
     * Relative image path.
     */
    private string $relativeImagePath;

    /**
     * Filesystem.
     */
    private Filesystem $filesystem;

    /**
     * Source path.
     */
    private string $sourcePath;

    /**
     * Cache path.
     */
    private string $cachePath;

    /**
     * Public path.
     */
    private string $publicPath;

    /**
     * Base URL.
     */
    private ?string $baseUrl = null;

    /**
     * Rebase.
     */
    private bool $rebase = false;

    /**
     * Max memory limit.
     */
    private ?string $maxMemoryLimit;

    /**
     * Max execution time.
     */
    private int $maxExecutionTime;

    /**
     * Optimize.
     */
    private bool $optimize;

    /**
     * Optimization options.
     */
    private array $optimizationOptions;

    /**
     * Has srcset.
     */
    private bool $hasSrcset = false;

    /**
     * Scaler.
     *
     * @var ScalerInterface|string
     */
    private $scaler;

    /**
     * Max width.
     */
    private int $scalerMinWidth;

    /**
     * Max width.
     */
    private int $scalerMaxWidth;

    /**
     * Step.
     */
    private int $scalerStep;

    /**
     * Sizes.
     */
    private array $scalerSizes = [];

    /**
     * Batch.
     */
    private int $batch = 3;

    /**
     * Filename format.
     *
     * @var callable|string|null
     */
    private $filenameFormat;

    public function __construct(
        string $pathToImage,
        string $sourcePath,
        string $cachePath,
        string $publicPath
    ) {
        $this->filesystem = new Filesystem();
        $this->manipulations = new Manipulations();
        $this->sourcePath = $sourcePath;
        $this->cachePath = $cachePath;
        $this->publicPath = $publicPath;
        $this->pathToImage = $this->resolveAbsoluteImageSourcePath($pathToImage);
        $this->relativeImagePath = $this->resolveRelativeImagePath();
    }

    /**
     * To string.
     */
    public function __toString(): string
    {
        return (string) $this->getSrc();
    }

    public function getManipulations(): SpatieManipulations
    {
        return $this->manipulations;
    }

    /**
     * Get src as base 64.
     */
    public function getSrcBase64(): string
    {
        return $this->getBase64($this->getSrcPath());
    }

    public function getImageUrl(): string
    {
        return $this->resolveUrl($this->pathToImage);
    }

    /**
     * Get src.
     *
     * @throws \Exception
     */
    public function getSrc(): string
    {
        if ($this->getExtension() === 'svg') {
            return $this->resolveUrl($this->pathToImage);
        }

        // Return URL
        return $this->resolveUrl($this->getSrcPath());
    }

    /**
     * Get src absolute path.
     */
    public function getSrcPath(): string
    {
        // throw error if missing public path / source path / cache path
        if (!$this->sourcePath || !$this->cachePath || !$this->publicPath) {
            throw new \Exception('Either publicPath, sourcePath or cachePath has not been set');
        }

        return $this->generateImage();
    }

    /**
     * Get srcset.
     */
    public function getSrcset(): ?string
    {
        if ($this->getExtension() === 'svg') {
            return $this->resolveUrl($this->pathToImage);
        }

        $srcset = $this->getSrcsetSources();

        if (empty($srcset)) {
            return null;
        }

        \ksort($srcset);

        return \implode(',', \array_map(function (int $width, string $path): string {
            return \sprintf('%s %dw', $this->resolveUrl($path), $width);
        }, \array_keys($srcset), $srcset));
    }

    /**
     * Get srcset sources.
     */
    public function getSrcsetSources(): array
    {
        if (!$this->getHasSrcset()) {
            $width = $this->manipulations->getManipulationArgument('width');
            if (empty($width)) {
                $width = $this->getWidth();
            }

            return [
                $width => $this->getSrc(),
            ];
        }

        $sizes = $this->scaler->scale();

        // Start with wider images
        \rsort($sizes);

        $originalWidth = $this->manipulations->getManipulationArgument('width');
        $originalHeight = $this->manipulations->getManipulationArgument('height');

        $srcset = [];
        $batch = $this->getBatch();

        foreach ($sizes as $i => $width) {
            if ($this->manipulations->hasManipulation('width')) {
                $this->manipulations->removeManipulation('width');
            }
            if ($this->manipulations->hasManipulation('height')) {
                $this->manipulations->removeManipulation('height');
            }

            // Set width
            $this->width($width);

            // Recalculate height for manipulations changing aspect ratio
            if (
                (
                    $this->manipulations->hasManipulation('crop') // crop / focalCrop
                    || $this->manipulations->hasManipulation('manualCrop')
                    || \in_array($this->manipulations->getManipulationArgument('fit'), [Manipulations::FIT_STRETCH, Manipulations::FIT_FILL, Manipulations::FIT_CROP, Manipulations::FIT_STRETCH], true)
                )
                && $originalWidth
                && $originalHeight
            ) {
                $height = \intval(\round(($originalHeight / $originalWidth) * $width));
                $this->height($height);
            }

            // If srcset is called only, if not rebased
            // @todo maybe check if pathToImage is absolute
            // $this->pathToImage = $this->resolveImageSourcePath($this->originalImagePath);

            $imageCachePath = $this->getImageCachePath();

            if ($batch) {
                $exists = $this->filesystem->exists($imageCachePath);
                if ($exists) {
                    ++$batch;
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
     * Set widths.
     */
    public function widths(): self
    {
        $args = \func_get_args();
        $argsCount = \count($args);
        $this->setHasSrcset(true);

        // Sizes scaler
        if ($argsCount === 1 && \is_array($args[0])) {
            $this->setScalerSizes($args[0]);
            $this->setScaler('sizes');
        }

        // Range scaler
        if ($argsCount === 2 || $argsCount === 3) {
            $minWidth = $args[0];
            $maxWidth = $args[1];

            if ($minWidth >= $maxWidth) {
                throw new \InvalidArgumentException(\sprintf('min width (%d) must be greater than max width (%d)', $minWidth, $maxWidth));
            }

            $this->setScalerMinWidth($minWidth);
            $this->setScalerMaxWidth($maxWidth);
            if (!empty($args[2])) {
                $this->setScalerStep($args[2]);
            }
            $this->setScaler('range');
        }

        return $this;
    }

    /**
     * Manipulate image.
     *
     * @param array|callable|Manipulations $manipulations
     */
    public function manipulate($manipulations): static
    {
        if (!\is_array($manipulations)) {
            parent::manipulate($manipulations);
        }
        if (isset($manipulations['widths'])) {
            $args = [];
            // range scaler
            if (!empty($manipulations['widths']['min']) && !empty($manipulations['widths']['max'])) {
                $args = \array_values($manipulations['widths']);
            // sizes scaler
            } elseif (\is_array($manipulations['widths'])) {
                $args = [$manipulations['widths']];
            }
            \call_user_func_array([$this, 'widths'], $args);
        }

        // Remove unknown manipulations
        unset($manipulations['widths']);

        if (\is_array($manipulations)) {
            $manipulations = new Manipulations([$manipulations]);
        }
        parent::manipulate($manipulations);

        return $this;
    }

    /**
     * Generate image.
     */
    public function generateImage(string $imageCachePath = ''): string
    {
        // Validate manipulations
        $this->validateManipulations();

        // Get cached file path
        $imageCachePath = $imageCachePath ?: $this->getImageCachePath();

        // Cache image exists
        if ($this->filesystem->exists($imageCachePath)) {
            return $imageCachePath;
        }

        // Increase memory limit
        $memoryLimit = \ini_get('memory_limit');
        $maxMemoryLimit = $this->getMaxMemoryLimit();
        if (
            $maxMemoryLimit
            && \intval($memoryLimit) !== -1
            && (
                ($memoryLimit === false || $memoryLimit === '')
                || ($this->parseByteSize($memoryLimit) < $this->parseByteSize($maxMemoryLimit))
            )
        ) {
            \ini_set('memory_limit', $maxMemoryLimit);
        }

        // Increase max_execution_time
        $max_execution_time = \ini_get('max_execution_time');
        if (
            ($max_execution_time === false || $max_execution_time === '')
            || (\intval($max_execution_time) > 0 && \intval($max_execution_time) < $this->getMaxExecutionTime())
        ) {
            \ini_set('max_execution_time', (string) $this->getMaxExecutionTime());
        }

        // Create directory if missing
        $cacheDir = \pathinfo($imageCachePath, PATHINFO_DIRNAME);
        if (!$this->filesystem->exists($cacheDir)) {
            $this->filesystem->mkdir($cacheDir);
        }

        // Create manipulated image
        try {
            $this->save($imageCachePath);
            return $imageCachePath;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Set source path.
     */
    public function setSourcePath(string $sourcePath): self
    {
        $this->sourcePath = \rtrim($sourcePath, '/');

        return $this;
    }

    /**
     * Get source path.
     */
    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    /**
     * Set cache path.
     */
    public function setCachePath(string $cachePath): self
    {
        $this->cachePath = \rtrim($cachePath, '/');

        return $this;
    }

    /**
     * Get cache path.
     */
    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    /**
     * Set public path.
     */
    public function setPublicPath(string $publicPath): self
    {
        $this->publicPath = \rtrim($publicPath, '/');

        return $this;
    }

    /**
     * Get public path.
     */
    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    /**
     * Set rebase.
     */
    public function setRebase(bool $rebase): self
    {
        $this->rebase = $rebase;

        return $this;
    }

    /**
     * Get rebase.
     */
    public function getRebase(): bool
    {
        return $this->rebase;
    }

    /**
     * Set optimize.
     */
    public function setOptimize(bool $optimize): self
    {
        $this->optimize = $optimize;

        return $this;
    }

    /**
     * Get optimize.
     */
    public function getOptimize(): bool
    {
        return $this->optimize;
    }

    /**
     * Set optimization options.
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
     * Get optimization options.
     */
    public function getOptimizationOptions(): array
    {
        return $this->optimizationOptions;
    }

    /**
     * Set base URL.
     */
    public function setBaseUrl(?string $baseUrl): self
    {
        $this->baseUrl = \is_string($baseUrl) ? \rtrim($baseUrl, '/') : $baseUrl;

        return $this;
    }

    /**
     * Get base URL.
     *
     * @return string
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * Set scaler.
     */
    public function setScaler(string $scaler): self
    {
        switch ($scaler) {
            case RangeScaler::NAME:
                $scaler = new RangeScaler();
                $scaler->setMinWidth($this->getScalerMinWidth());
                $scaler->setMaxWidth($this->getScalerMaxWidth());
                $scaler->setStep($this->getScalerStep());

                break;

            case SizesScaler::NAME:
                $scaler = new SizesScaler();
                $scaler->setSizes($this->getScalerSizes());

                break;
        }

        $this->scaler = $scaler;

        return $this;
    }

    /**
     * Get scaler.
     *
     * @return ScalerInterface|string
     */
    public function getScaler()
    {
        return $this->scaler;
    }

    /**
     * Set min width.
     */
    public function setScalerMinWidth(int $scalerMinWidth): self
    {
        $this->scalerMinWidth = $scalerMinWidth;

        return $this;
    }

    /**
     * Get min width.
     */
    public function getScalerMinWidth(): int
    {
        return $this->scalerMinWidth;
    }

    /**
     * Set max width.
     */
    public function setScalerMaxWidth(int $scalerMaxWidth): self
    {
        $this->scalerMaxWidth = $scalerMaxWidth;

        return $this;
    }

    /**
     * Get max width.
     */
    public function getScalerMaxWidth(): int
    {
        return $this->scalerMaxWidth;
    }

    /**
     * Set step.
     */
    public function setScalerStep(int $scalerStep): self
    {
        $this->scalerStep = $scalerStep;

        return $this;
    }

    /**
     * Get step.
     */
    public function getScalerStep(): int
    {
        return $this->scalerStep;
    }

    /**
     * Set sizes.
     */
    public function setScalerSizes(array $scalerSizes): self
    {
        $this->scalerSizes = $scalerSizes;

        return $this;
    }

    /**
     * Get sizes.
     */
    public function getScalerSizes(): array
    {
        return $this->scalerSizes;
    }

    /**
     * Set max memory limit.
     *
     * @param string $limit
     */
    public function setMaxMemoryLimit(?string $limit): self
    {
        $this->maxMemoryLimit = $limit;

        return $this;
    }

    /**
     * Get max memory limit.
     */
    public function getMaxMemoryLimit(): ?string
    {
        return $this->maxMemoryLimit;
    }

    /**
     * Set max execution time.
     */
    public function setMaxExecutionTime(int $maxExecutionTime): self
    {
        $this->maxExecutionTime = $maxExecutionTime;

        return $this;
    }

    /**
     * Get max execution time.
     *
     * @return int
     */
    public function getMaxExecutionTime(): ?int
    {
        return $this->maxExecutionTime;
    }

    /**
     * Set has srcset.
     */
    public function setHasSrcset(bool $bool): self
    {
        $this->hasSrcset = $bool;

        return $this;
    }

    /**
     * Get has srcset.
     */
    public function getHasSrcset(): bool
    {
        return $this->hasSrcset;
    }

    /**
     * Set batch.
     */
    public function setBatch(int $batch): self
    {
        $this->batch = $batch;

        return $this;
    }

    /**
     * Get batch.
     */
    public function getBatch(): int
    {
        return $this->batch;
    }

    /**
     * Set filename method.
     *
     * @param callable|string|null $filenameFormat
     */
    public function setFilenameFormat($filenameFormat): self
    {
        $this->filenameFormat = $filenameFormat;

        return $this;
    }

    /**
     * Get filename method.
     *
     * @return callable|string|null
     */
    public function getFilenameFormat()
    {
        return $this->filenameFormat;
    }

    public function save(string $outputPath = ''): void
    {
        if ($outputPath === '') {
            $outputPath = $this->pathToImage;
        }

        $this->addFormatManipulation($outputPath);

        $isAvif = $this->manipulations->getFirstManipulationArgument('format') === Manipulations::FORMAT_AVIF;
        $isAvifSupported = $this->isAvifSupported();

        if ($isAvif && !$isAvifSupported) {
            $this->manipulations->removeManipulation('format');
            $this->manipulations->removeManipulation('optimize');
            // AVIF will be converted with cavif
            $sourceExtension = \pathinfo($this->pathToImage, PATHINFO_EXTENSION);
            // cavif can't convert gif to avif
            if ($sourceExtension === Manipulations::FORMAT_GIF) {
                // Convert to PNG first
                $sourceExtension = Manipulations::FORMAT_PNG;
            }
            $outputPath .= '.' . $sourceExtension;
        }

        $imageManager = new ImageManager([
            'driver' => $this->imageDriver,
        ]);
        $glideApi = new Api(
            $imageManager,
            []
        );

        $image = $glideApi->getImageManager()->make($this->pathToImage);
        $imageData = $image->encode()->getEncoded();
        foreach ($this->manipulations->getManipulationSequence() as $manipulationGroup) {
            $glideParams = $this->prepareManipulations($manipulationGroup);
            $glideApi->setManipulators($this->getManipulators());
            $imageData = $glideApi->run(
                $imageData,
                $glideParams
            );
        }

        $this->filesystem->dumpFile($outputPath, $imageData);

        if ($this->shouldOptimize()) {
            $optimizerChainConfiguration = $this->manipulations->getFirstManipulationArgument('optimize');

            $optimizerChainConfiguration = \json_decode($optimizerChainConfiguration, true);

            $this->performOptimization($outputPath, $optimizerChainConfiguration);
        }

        if (!$isAvif) {
            return;
        }

        if ($this->isAvifSupported()) {
            return;
        }

        // Add avif format again, so srcset keeps the avif format
        $this->to(Manipulations::FORMAT_AVIF);

        // Convert to avif with the binary
        $imageCachePathAvif = \pathinfo($outputPath, PATHINFO_DIRNAME) . '/' . \pathinfo($outputPath, PATHINFO_FILENAME);
        // Settings from https://www.industrialempathy.com/posts/avif-webp-quality-settings/
        $args = [
            $this->getAvifBinaryPath(),
            '--quiet',
            '--overwrite',
            '--quality=56',
            '--speed=5',
            \sprintf('--output=%s', $imageCachePathAvif),
            $outputPath,
        ];

        $process = new Process($args);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $exception->getMessage();
        }

        // Delete source image
        @\unlink($outputPath);
    }

    /**
     * @throws InvalidManipulation
     */
    public function crop(int $width, int $height, string $cropMethod = Manipulations::CROP_CENTER): self
    {
        return parent::__call(__FUNCTION__, [$cropMethod, $width, $height]);
    }

    /**
     * @throws InvalidManipulation
     */
    public function fit(int $width, int $height, string $fitMethod = Manipulations::FIT_FILL): self
    {
        return parent::__call(__FUNCTION__, [$fitMethod, $width, $height]);
    }

    /**
     * @return $this
     */
    public function watermark(string $filePath)
    {
        return parent::__call(__FUNCTION__, $this->resolveAbsoluteImageSourcePath($filePath));
    }

    /**
     * Get binary path.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getAvifBinaryPath()
    {
        $binaryPath = [\dirname(__DIR__), 'bin'];

        $arch = \strtolower(OsInfo::arch());

        $binaryName = 'cavif';

        // TODO: throw exception if arch isn't 64 bits
        if (OsInfo::isFamily(FamilyName::LINUX)) {
            \array_push($binaryPath, 'linux', $binaryName);
        } elseif (OsInfo::isFamily(FamilyName::DARWIN)) {
            \array_push($binaryPath, 'macos', $binaryName);
        } elseif (OsInfo::isFamily(FamilyName::WINDOWS)) {
            \array_push($binaryPath, 'windows', $binaryName . '.exe');
        }

        $binaryPath = \implode(DIRECTORY_SEPARATOR, $binaryPath);

        if (!\is_file($binaryPath)) {
            throw new \Exception(\sprintf('No binary available for your system: %s %s', OsInfo::family(), $arch));
        }

        return $binaryPath;
    }

    /**
     * Alias for format.
     *
     * Required for usage in Twig since Twig already has `format` filter
     */
    public function to(string $format): self
    {
        $this->format($format);

        return $this;
    }

    /**
     * Determine if image aspect ratio will change given current manipulations.
     */
    public function aspectRatioWillChange(): bool
    {
        $originalWidth = $this->manipulations->getManipulationArgument('width');
        $originalHeight = $this->manipulations->getManipulationArgument('height');
        if (
            (
                $this->manipulations->hasManipulation('crop') // crop / focalCrop
                || $this->manipulations->hasManipulation('manualCrop')
                || \in_array($this->manipulations->getManipulationArgument('fit'), [Manipulations::FIT_STRETCH, Manipulations::FIT_FILL, Manipulations::FIT_CROP, Manipulations::FIT_STRETCH], true)
            )
            && $originalWidth
            && $originalHeight
        ) {
            return true;
        }

        return false;
    }

    public function getAspectRatio(): float
    {
        $manipulatedWidth = $this->manipulations->getManipulationArgument('width');
        $manipulatedHeight = $this->manipulations->getManipulationArgument('height');

        // Width and height are fixed
        if ($this->aspectRatioWillChange()) {
            return (int) $manipulatedWidth / (int) $manipulatedHeight;
        }

        // Width and height are not fixed
        return $this->getWidth() / $this->getHeight();
    }

    /**
     * Get target mime type.
     */
    public function getTargetMime(): string
    {
        $extension = $this->manipulations->getManipulationArgument('format');
        if (!$extension) {
            $extension = \pathinfo($this->pathToImage, PATHINFO_EXTENSION);
        }

        $extension = \str_replace('jpg', 'jpeg', \strtolower($extension));

        return \sprintf('image/%s', $extension);
    }

    /**
     * Get source extension.
     */
    public function getExtension(): string
    {
        return \pathinfo($this->pathToImage, PATHINFO_EXTENSION);
    }

    protected function addFormatManipulation($outputPath): void
    {
        if ($this->manipulations->hasManipulation('format')) {
            return;
        }

        // Prevent from adding format manipulation when input file is `jpeg`
        $inputExtension = \strtolower(\pathinfo($this->pathToImage, PATHINFO_EXTENSION));
        if ($inputExtension === 'jpeg') {
            $inputExtension = 'jpg';
        }
        $outputExtension = \strtolower(\pathinfo($outputPath, PATHINFO_EXTENSION));
        if ($inputExtension === $outputExtension) {
            return;
        }

        parent::addFormatManipulation($outputPath);
    }

    protected function getManipulators(): array
    {
        return [
            new Dither(),
            new Manipulators\Orientation(),
            new Manipulators\Crop(),
            new Manipulators\Size(),
            new Manipulators\Brightness(),
            new Manipulators\Contrast(),
            new Manipulators\Gamma(),
            new Manipulators\Sharpen(),
            new Manipulators\Filter(),
            new Manipulators\Flip(),
            new Manipulators\Blur(),
            new Manipulators\Pixelate(),
            // new Manipulators\Watermark($this->getWatermarks(), $this->getWatermarksPathPrefix() ?: ''),
            new Manipulators\Background(),
            new Manipulators\Border(),
            new Manipulators\Encode(),
        ];
    }

    /**
     * Check for native AVIF support.
     */
    protected function isAvifSupported(): bool
    {
        if ($this->imageDriver === 'gd') {
            return \function_exists('imagecreatefromavif');
        }
        // AVIF encoding does not work well in Imagick yet
        // https://github.com/ImageMagick/ImageMagick/issues/1432
        // } elseif($this->imageDriver === 'imagick') {
        //     return \class_exists('Imagick') && \Imagick::queryFormats('AVIF');
        // }
        return false;
    }

    private function prepareManipulations(array $manipulationGroup): array
    {
        $glideManipulations = [];

        foreach ($manipulationGroup as $name => $argument) {
            if ($name !== 'optimize') {
                $glideManipulations[$this->convertToGlideParameter($name)] = $argument;
            }
        }

        return $glideManipulations;
    }

    private function convertToGlideParameter(string $manipulationName): string
    {
        return match ($manipulationName) {
            'width'             => 'w',
            'height'            => 'h',
            'blur'              => 'blur',
            'pixelate'          => 'pixel',
            'dither'            => 'dither',
            'crop'              => 'fit',
            'manualCrop'        => 'crop',
            'orientation'       => 'or',
            'flip'              => 'flip',
            'fit'               => 'fit',
            'devicePixelRatio'  => 'dpr',
            'brightness'        => 'bri',
            'contrast'          => 'con',
            'gamma'             => 'gam',
            'sharpen'           => 'sharp',
            'filter'            => 'filt',
            'background'        => 'bg',
            'border'            => 'border',
            'quality'           => 'q',
            'format'            => 'fm',
            'watermark'         => 'mark',
            'watermarkWidth'    => 'markw',
            'watermarkHeight'   => 'markh',
            'watermarkFit'      => 'markfit',
            'watermarkPaddingX' => 'markx',
            'watermarkPaddingY' => 'marky',
            'watermarkPosition' => 'markpos',
            'watermarkOpacity'  => 'markalpha',
            default             => throw CouldNotConvert::unknownManipulation($manipulationName)
        };
    }

    /**
     * Get cache filename.
     *
     * @param string $relativeImagePath Relative path to image
     */
    private function getCacheFilename($relativeImagePath): string
    {
        // Get extension
        $extension = \pathinfo($relativeImagePath, PATHINFO_EXTENSION);
        // Normalize extension
        $extension = \str_replace('jpeg', 'jpg', \strtolower($extension));

        $extensionTarget = $this->manipulations->getManipulationArgument('format');

        if ($extensionTarget) {
            if ($extensionTarget === $extension) {
                // Avoid adding format manipulation to the hash if same as source
                $this->manipulations->removeManipulation('format');
            } else {
                $extension = $extensionTarget;
            }
        }

        // Sort manipulations to avoid different hash for identical manipulations
        $manipulations = \array_map(function ($m) {
            // Remove optimize, can be called multiple times but is executed once
            unset($m['optimize']);
            \ksort($m);

            return $m;
        }, $this->manipulations->toArray());

        // TODO: maybe remove optimize if avif
        $manipulations['optimize'] = $this->getOptimize();

        // Create a unique hash based on relative file path and manipulations
        // Important: use relative path to avoid hash changes when absolute folders change (e.g. different hosting/stage for example)
        // @todo if file is an absolute path, rebased -> conflict
        $hash = \substr(\md5(\json_encode($manipulations) . $relativeImagePath), 0, 8);

        $filename = \pathinfo($relativeImagePath, PATHINFO_FILENAME);

        $filenameFinal = null;

        // Custom filename as string
        if (\is_string($this->filenameFormat)) {
            $replacements = [
                'name' => $filename,
                'hash' => $hash,
            ];
            $filenameFinal = \str_replace(
                \array_map(function ($var) {
                    return \sprintf('{%s}', $var);
                }, \array_keys($replacements)),
                $replacements,
                $this->filenameFormat,
                $filename
            );
        }

        // Custom filename as callable
        if (\is_callable($this->filenameFormat)) {
            $filenameFinal = \call_user_func_array(
                $this->filenameFormat,
                [
                    $relativeImagePath,
                    $filename,
                    $hash,
                    $this->manipulations,
                ]
            );
        }

        $filenameFinal = $filenameFinal ?? $this->getDefaultCacheFilename($filename, $hash);

        return \sprintf('%s.%s', $filenameFinal, $extension);
    }

    /**
     * Get default cache file name.
     */
    private function getDefaultCacheFilename(string $filename, string $hash): string
    {
        $parts = [
            $filename,
            $hash,
        ];

        if (
            $this->manipulations->hasManipulation('width')
            || $this->manipulations->hasManipulation('height')
        ) {
            $parts[] = \sprintf('%dx%d', $this->manipulations->getManipulationArgument('width') ?? 0, $this->manipulations->getManipulationArgument('height') ?? 0);
        }

        return \implode('-', \array_filter($parts));
    }

    /**
     * Get cache file path.
     */
    private function getImageCachePath(): string
    {
        $dirname = \pathinfo($this->relativeImagePath, PATHINFO_DIRNAME);
        $dirname = $dirname === '.' ? '' : $dirname;

        if (!empty($dirname)) {
            $dirname = $this->getRebase() ? '' : $dirname . '/';
        }

        return $this->getCachePath() . '/' . $dirname . $this->getCacheFilename($this->relativeImagePath);
    }

    /**
     * Resolve relative image path.
     */
    private function resolveRelativeImagePath(): string
    {
        if (\strpos($this->pathToImage, $this->getSourcePath()) === false) {
            return \pathinfo($this->pathToImage, PATHINFO_BASENAME);
        }
        // Image is in source path, make it relative
        return \ltrim(\str_replace($this->getSourcePath(), '', $this->pathToImage), '/');
    }

    /**
     * Resolve absolute image source path.
     */
    private function resolveAbsoluteImageSourcePath(string $imagePath): string
    {
        if ($this->filesystem->isAbsolutePath($imagePath)) {
            return $imagePath;
        }

        return $this->getSourcePath() . '/' . $imagePath;
    }

    /**
     * Resolve URL.
     */
    private function resolveUrl(string $imageCachePath): string
    {
        $imageRelativeUrl = \str_replace($this->getPublicPath(), '', $imageCachePath);

        return $this->baseUrl ? $this->baseUrl . $imageRelativeUrl : $imageRelativeUrl;
    }

    /**
     * Validate manipulations.
     */
    private function validateManipulations(): void
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
    }

    /**
     * Get base64 encoded image datauri.
     *
     * @param string $path
     */
    private function getBase64($path): string
    {
        try {
            $data = \file_get_contents($path);
            if ($data === false) {
                throw new \Exception('Unable to get contents from file');
            }
        } catch (\Exception $e) {
            return $e->getMessage();
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
            $repr .= ';' . $key . '=' . \rawurlencode($value);
        }

        if (\strpos($mime, 'text/') === 0) {
            $repr .= ',' . \rawurlencode($data);
        } else {
            $repr .= ';base64,' . \base64_encode($data);
        }

        return $repr;
    }

    /**
     * Parses a given byte size.
     *
     * @param mixed $size
     *                    An integer or string size expressed as a number of bytes with optional SI
     *                    or IEC binary unit prefix (e.g. 2, 3K, 5MB, 10G, 6GiB, 8 bytes, 9mbytes).
     *
     * @return float
     */
    private function parseByteSize($size)
    {
        // Remove the non-unit characters from the size.
        $unit = \preg_replace('/[^bkmgtpezy]/i', '', $size);
        // Remove the non-numeric characters from the size.
        $size = \preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return \round($size * \pow(1024, (float) \stripos('bkmgtpezy', $unit[0])));
        }

        return \round((float) $size);
    }
}
