<?php

/**
 * Library for creating thumbnails of images
 *
 * @package Mavik Thumbnails
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2012-2020 Vitalii Marenkov
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mavik\Thumbnails;

/**
 * Strategy of resizing
 */
abstract class ResizeType
{

    /**
     * Set thumnail size
     * 
     * @param ThumbInfo $info
     * @param int $width
     * @param int $height
     */
    public function setSize(ThumbInfo $info, $width, $height)
    {
        $defaultDimension = $this->getDefaultDimension($info, $width, $height);
        switch ($defaultDimension) {
            case 'w':
                $info->thumbnail->width  = $width;
                $info->thumbnail->height = round($info->original->height * $width
                    / $info->original->width);
                break;
            case 'h':
                $info->thumbnail->height = $height;
                $info->thumbnail->width  = round($info->original->width * $height
                    / $info->original->height);
                break;
            default:
                $info->thumbnail->width  = $width;
                $info->thumbnail->height = $height;
        }
    }

    /**
     * Coordinates and size of area in the original image
     * 
     * @return array
     */
    public function getArea(MavikThumbInfo $info)
    {
        return array(0, 0, $info->original->width, $info->original->height);
    }

    /**
     * Which dimension to use: width or heigth or width and heigth?
     * 
     * @return string
     */
    protected function getDefaultDimension(MavikThumbInfo $info, $width, $height)
    {
        return 'wh';
    }
}
?>
