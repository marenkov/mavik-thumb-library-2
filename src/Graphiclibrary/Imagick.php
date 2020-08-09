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
 * Adapter for Image Magic
 */
class Imagick extends Graphiclibrary {

    /**
     * @throws GraphicLibraryException
     */
    protected function checkRequirements()
    {
        if (!class_exists('Imagick')) {
            throw new GraphicLibraryException('Library ImageMagic is not installed', GraphicLibraryException::GRAPHIC_LIBRARY_IS_MISSING);
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
        $imagik = new Imagick($info->original->path);
        $imagik->cropimage($widht, $height, $x, $y);
        
        foreach ($info->thumbnails as $ratio => $thumbnail) {
            if ($info->isLess($thumbnail)) {
                $currentImagik = clone $imagik;
                $currentImagik->thumbnailimage($thumbnail->realWidth, $thumbnail->realHeight);
                ob_start();
                echo $currentImagik;
                JFile::write($thumbnail->path, ob_get_contents());
                ob_end_clean();
            } else {
                unset($info->thumbnails[$ratio]);
            }
        }
    }
}