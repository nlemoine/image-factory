<?php

namespace HelloNico\ImageFactory\Test\Twig;

use HelloNico\ImageFactory\ResponsiveImage;
use HelloNico\ImageFactory\Twig\ImageFactoryExtension;
use HelloNico\ImageFactory\Test\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ImageFactoryExtensionTest extends TestCase
{
    /** @test */
    public function itCanChainManipulations()
    {
        $extension = new ImageFactoryExtension($this->getFactory());
        $image = $extension->width($this->getTestJpg(), [200])->blur(40);

        $this->assertInstanceOf(ResponsiveImage::class, $image);
    }

    /** @test */
    public function itRewritesManipulationsMethods()
    {
        $extension = new ImageFactoryExtension($this->getFactory());
        $image = $extension->device_pixel_ratio($this->getTestJpg(), [2]);
        $this->assertTrue($image->getManipulationSequence()->contains('devicePixelRatio'));

        $image = $extension->focal_crop($this->getTestJpg(), [200, 200, 200, 200]);
        $this->assertTrue($image->getManipulationSequence()->contains('crop'));

        $image = $extension->manual_crop($this->getTestJpg(), [200, 200, 200, 200]);
        $this->assertTrue($image->getManipulationSequence()->contains('manualCrop'));

        $image = $extension->watermark_width($this->getTestJpg(), [200]);
        $this->assertTrue($image->getManipulationSequence()->contains('watermarkWidth'));

        $image = $extension->watermark_height($this->getTestJpg(), [200]);
        $this->assertTrue($image->getManipulationSequence()->contains('watermarkHeight'));

        $image = $extension->watermark_fit($this->getTestJpg(), ['contain']);
        $this->assertTrue($image->getManipulationSequence()->contains('watermarkFit'));

        $image = $extension->watermark_padding($this->getTestJpg(), [10]);
        $this->assertTrue($image->getManipulationSequence()->contains('watermarkPaddingX'));

        $image = $extension->watermark_position($this->getTestJpg(), ['top']);
        $this->assertTrue($image->getManipulationSequence()->contains('watermarkPosition'));

        $image = $extension->watermark_opacity($this->getTestJpg(), [50]);
        $this->assertTrue($image->getManipulationSequence()->contains('watermarkOpacity'));
    }

    /** @test */
    public function itPreventsFromCallingUnkonwnManipulation()
    {
        $this->expectException(\BadMethodCallException::class);

        $extension = new ImageFactoryExtension($this->getFactory());
        $extension->src('file');
    }
}
