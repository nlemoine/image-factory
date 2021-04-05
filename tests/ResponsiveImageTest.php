<?php

namespace HelloNico\ImageFactory\Test;

use Spatie\Image\Manipulations;

/**
 * @internal
 * @coversNothing
 */
class ResponsiveImageTest extends TestCase
{
    /** @test */
    public function itCanUseACallableCustomFilename()
    {
        $image = $this->getFactory([
            'filenameFormat' => function (string $imagePath, string $filename, string $hash, Manipulations $manipulations) {
                return 'title';
            },
        ])->create($this->getTestJpg());
        $image->width(500)->format('jpg');
        $target = $image->generateImage();

        $this->assertEquals('title.jpg', \basename($target));
    }

    /** @test */
    public function itCanUseAStringCustomFilename()
    {
        $image = $this->getFactory([
            'filenameFormat' => '{name}-my-super-cool-seo-title',
        ])->create('image.jpg');
        $image->width(500)->format('jpg');
        $target = $image->generateImage();

        $this->assertEquals('image-my-super-cool-seo-title.jpg', \basename($target));
    }

    /** @test */
    public function itWillNormalizeFileExtension()
    {
        $image = $this->getFactory()->create('surfboards.JPEG');
        $target = $image->generateImage();

        $this->assertEquals('jpg', \pathinfo($target, PATHINFO_EXTENSION));
    }

    /** @test */
    public function itCanModifyAnImageUsingManipulations()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->width(500);

        $target = $image->generateImage();
        $size = \getimagesize($target);

        $this->assertFileExists($target);
        $this->assertEquals(500, $size[0]);
    }

    /** @test */
    public function itCanCropAnImageWithReorderedParameters()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->crop(500, 200);

        $target = $image->generateImage();
        $size = \getimagesize($target);

        $this->assertFileExists($target);
        $this->assertEquals(500, $size[0]);
        $this->assertEquals(200, $size[1]);
    }

    /** @test */
    public function itCanFitAnImageWithReorderedParameters()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->fit(500, 200);

        $target = $image->generateImage();

        $this->assertFileExists($target);
    }

    /** @test */
    public function itDoesNotCareAboutManipulationsOrder()
    {
        $factory = $this->getFactory();
        $image = $factory->create($this->getTestJpg());
        $image->width(100)->blur(5);

        $image2 = $factory->create($this->getTestJpg());
        $image2->blur(5)->width(100);

        $this->assertEquals($image->generateImage(), $image2->generateImage());
    }

    /** @test */
    public function itHandlesMultipleOptimizeCalls()
    {
        $factory = $this->getFactory();
        $image = $factory->create($this->getTestJpg());
        $image->width(100)->optimize()->apply()->blur(5);

        $image2 = $factory->create($this->getTestJpg());
        $image2->width(100)->apply()->blur(5)->optimize();

        $this->assertEquals($image->generateImage(), $image2->generateImage());
    }

    /** @test */
    public function itCanGenerateDatauri()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->width(1)->datauri(true);

        $data = $image->getSrc();

        $this->assertTrue(false !== \strpos($data, 'data:image/jpeg'));
    }

    /** @test */
    public function itCanRebasePath()
    {
        $image = $this->getFactory(['rebase' => true])->create('10/image.jpg');
        $image->width(200)->datauri(true);

        $target = $image->generateImage();

        $this->assertEquals($image->getCachePath(), \pathinfo($target, PATHINFO_DIRNAME));
    }

    /** @test */
    public function itCanHandleSpecialCharacters()
    {
        // @todo check again
        $image = $this->getFactory()->create('11/éimage.jpg');
        $image->width(200);

        $target = $image->generateImage();
        $this->assertSame('é', \mb_substr(\pathinfo($target, PATHINFO_BASENAME), 0, 1));
    }

    /** @test */
    public function itCanAddBaseUrl()
    {
        $image = $this->getFactory(['baseUrl' => 'https://example.com'])->create('image.jpg');

        $url = $image->getSrc();

        $this->assertEquals(\parse_url($url, PHP_URL_SCHEME).'://'.\parse_url($url, PHP_URL_HOST), 'https://example.com');
    }

    /** @test */
    public function itCanHandleAnAbsolutePath()
    {
        $image = $this->getFactory()->create(__DIR__.'/images/source/image.jpg');

        $target = $image->generateImage();

        $this->assertEquals($image->getCachePath(), \pathinfo($target, PATHINFO_DIRNAME));
    }

    /** @test */
    public function itCanHandleAnAbsolutePathOutsideSource()
    {
        $image = $this->getFactory()->create(__DIR__.'/images/image.jpg');

        $target = $image->generateImage();

        $this->assertEquals($image->getCachePath(), \pathinfo($target, PATHINFO_DIRNAME));
    }

    /** @test */
    public function itCanGenerateSrcset()
    {
        $image = $this->getFactory(['batch' => 0])->create($this->getTestJpg());

        $image->srcset([100, 200, 300, 400]);
        $targets = $image->getSrcSetSources();

        foreach ($targets as $target) {
            $this->assertFileExists($target);
        }
        $this->assertEquals(\count($targets), 4);
    }

    /** @test */
    public function itCanGenerateSrcsetAndKeepFolders()
    {
        $image = $this->getFactory(['batch' => 0])->create('10/image.jpg');

        $image->srcset([100, 200, 300, 400]);
        $targets = $image->getSrcSetSources();

        foreach ($targets as $target) {
            $this->assertEquals($image->getCachePath().'/10', \pathinfo($target, PATHINFO_DIRNAME));
        }
    }

    /** @test */
    public function itCanGenerateSrcsetByBatch()
    {
        $image = $this->getFactory(['batch' => 2])->create($this->getTestJpg());

        $image->srcset([100, 200, 300, 400, 500]);
        $targets = $image->getSrcSetSources();

        // First batch
        foreach ($targets as $target) {
            $this->assertFileExists($target);
        }
        $this->assertEquals(\count($targets), 2);

        // Second batch
        $targets = $image->getSrcSetSources();
        foreach ($targets as $target) {
            $this->assertFileExists($target);
        }
        $this->assertEquals(\count($targets), 4);

        // Third batch
        $targets = $image->getSrcSetSources();
        foreach ($targets as $target) {
            $this->assertFileExists($target);
        }
        $this->assertEquals(\count($targets), 5);
    }

    /** @test */
    public function itCanGenerateSrcsetStartingByHighestWidth()
    {
        $image = $this->getFactory(['batch' => 2])->create($this->getTestJpg());

        $image->srcset([100, 200, 300, 400, 500]);
        $targets = $image->getSrcSetSources();
        \reset($targets);
        $this->assertEquals(\key($targets), '500');
    }

    /** @test */
    public function itCanPreventSrcsetAndDatauri()
    {
        $this->expectException(\Exception::class);

        $image = $this->getFactory()->create($this->getTestJpg());
        $image->srcset()->datauri();

        $image->generateImage();
    }

    /** @test */
    public function isCanConvertFromJpegToAvif()
    {
        $image = $this->getFactory()->create($this->getTestJpg());
        $image->format('avif');

        $target = $image->generateImage();
        $this->assertFileExists($target);
    }

    /** @test */
    public function isCanConvertFromPngToAvif()
    {
        $image = $this->getFactory()->create('github.png');
        $image->format('avif');

        $target = $image->generateImage();
        $this->assertFileExists($target);
    }

    /** @test */
    public function isCanConvertFromGifToAvif()
    {
        $image = $this->getFactory()->create('github.gif');
        $image->format('avif');

        $target = $image->generateImage();
        $this->assertFileExists($target);
    }
}
