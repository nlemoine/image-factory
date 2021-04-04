<?php

namespace HelloNico\ImageFactory\Test;

use HelloNico\ImageFactory\ResponsiveImage;
use HelloNico\ImageFactory\Twig\ImageFactoryExtension;

/**
 * @internal
 * @coversNothing
 */
class ImageFactoryExtensionTest extends TestCase
{
    /** @test */
    // public function itReturnsAnImageInstance()
    // {
    //     $extension = new ImageFactoryExtension($this->getFactory());
    //     $twig = "{{ '{$this->getTestJpg()}'|devicePixelRatio(4) }}";
    //     $expected = ' class="main content"';

    //     dd($this->render($twig));

    //     // $this->assertInstanceOf(ResponsiveImage::class, $image);
    // }

    /** @test */
    public function itCanChainManipulations()
    {
        $extension = new ImageFactoryExtension($this->getFactory());
        $image = $extension->width($this->getTestJpg(), [200])->blur(40);

        $this->assertInstanceOf(ResponsiveImage::class, $image);
    }

    /** @test */
    public function itRewritesCamelCaseManipulations()
    {
        $extension = new ImageFactoryExtension($this->getFactory());
        $image = $extension->device_pixel_ratio($this->getTestJpg(), [2]);

        $this->assertEquals($image->getManipulationSequence()->getFirstManipulationArgument('devicePixelRatio'), 2);
    }

    /** @test */
    public function itPreventsFromCallingUnkonwnManipulation()
    {
        $this->expectException(\BadMethodCallException::class);

        $extension = new ImageFactoryExtension($this->getFactory());
        $extension->src('file');
    }
}
