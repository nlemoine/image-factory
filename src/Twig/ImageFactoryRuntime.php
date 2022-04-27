<?php

namespace HelloNico\ImageFactory\Twig;

use HelloNico\ImageFactory\Factory;
use HelloNico\ImageFactory\ResponsiveImage;
use Spatie\Image\Exceptions\InvalidManipulation;
use function Symfony\Component\String\u;
use Twig\Extension\RuntimeExtensionInterface;

class ImageFactoryRuntime implements RuntimeExtensionInterface
{
    /**
     * Image factory.
     */
    private Factory $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Call manipulation.
     *
     * @throws \InvalidArgumentException
     * @throws InvalidManipulation
     */
    public function __call(string $name, array $arguments = []): ResponsiveImage
    {
        $image = $arguments[0];

        if (\is_string($image)) {
            $image = $this->factory->create($arguments[0]);
        }
        if (!$image instanceof ResponsiveImage) {
            throw new \InvalidArgumentException(\sprintf('You must pass a string or a %s object', ResponsiveImage::class));
        }

        $args = isset($arguments[1]) ? $arguments[1] : [];

        if ('manipulate' === $name) {
            $image->manipulate($args);
        } else {
            $image->{(string) u($name)->camel()}(...$args);
        }

        return $image;
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function apply($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function background($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function blur($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function border($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function brightness($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function contrast($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function crop($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */

    /**
     * @param string|ResponsiveImage $source
     */
    public function device_pixel_ratio($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */

    /**
     * @param string|ResponsiveImage $source
     */
    public function fit($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function flip($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function focal_crop($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function gamma($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function greyscale($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function height($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function manipulate($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function manual_crop($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function optimize($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function orientation($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function pixelate($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function quality($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function sepia($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function sharpen($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function to($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function watermark($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function watermark_fit($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function watermark_height($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function watermark_opacity($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function watermark_padding($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function watermark_position($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function watermark_width($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function width($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|ResponsiveImage $source
     */
    public function widths($source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }
}
