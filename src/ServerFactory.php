<?php

namespace HelloNico\ImageFactory;

use HelloNico\ImageFactory\Manipulators\Filter;
use League\Glide\Manipulators\BaseManipulator;
use League\Glide\Manipulators\Filter as GlideFilter;
use League\Glide\ServerFactory as GlideServerFactory;

class ServerFactory extends GlideServerFactory
{
    public function getManipulators()
    {
        return \array_map(function (BaseManipulator $manipulator) {
            return \get_class($manipulator) === GlideFilter::class
                ? new Filter()
                : $manipulator;
        }, parent::getManipulators());
    }

    public static function create(array $config = [])
    {
        return (new static($config))->getServer();
    }
}
