<?php

namespace HelloNico\ImageFactory\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageFactoryExtension extends AbstractExtension
{
    private array $manipulations = [
        'apply',
        'background',
        'blur',
        'border',
        'brightness',
        'contrast',
        'crop',
        'device_pixel_ratio',
        'fit',
        'flip',
        'focal_crop',
        'gamma',
        'greyscale',
        'height',
        'manipulate',
        'manual_crop',
        'optimize',
        'orientation',
        'pixelate',
        'quality',
        'sepia',
        'dithering',
        'sharpen',
        'to',
        'watermark',
        'watermark_fit',
        'watermark_height',
        'watermark_opacity',
        'watermark_padding',
        'watermark_position',
        'watermark_width',
        'width',
        'widths',
    ];


    public function getFilters(): array
    {
        $filters = [];
        foreach ($this->manipulations as $manipulation) {
            $filters[] = new TwigFilter(
                $manipulation,
                [ImageFactoryRuntime::class, $manipulation],
                $manipulation !== 'manipulate' ? [
                    'is_variadic' => true,
                ] : []
            );
        }

        return $filters;
    }
}
