<?php

namespace HelloNico\ImageFactory\Manipulators;

use HelloNico\ImageFactory\Manipulations;
use Intervention\Image\Image;
use League\Glide\Manipulators\Filter as ManipulatorsFilter;

/**
 * @property string $filt
 */
class Filter extends ManipulatorsFilter
{
    public function run(Image $image)
    {
        if ($this->filt === Manipulations::FILTER_DITHERING) {
            return $this->runDitheringFilter($image);
        }

        return parent::run($image);
    }

    protected function runDitheringFilter(Image $image)
    {
        return $this->ditherWithGd($image);
    }

    protected function ditherWithGd(Image $image)
    {
        $image->greyscale();

        $size = $image->getSize();
        $imagePixels = [];

        for ($x = 0; $x < $size->width; $x++) {
            for ($y = 0; $y < $size->height; $y++) {
                $imagePixels[$x][$y] = \imagecolorat($image->getCore(), $x, $y);
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
                if ($old > 0xffffff * .5) { // This is the b/w threshold. Currently @ halfway between white and black.
                    $new = 0xffffff;
                    \imagesetpixel($resource, $x, $y, $white); // Only setting white pixels, because the image is already black.
                } else {
                    $new = 0x000000;
                }
                $quant_error = $old - $new;
                $error_diffusion = (1 / 8) * $quant_error; // I can do this because this dither uses 1 value for the applied error diffusion.
                // dithering here.
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

        // set new resource
        $image->setCore($resource);

        return $image;
    }
}
