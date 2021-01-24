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
 * Strategy of resizing
 */
abstract class AbstractStrategy
{
    /**
     * Real size of thumbnail
     * 
     * @param Image $originalImage
     * @param int $requestedThumbWidth
     * @param int $requestedThumbHeight
     * @return array ['width' => <width>, 'heigth' => <height>]
     */
    public function size(Image $originalImage, int $requestedThumbWidth, int $requestedThumbHeight): array
    {
        $defaultDimension = $this->getDefaultDimension($originalImage, $requestedThumbWidth, $requestedThumbHeight);
        switch ($defaultDimension) {
            case 'w':
                return [
                    'width' => $requestedThumbWidth,
                    'height' => round($originalImage->height * $requestedThumbWidth / $originalImage->width)
                ];
            case 'h':
                return [                    
                    'width' => round($originalImage->width * $requestedThumbHeight / $originalImage->height),
                    'height' => $requestedThumbHeight
                ];
            default:
                return [
                    'width' => $requestedThumbWidth,
                    'height' => $requestedThumbHeight
                ];
        }
    }

    /**
     * Coordinates and size of area in the original image
     * 
     * @param Image $originalImage
     * @param int $thumbWidth
     * @param int $thumbHeight
     * @return array ['x' => <x>, 'y' => <y>, 'width' => <width>, 'height' => <height>]
     */
    public function area(Image $originalImage, int $thumbWidth, int $thumbHeight): array
    {
        return array('x' =>0, 'y' => 0, 'width' => $originalImage->width, 'height' => $originalImage->height);
    }

    /**
     * Which dimension to use: width or heigth or width and heigth?
     * 
     * @param Image $originalImage
     * @param int $thumbWidth
     * @param int $thumbHeight     
     * @return string w|h|wh
     */
    protected function getDefaultDimension(Image $originalImage, int $thumbWidth, int $thumbHeight): string
    {
        return 'wh';
    }
}
?>
