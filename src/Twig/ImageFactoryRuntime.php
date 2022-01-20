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

    public function apply(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function background(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function blur(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function border(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function brightness(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function contrast(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function crop(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function device_pixel_ratio(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function fit(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function flip(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function focal_crop(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function gamma(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function greyscale(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function height(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function manipulate(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function manual_crop(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function optimize(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function orientation(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function pixelate(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function quality(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function sepia(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function sharpen(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function to(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_fit(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_height(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_opacity(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_padding(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_position(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_width(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function width(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function widths(string $source, array $args = []): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }
}
