<?php

/**
 * Library for creating thumbnails of images
 *
 * @package Mavik Thumbnails
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2012-2020 Vitalii Marenkov
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mavik\Thumbnails\ResizeStrategy;

use Mavik\Thumbnails\DataType\Image;

/**
 * Resize strategy Fit
 */
class Fit extends AbstractStrategy {

    protected function getDefaultDimension(Image $originalImage, int $thumbWidth, int $thumbHeight): string
    {
        if (!$thumbHeight || $originalImage->width / $thumbWidth > $originalImage->height / $thumbHeight) {
            return 'w';
        } else {
            return 'h';
        }
    } 
}
