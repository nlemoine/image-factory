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
     * @param string $name
     *
     * @throws \InvalidArgumentException
     * @throws InvalidManipulation
     */
    public function __call($name, array $arguments = []): ResponsiveImage
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

    public function apply($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function background($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function blur($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function border($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function brightness($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function contrast($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function crop($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function device_pixel_ratio($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function fit($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function flip($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function focal_crop($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function gamma($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function greyscale($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function height($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function manipulate($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function manual_crop($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function optimize($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function orientation($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function pixelate($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function quality($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function sepia($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function sharpen($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function to($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function watermark($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function watermark_fit($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function watermark_height($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function watermark_opacity($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function watermark_padding($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function watermark_position($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function watermark_width($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function width($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function widths($source, array $args = []) {
        return $this->__call(__FUNCTION__, func_get_args());
    }

}
