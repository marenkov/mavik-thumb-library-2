<?php

/**
 * Library for creating thumbnails of images
 *
 * @package Mavik Thumbnails
 * @author Vitalii Marenkov <admin@mavik.com.ua>
 * @copyright 2012-2020 Vitalii Marenkov
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Mavik\Thumbnails\Graphiclibrary;

use Mavik\Thumbnails\Graphiclibrary;
use Mavik\Thumbnails\Exception\GraphicLibraryException;

/**
 * Adapter for Graphic Magic
 */
class Gmagick extends Graphiclibrary {

    /**
     * @throws GraphicLibraryException
     */
    protected function checkRequirements()
    {
        if (!class_exists('Gmagick')) {
            throw new GraphicLibraryException('Library Gmagick is not installed', GraphicLibraryException::GRAPHIC_LIBRARY_IS_MISSING);
        }
    }

    /**
     * @param MavikThumbInfo $info
     * @param int $x
     * @param int $y
     * @param int $widht
     * @param int $height
     */
    public function createThumbnail(MavikThumbInfo $info, $x, $y, $widht, $height)
    {
        $gmagik = new Gmagick($info->original->path);
        $gmagik->cropimage($widht, $height, $x, $y);
        
        foreach ($info->thumbnails as $ratio => $thumbnail) {
            if ($info->isLess($thumbnail)) {
                $currentGmagik = clone $gmagik;
                $currentGmagik->resizeimage($thumbnail->realWidth, $thumbnail->realHeight, null, 1);
                ob_start();
                echo $currentGmagik;
                JFile::write($thumbnail->path, ob_get_contents());
                ob_end_clean();
            } else {
                unset($info->thumbnails[$ratio]);
            }
        }        
    }
}