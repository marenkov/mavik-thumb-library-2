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
 * Information about original image and thumbnails
 *
 * For one image can be created a few thumbnails - one for every ratio.
 * Example: You set size of thumbnail 100x200 and ratios 1, 2, 3.
 * Will be created 3 thumbnails with sizes: 100x200, 200x400, 300x600
 * (if it is not bigger than original image).
 */
class ImageWithThumbnails {
   
    /**
     * Info about original image
     * 
     * @var Image
     */
    public $original;
    
    /**
     * Info about thumbnails
     * 
     * @var Thumbnail[]
     */    
    public $thumbnails = [];
        
    public function allThumbnailsExist(): bool
    {
        foreach ($this->thumbnails as $thumbnail) {
            if (!$thumbnail->isExist) {
                return false;
            }
        }
        return true;
    }
}