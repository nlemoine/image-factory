<?php

namespace HelloNico\ImageFactory\Twig;

use HelloNico\ImageFactory\Factory;
use HelloNico\ImageFactory\ResponsiveImage;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageFactoryExtension extends AbstractExtension
{
    private $factory;
    private $manipulations;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
        $this->manipulations = $this->getManipulations();
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
        $image = $arguments[0];
        if (is_string($image)) {
            $image = $this->factory->create($arguments[0]);
        }
        if (!$image instanceof ResponsiveImage) {
            throw new \Exception('You must pass a string or a ResponsiveImage object');
        }

        $args = isset($arguments[1]) ? $arguments[1] : [];

        if ('manipulate' === $name) {
            $image->{$name}($args);
        } else {
            $image->{$name}(...$args);
        }

        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        $filters = \array_map(function ($manipulation) {
            return new TwigFilter(
                $manipulation,
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

    /**
     * Get manipulations
     *
     * @return array
     */
    private function getManipulations() :array
    {
        // Get a list of manipulations
        $manipulations_class = new \ReflectionClass(\Spatie\Image\Manipulations::class);
        $manipulations = array_map(function (\ReflectionMethod $method) {
            $method_name = $method->getName();

            // `format` is a native Twig filter
            return 'format' === $method_name ? 'to' : $method_name;
        }, $manipulations_class->getMethods(\ReflectionMethod::IS_PUBLIC));

        // Exclude non manipulations methods
        $excludes = [
            '__construct',
            'create',
            'toArray',
            'removeManipulation',
            'hasManipulation',
            'getManipulationArgument',
            'mergeManipulations',
            'getManipulationSequence',
            'isEmpty',
            'getFirstManipulationArgument'
        ];
        $manipulations = array_filter($manipulations, function ($method) use ($excludes) {
            return !in_array($method, $excludes, true);
        });

        // Add missing manipulations
        return array_merge($manipulations, [
            'manipulate',
            'datauri',
            'srcset'
        ]);
    }
}
