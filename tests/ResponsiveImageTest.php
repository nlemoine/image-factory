<?php

namespace HelloNico\ImageFactory\Test;

use HelloNico\ImageFactory\ResponsiveImage;
use HelloNico\ImageFactory\Factory;

class ResponsiveImageTest extends TestCase
{

    /** @test */
    // public function is_can_convert_from_jpeg_to_avif()
    // {
    //     $image = $this->getFactory()->create($this->getTestJpg());
    //     $image->format('avif');

    //     $target = $image->generateImage();
    //     $this->assertFileExists($target);
    // }

    /** @test */
    public function it_can_modify_an_image_using_manipulations()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->width(500);

        $target = $image->generateImage();
        $size = getimagesize($target);

        $this->assertFileExists($target);
        $this->assertEquals(500, $size[0]);
    }

    /** @test */
    public function it_can_crop_an_image_with_reordered_parameters()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->crop(500, 200);

        $target = $image->generateImage();
        $size = getimagesize($target);

        $this->assertFileExists($target);
        $this->assertEquals(500, $size[0]);
        $this->assertEquals(200, $size[1]);
    }

    /** @test */
    public function it_can_fit_an_image_with_reordered_parameters()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->fit(500, 200);

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
    public function it_handles_multiple_optimize_calls()
    {
        $factory = $this->getFactory();
        $image = $factory->create($this->getTestJpg());
        $image->width(100)->optimize()->apply()->blur(5);

        $image2 = $factory->create($this->getTestJpg());
        $image2->width(100)->apply()->blur(5)->optimize();

        $this->assertEquals($image->generateImage(), $image2->generateImage());
    }

    /** @test */
    public function it_can_generate_datauri()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->width(1)->datauri(true);

        $data = $image->getSrc();

        $this->assertTrue(false !== strpos($data, 'data:image/jpeg'));
    }

    /** @test */
    public function it_can_rebase_path()
    {
        $image = $this->getFactory(['rebase' => true])->create('10/image.jpg');
        $image->width(200)->datauri(true);

        $target = $image->generateImage();

        $this->assertEquals($image->getCachePath(), pathinfo($target, PATHINFO_DIRNAME));
    }

    /** @test */
    public function it_can_handle_special_characters()
    {
        $image = $this->getFactory()->create('11/éimage.jpg');
        $image->width(200);

        $target = $image->generateImage();
        $this->assertSame('é', mb_substr(pathinfo($target, PATHINFO_BASENAME), 0, 1));
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
    public function it_can_generate_srcset_and_keep_folders()
    {
        $image = $this->getFactory(['batch' => 0])->create('10/image.jpg');

        $image->srcset([100, 200, 300, 400]);
        $targets = $image->getSrcSetSources();

        foreach ($targets as $target) {
            $this->assertEquals($image->getCachePath() . '/10', pathinfo($target, PATHINFO_DIRNAME));
        }
    }

    /** @test */
    public function it_can_generate_srcset_by_batch()
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
    public function it_can_generate_srcset_starting_by_highest_width()
    {
        $image = $this->getFactory(['batch' => 2])->create($this->getTestJpg());

        $image->srcset([100, 200, 300, 400, 500]);
        $targets = $image->getSrcSetSources();
        reset($targets);
        $this->assertEquals(key($targets), '500');
    }

    /** @test */
    public function it_can_prevent_srcset_and_datauri()
    {
        $this->expectException(\Exception::class);

        $image = $this->getFactory()->create($this->getTestJpg());
        $image->srcset()->datauri();

        $image->generateImage();
    }

}
