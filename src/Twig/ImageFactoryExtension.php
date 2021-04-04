<?php

namespace HelloNico\ImageFactory\Twig;

use HelloNico\ImageFactory\Factory;
use HelloNico\ImageFactory\ResponsiveImage;
use Jawira\CaseConverter\Convert;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Spatie\Image\Exceptions\InvalidManipulation;

class ImageFactoryExtension extends AbstractExtension
{
    /**
     * Image factory.
     *
     * @var Factory
     */
    private $factory;

    /**
     * Allowed manipulations.
     *
     * @var array
     */
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

        $methodName = new Convert($name);

        if ('manipulate' === $name) {
            $image->{$name}($args);
        } else {
            $image->{$methodName->fromSnake()->toCamel()}(...$args);
        }

        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        $filters = \array_map(function (string $manipulation) {
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
     * Get manipulations.
     */
    private function getManipulations(): array
    {
        // Get a list of manipulations
        $manipulationsClass = new \ReflectionClass(\Spatie\Image\Manipulations::class);

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
            'getFirstManipulationArgument',
        ];

        // Exclude non manipulations methods
        $manipulations = \array_filter($manipulationsClass->getMethods(\ReflectionMethod::IS_PUBLIC), function (\ReflectionMethod $method) use ($excludes) {
            return !\in_array($method->getName(), $excludes, true);
        });

        $manipulations = \array_map(function (\ReflectionMethod $method) {
            $methodName = new Convert($method->getName());
            // `format` is a native Twig filter
            $methodSnake = $methodName->fromCamel()->toSnake();

            return 'format' === $methodSnake ? 'to' : $methodSnake;
        }, $manipulations);

        // Add missing manipulations
        return \array_merge($manipulations, [
            'manipulate',
            'datauri',
            'srcset',
        ]);
    }
}
