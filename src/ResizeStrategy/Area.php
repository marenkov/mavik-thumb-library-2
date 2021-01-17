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
use Mavik\Thumbnails\ResizeStrategy\FitStrategy as AlternativeStrategy;

/**
 * Resize strategy Area
 */
class Area extends AbstractStrategy {
    
    /**
     * It is used if requested width or height is not defined 
     * 
     * @var AbstractStrategy
     */ 
    private $alternativeStrategy;

    public function __construct()
    {
        $this->alternativeStrategy = new AlternativeStrategy();
    }

    public function size(Image $originalImage, int $requestedThumbWidth, int $requestedThumbHeight): array
    {
        if (!$requestedThumbWidth || !$requestedThumbHeight) {
            return $this->alternativeStrategy->size($originalImage, $requestedThumbWidth, $requestedThumbHeight);
        }

        $thumbArea = $requestedThumbWidth * $requestedThumbHeight;
        $originArea = $originalImage->width * $originalImage->height;
        $ratio = sqrt($originArea/$thumbArea);
        return [
            'width' => round($originalImage->width / $ratio),
            'height' => round($originalImage->height / $ratio)
        ];
    }    
}
