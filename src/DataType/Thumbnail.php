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

use Mavik\Thumbnails\DataType\Image;

/**
 *  Information about thumbnail
 */
class Thumbnail extends Image {

    /** @var int */
    public $viewWidth;

    /** @var int */
    public $viewHeight;

    /** @var bool */
    public $isExist;
    
    /** @var float */
    public $ratio;
}
