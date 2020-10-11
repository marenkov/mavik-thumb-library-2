<?php

/**
 * Library for creating thumbnails of images
 *
 * @package Mavik Thumbnails
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2012-2020 Vitalii Marenkov
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mavik\Thumbnails\DataType;

/**
 *  Information about image
 */
class Image {

    /** @var string */
    public $url;

    /** @var string */
    public $path;

    /** @var int */
    public $width;

    /** @var int */
    public $height;

    /** @var int constant IMG_XXX */
    public $type;

    /** @var bool */
    public $isLocal;

    /** @var int */
    public $fileSize;
}
