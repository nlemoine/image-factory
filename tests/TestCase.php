<?php

namespace HelloNico\ImageFactory\Test;

use HelloNico\ImageFactory\Factory;
use HelloNico\ImageFactory\Twig\ImageFactoryExtension;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

abstract class TestCase extends PHPUnitTestCase
{
    protected $factory;

    protected function setUp(): void
    {
        $filesystem = new Filesystem();
        $cachePath = __DIR__.'/images/cache';

        try {
            $filesystem->remove($cachePath);
            $filesystem->mkdir($cachePath);
        } catch (IOExceptionInterface $exception) {
        }
    }

    protected function getFactory(array $parameters = [])
    {
        return new Factory(\array_merge([
            'sourcePath'   => $this->getSourceTestFolder(),
            'cachePath'    => $this->getCacheTestFolder(),
            'publicPath'   => $this->getPublicTestFolder(),
        ], $parameters));
    }

    protected function getTestJpg(): string
    {
        return 'image.jpg';
    }

    protected function getSourceTestFolder(): string
    {
        return __DIR__.'/images/source';
    }

    protected function getCacheTestFolder(): string
    {
        return __DIR__.'/images/cache';
    }

    protected function getPublicTestFolder(): string
    {
        return __DIR__;
    }

    protected function render($template)
    {
        $loader = new ArrayLoader([
            'template' => $template,
        ]);
        $twig = new Environment($loader);
        $twig->addExtension(new ImageFactoryExtension($this->getFactory()));

        return $twig->render('template');
    }
}
