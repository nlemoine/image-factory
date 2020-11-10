<?php

namespace HelloNico\ImageFactory\Test;

use HelloNico\ImageFactory\ResponsiveImage;
use HelloNico\ImageFactory\Twig\ImageFactoryExtension;

class ImageFactoryExtensionTest extends TestCase
{

    /** @test */
    public function it_returns_an_image_instance() {
        $extension = new ImageFactoryExtension($this->getFactory());
        $image = $extension->width($this->getTestJpg(), [200]);

        $this->assertInstanceOf(ResponsiveImage::class, $image);
    }

    /** @test */
    public function it_can_chain_manipulations() {
        $extension = new ImageFactoryExtension($this->getFactory());
        $image = $extension->width($this->getTestJpg(), [200])->blur(40);

        $this->assertInstanceOf(ResponsiveImage::class, $image);
    }

    /** @test */
    public function it_prevents_from_calling_unkonwn_manipulation() {

        $this->expectException(\BadMethodCallException::class);

        $extension = new ImageFactoryExtension($this->getFactory());
        $extension->whoops('file');
    }


}
