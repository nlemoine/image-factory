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
}
