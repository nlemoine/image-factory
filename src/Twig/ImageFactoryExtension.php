<?php

namespace HelloNico\ImageFactory\Twig;

use HelloNico\ImageFactory\Factory;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageFactoryExtension extends AbstractExtension
{
    private $factory;

    private $manipulations = [
        'width',
        'height',
        'blur',
        'pixelate',
        'crop',
        'manualCrop',
        'orientation',
        'flip',
        'fit',
        'devicePixelRatio',
        'brightness',
        'contrast',
        'gamma',
        'greyscale',
        'sepia',
        'sharpen',
        'filter',
        'background',
        'border',
        'quality',
        'format',
        'watermark',
        'watermarkWidth',
        'watermarkHeight',
        'watermarkFit',
        'watermarkPaddingX',
        'watermarkPaddingY',
        'watermarkPosition',
        'watermarkOpacity',
        'apply',
        'optimize',
        'srcset',
        'datauri',
    ];

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Call manipulation.
     *
     * @param string $name
     *
     * @return Image
     */
    public function __call($name, array $arguments = [])
    {
        $image = $this->factory->create($arguments[0]);
        $args = isset($arguments[1]) ? $arguments[1] : [];
        if ('manipulate' === $name) {
            $image = $image->{$name}($args);
        } else {
            $image = $image->{$name}(...$args);
        }

        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        $filters = \array_map(function ($manipulation) {
            // `format` is a native Twig filter
            $filter = 'format' === $manipulation ? 'to' : $manipulation;

            return new TwigFilter(
                $filter,
                [$this, $manipulation],
                ['is_variadic' => true]
            );
        }, $this->manipulations);

        $filters[] = new TwigFilter(
            'manipulate',
            [$this, 'manipulate']
        );

        return $filters;
    }
}
