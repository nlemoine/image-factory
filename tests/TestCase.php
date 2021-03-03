<?php

namespace HelloNico\ImageFactory\Test;

use HelloNico\ImageFactory\Factory;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class TestCase extends PHPUnitTestCase
{
    protected $factory;

    protected function setUp(): void
    {
        $filesystem = new Filesystem();
        $cachePath = __DIR__ . '/images/cache';
        try {
            $filesystem->remove($cachePath);
            $filesystem->mkdir($cachePath);
        } catch (IOExceptionInterface $exception) {
        }
    }

    protected function getFactory(array $parameters = [])
    {
        return new Factory(array_merge([
            'sourcePath'   => $this->getSourceTestFolder(),
            'cachePath'    => $this->getCacheTestFolder(),
            'publicPath'    => $this->getPublicTestFolder(),
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

    /**
     * @param $object
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    protected function callMethod($object, string $method, array $parameters = [])
    {
        try {
            $className = get_class($object);
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new \Exception($e->getMessage());
        }

        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
