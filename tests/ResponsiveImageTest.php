<?php

namespace HelloNico\ImageFactory\Test;

use HelloNico\ImageFactory\ResponsiveImage;
use HelloNico\ImageFactory\Factory;

class ResponsiveImageTest extends TestCase
{

    /** @test */
    public function it_can_modify_an_image_using_manipulations()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->width(300);

        $target = $image->generateImage();

        $this->assertFileExists($target);
    }

    /** @test */
    public function it_can_crop_an_image_with_reordered_parameters()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->crop(300, 100);

        $target = $image->generateImage();

        $this->assertFileExists($target);
    }

    /** @test */
    public function it_can_fit_an_image_with_reordered_parameters()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->fit(300, 100);

        $target = $image->generateImage();

        $this->assertFileExists($target);
    }

    /** @test */
    public function it_does_not_care_about_manipulations_order()
    {
        $factory = $this->getFactory();
        $image = $factory->create($this->getTestJpg());
        $image->width(100)->blur(5);

        $image2 = $factory->create($this->getTestJpg());
        $image2->blur(5)->width(100);

        $this->assertEquals($image->generateImage(), $image2->generateImage());
    }

    /** @test */
    public function it_can_generate_datauri()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->width(200)->datauri(true);

        $target = $image->generateImage();
        $data = $image->getSrc();

        $target_data = $this->callMethod($image, 'getBase64', [$target]);

        $this->assertEquals($data, $target_data);
    }

    /** @test */
    public function it_can_rebase_path()
    {
        $image = $this->getFactory(['rebase' => true])->create('image.jpg');
        $image->width(200)->datauri(true);

        $target = $image->generateImage();

        $this->assertEquals($image->getCachePath(), pathinfo($target, PATHINFO_DIRNAME));
    }

    /** @test */
    public function it_can_add_base_url()
    {
        $image = $this->getFactory(['baseUrl' => 'https://example.com'])->create('image.jpg');

        $url = $image->getSrc();

        $this->assertEquals(parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST), 'https://example.com');
    }

    /** @test */
    public function it_can_handle_an_absolute_path()
    {
        $image = $this->getFactory()->create(__DIR__ . '/images/source/image.jpg');

        $target = $image->generateImage();

        $this->assertEquals($image->getCachePath(), pathinfo($target, PATHINFO_DIRNAME));
    }

    /** @test */
    public function it_can_handle_an_absolute_path_outside_source()
    {
        $image = $this->getFactory()->create(__DIR__ . '/images/image.jpg');

        $target = $image->generateImage();

        $this->assertEquals($image->getCachePath(), pathinfo($target, PATHINFO_DIRNAME));
    }

    /** @test */
    public function it_can_generate_srcset()
    {
        $image = $this->getFactory(['batch' => 0])->create($this->getTestJpg());

        $image->srcset([100, 200, 300, 400]);
        $targets = $image->getSrcSetSources();

        foreach ($targets as $target) {
            $this->assertFileExists($target);
        }
        $this->assertEquals(count($targets), 4);
    }

    /** @test */
    public function it_can_output_srcset_by_batch()
    {
        $image = $this->getFactory(['batch' => 2])->create($this->getTestJpg());

        $image->srcset([100, 200, 300, 400, 500]);
        $targets = $image->getSrcSetSources();

        // First batch
        foreach ($targets as $target) {
            $this->assertFileExists($target);
        }
        $this->assertEquals(count($targets), 2);

        // Second batch
        $targets = $image->getSrcSetSources();
        foreach ($targets as $target) {
            $this->assertFileExists($target);
        }
        $this->assertEquals(count($targets), 4);

        // Third batch
        $targets = $image->getSrcSetSources();
        foreach ($targets as $target) {
            $this->assertFileExists($target);
        }
        $this->assertEquals(count($targets), 5);
    }

    /** @test */
    public function it_can_prevent_srcset_and_datauri()
    {
        $this->expectException(\Exception::class);

        $image = $this->getFactory()->create($this->getTestJpg());
        $image->srcset()->datauri();

        $image->generateImage();
    }

    protected function assertImageType(string $filePath, $expectedType)
    {
        $expectedType = image_type_to_mime_type($expectedType);

        $type = image_type_to_mime_type(exif_imagetype($filePath));

        $this->assertTrue($expectedType === $type, "The file `{$filePath}` isn't an `{$expectedType}`, but an `{$type}`");
    }
}
