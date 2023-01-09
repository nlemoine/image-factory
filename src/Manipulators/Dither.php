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
        $imagePixels = [];

        $pixels = $image->getCore()->exportImagePixels(0, 0, $size->width, $size->height, 'RGB', \Imagick::PIXEL_CHAR);
        $pixelsLength = \count($pixels);
        $j = 0;
        for ($i = 0; $i < $pixelsLength; $i += 3) {
            $x = $j % $size->width;
            $y = \floor($j / $size->width);
            $a = 0;
            $c = $pixels[$i];
            $imagePixels[$x][$y] = \intval(($a << 24) + ($c << 16) + ($c << 8) + $c);
            $j++;
        }
        unset($pixels);

        $draw = new \ImagickDraw();
        $white = new \ImagickPixel('#FFFFFF');
        for ($y = 0; $y < $size->height; $y++) {
            for ($x = 0; $x < $size->width; $x++) {
                $old = $imagePixels[$x][$y];
                if ($old > 0xffffff * .5) {
                    $new = 0xffffff;
                    $draw->setFillColor($white);
                    $draw->point($x, $y);
                } else {
                    $new = 0;
                }

                $quant_error = $old - $new;
                $error_diffusion = (1 / 8) * $quant_error;
                if (isset($imagePixels[$x + 1][$y])) {
                    $imagePixels[$x + 1][$y] += $error_diffusion;
                }
                if (isset($imagePixels[$x + 2][$y])) {
                    $imagePixels[$x + 2][$y] += $error_diffusion;
                }
                if (isset($imagePixels[$x - 1][$y + 1])) {
                    $imagePixels[$x - 1][$y + 1] += $error_diffusion;
                }
                if (isset($imagePixels[$x][$y + 1])) {
                    $imagePixels[$x][$y + 1] += $error_diffusion;
                }
                if (isset($imagePixels[$x + 1][$y + 1])) {
                    $imagePixels[$x + 1][$y + 1] += $error_diffusion;
                }
                if (isset($imagePixels[$x][$y + 2])) {
                    $imagePixels[$x][$y + 2] += $error_diffusion;
                }
            }
        }

        $image->getCore()->drawImage($draw);

        return $image;
    }

    protected function ditherWithGd(Image $image)
    {
        $image->greyscale();

        $size = $image->getSize();
        $imagePixels = [];

        for ($x = 0; $x < $size->width; $x++) {
            for ($y = 0; $y < $size->height; $y++) {
                $color = \imagecolorat($image->getCore(), $x, $y);
                // if (!\imageistruecolor($image->getCore())) {
                //     $color = \imagecolorsforindex($image->getCore(), $color);
                //     $color['alpha'] = \round(1 - $color['alpha'] / 127, 2);
                // }
                $imagePixels[$x][$y] = $color;
            }
        }

        $resource = \imagecreatetruecolor($size->width, $size->height);
        \imagealphablending($resource, false);
        \imagesavealpha($resource, true);
        $white = \imagecolorallocate($resource, 0xff, 0xff, 0xff);
        /* Atkinson Error Diffusion Kernel:

        1/8 is 1/8 * quantization error.

        +-------+-------+-------+-------+
        |       | Curr. |  1/8  |  1/8  |
        +-------|-------|-------|-------|
        |  1/8  |  1/8  |  1/8  |       |
        +-------|-------|-------|-------|
        |       |  1/8  |       |       |
        +-------+-------+-------+-------+

        */

        for ($y = 0; $y < $size->height; $y++) {
            for ($x = 0; $x < $size->width; $x++) {
                $old = $imagePixels[$x][$y];
                if ($old > 0xffffff * .5) {
                    $new = 0xffffff;
                    \imagesetpixel($resource, $x, $y, $white);
                } else {
                    $new = 0x000000;
                }
                $quant_error = $old - $new;
                $error_diffusion = (1 / 8) * $quant_error;
                if (isset($imagePixels[$x + 1][$y])) {
                    $imagePixels[$x + 1][$y] += $error_diffusion;
                }
                if (isset($imagePixels[$x + 2][$y])) {
                    $imagePixels[$x + 2][$y] += $error_diffusion;
                }
                if (isset($imagePixels[$x - 1][$y + 1])) {
                    $imagePixels[$x - 1][$y + 1] += $error_diffusion;
                }
                if (isset($imagePixels[$x][$y + 1])) {
                    $imagePixels[$x][$y + 1] += $error_diffusion;
                }
                if (isset($imagePixels[$x + 1][$y + 1])) {
                    $imagePixels[$x + 1][$y + 1] += $error_diffusion;
                }
                if (isset($imagePixels[$x][$y + 2])) {
                    $imagePixels[$x][$y + 2] += $error_diffusion;
                }
            }
        }

        $image->setCore($resource);

        return $image;
    }
}
