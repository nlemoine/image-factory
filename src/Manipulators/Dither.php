<?php

namespace HelloNico\ImageFactory\Manipulators;

use Intervention\Image\Image;
use League\Glide\Manipulators\BaseManipulator;

class Dither extends BaseManipulator
{
    public function run(Image $image)
    {
        if ($this->dither) {
            return $this->runDitheringFilter($image);
        }

        return $image;
    }

    protected function runDitheringFilter(Image $image)
    {
        $driver = \strtolower($image->getDriver()->getDriverName());
        if ($driver === 'gd') {
            return $this->ditherWithGd($image);
        } elseif ($driver === 'imagick') {
            return $this->ditherWithImagick($image);
        }
        return $image;
    }

    protected function ditherWithImagick(Image $image)
    {
        $image->greyscale();

        $size = $image->getSize();

        // We only need Red and Alpha channel
        $imagePixels = $image->getCore()->exportImagePixels(0, 0, $size->width, $size->height, 'RA', \Imagick::PIXEL_CHAR);

        $imagePixels = $this->ditherImage($imagePixels, $size->width);

        $imageLength = \count($imagePixels);
        $newImagePixels = [];
        for ($currentPixel = 0; $currentPixel <= $imageLength; $currentPixel += 2) {
            if (!isset($imagePixels[$currentPixel])) {
                continue;
            }
            $newImagePixels[] = $imagePixels[$currentPixel];
            $newImagePixels[] = $imagePixels[$currentPixel];
            $newImagePixels[] = $imagePixels[$currentPixel];
            $newImagePixels[] = $imagePixels[$currentPixel + 1];
        }

        $im = new \Imagick();
        $im->newImage($size->width, $size->height, 'white');

        $im->importImagePixels(0, 0, $size->width, $size->height, 'RGBA', \Imagick::PIXEL_CHAR, $newImagePixels);

        $image->setCore($im);

        return $image;
    }

    protected function ditherWithGd(Image $image)
    {
        $image->greyscale();

        $size = $image->getSize();
        $imageWidth = $size->width;

        // Create a array of pixels in a shape of array(
        //     Red,
        //     Alpha,
        //     R,
        //     A,
        //     ...
        // )
        $imagePixels = [];
        for ($y = 0; $y < $size->height; $y++) {
            for ($x = 0; $x < $size->width; $x++) {
                $color = \imagecolorat($image->getCore(), $x, $y);
                // Blue
                $imagePixels[] = $color & 0xFF;
                // Alpha
                $imagePixels[] = ($color >> 24) & 0xFF;
            }
        }

        $resource = \imagecreatetruecolor($size->width, $size->height);
        \imagealphablending($resource, false);
        \imagesavealpha($resource, true);

        $imageLength = \count($imagePixels);
        $imagePixels = $this->ditherImage($imagePixels, $imageWidth);

        $i = 0;
        for ($currentPixel = 0; $currentPixel <= $imageLength; $currentPixel += 2) {
            if (!isset($imagePixels[$currentPixel])) {
                continue;
            }
            $color = $imagePixels[$currentPixel];
            $alpha = $imagePixels[$currentPixel + 1];
            $pixel = \imagecolorallocatealpha($resource, $color, $color, $color, $alpha);
            $x = $i % $imageWidth;
            $y = \floor($i / $imageWidth);
            \imagesetpixel($resource, $x, $y, $pixel);
            $i++;
        }

        $image->setCore($resource);

        return $image;
    }

    /**
     * Dither image pixels with Atkinson Error Diffusion Kernel
     *
     * @param array $imagePixels
     * @param integer $imageWidth
     * @return array
     */
    protected function ditherImage(array $imagePixels, int $imageWidth): array
    {
        /**
         * Atkinson Error Diffusion Kernel:
         * 1/8 is 1/8 * quantization error.
         * +-------+-------+-------+-------+
         * |       | Curr. |  1/8  |  1/8  |
         * +-------|-------|-------|-------|
         * |  1/8  |  1/8  |  1/8  |       |
         * +-------|-------|-------|-------|
         * |       |  1/8  |       |       |
         * +-------+-------+-------+-------+
         */
        $imageLength = \count($imagePixels);
        for ($currentPixel = 0; $currentPixel <= $imageLength; $currentPixel += 2) {
            if (!isset($imagePixels[$currentPixel])) {
                continue;
            }
            if ($imagePixels[$currentPixel] <= 128) {
                $newPixelColor = 0;
            } else {
                $newPixelColor = 255;
            }
            $errorDiffusion = ($imagePixels[$currentPixel] - $newPixelColor) / 8;

            // Set new pixels color channels
            $imagePixels[$currentPixel] = $newPixelColor;

            // Error diffusion
            if (isset($imagePixels[$currentPixel + 2])) {
                $imagePixels[$currentPixel + 2] += $errorDiffusion;
            }
            if (isset($imagePixels[$currentPixel + 4])) {
                $imagePixels[$currentPixel + 4] += $errorDiffusion;
            }
            if (isset($imagePixels[$currentPixel + 2 * $imageWidth - 2])) {
                $imagePixels[$currentPixel + 2 * $imageWidth - 2] += $errorDiffusion;
            }
            if (isset($imagePixels[$currentPixel + 2 * $imageWidth])) {
                $imagePixels[$currentPixel + 2 * $imageWidth] += $errorDiffusion;
            }
            if (isset($imagePixels[$currentPixel + 2 * $imageWidth + 2])) {
                $imagePixels[$currentPixel + 2 * $imageWidth + 2] += $errorDiffusion;
            }
            if (isset($imagePixels[$currentPixel + 4 * $imageWidth])) {
                $imagePixels[$currentPixel + 4 * $imageWidth] += $errorDiffusion;
            }
        }

        return $imagePixels;
    }
}
