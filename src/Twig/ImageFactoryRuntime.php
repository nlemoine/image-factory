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

    public function apply(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function background(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function blur(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function border(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function brightness(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function contrast(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function crop(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function device_pixel_ratio(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function fit(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function flip(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function focal_crop(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function gamma(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function greyscale(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function height(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function manipulate(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function manual_crop(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function optimize(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function orientation(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function pixelate(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function quality(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function sepia(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function sharpen(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function to(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_fit(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_height(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_opacity(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_padding(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_position(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function watermark_width(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function width(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }

    public function widths(): ResponsiveImage
    {
        return $this->__call(__FUNCTION__, \func_get_args());
    }
}
