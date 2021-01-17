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
 * Resize strategy Fill
 */
class Fill extends AbstractStrategy {

    public function area(Image $originalImage, int $thumbWidth, int $thumbHeight): array
    {
        if ($originalImage->width / $originalImage->height < $thumbWidth / $thumbHeight) {
                $x = 0;
                $widht = $originalImage->width;
                $height = $originalImage->width *  $thumbHeight / $thumbWidth;
                $y = ($originalImage->height - $height) / 2;
        } else {
                $y = 0;
                $height = $originalImage->height;
                $widht = $originalImage->height *  $thumbWidth/$thumbHeight;
                $x = ($originalImage->width - $widht) / 2;
        }
        return array($x, $y, $widht, $height);
    }    
}

