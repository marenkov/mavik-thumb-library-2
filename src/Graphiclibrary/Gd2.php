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
 * Adapter for Graphic Library GD2
 */
class Gd2 extends Graphiclibrary {

    /**
     * @throws GraphicLibraryException
     */
    protected function checkRequirements()
    {
        if (!function_exists('imagecreatetruecolor')) {
            throw new GraphicLibraryException('Library GD2 is not installed', GraphicLibraryException::GRAPHIC_LIBRARY_IS_MISSING);
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
        $orig = $this->createOrigin($info);
        foreach ($info->thumbnails as $ratio => $thumbnail) {
            if ($info->isLess($thumbnail)) {
                $thumb = imagecreatetruecolor($thumbnail->realWidth, $thumbnail->realHeight);
                $colorsCount = $this->processTransparent($orig, $thumb, $info->original, $thumbnail);
                $this->createThumbnailFile($thumb, $orig, $info->original, $thumbnail, $colorsCount, $x, $y, $widht, $height);    
                imagedestroy($thumb);
            } else {
                unset($info->thumbnails[$ratio]);
            }
        }
        imagedestroy($orig);
    }

    /**
     * @param MavikThumbInfo $info
     * @return resource
     * @throws Exception
     */
    private function createOrigin(MavikThumbInfo $info)
    {
        switch ($info->original->type)
        {
            case 'image/jpeg':
                return imagecreatefromjpeg($info->original->path);
            case 'image/png':
                return imagecreatefrompng($info->original->path);
            case 'image/gif':
                return imagecreatefromgif($info->original->path);
            default:
                throw new Exception(JText::sprintf('Unsupported type of image', $info->original->type), MavikThumbGenerator::ERROR_UNSUPPORTED_TYPE);
        }
    }

    /**
     * @param resource $orig
     * @param resource $thumb
     * @param MavikThumbImageInfo $originalInfo
     * @param MavikThumbImageInfo $thumbInfo
     * @return int
     */
    private function processTransparent($orig, $thumb, MavikThumbImageInfo $originalInfo, MavikThumbImageInfo $thumbInfo)
    {
        $colorsCount = imagecolorstotal($orig);
        if ($originalInfo->type == 'image/png' || $originalInfo->type == 'image/gif') {
            $transparentIndex = imagecolortransparent($orig);
            if ($transparentIndex >= 0 && $transparentIndex < $colorsCount)
            {
                // without alpha-chanel
                $transparentRgb = imagecolorsforindex($orig, $transparentIndex);
                if ($colorsCount == 256) {
                    imagecolordeallocate($orig, $transparentIndex);
                }
                $transparentIndex = imagecolorallocate($orig, $transparentRgb['red'], $transparentRgb['green'], $transparentRgb['blue']);
                imagefilledrectangle( $thumb, 0, 0, $thumbInfo->realWidth, $thumbInfo->realHeight, $transparentIndex );
                imagecolortransparent($thumb, $transparentIndex);
            } elseif ($originalInfo->type == 'image/png') {
                // with alpha-chanel
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                imagefilledrectangle($thumb, 0, 0, $thumbInfo->realWidth, $thumbInfo->realHeight, $transparent);
            }
        }

        return $colorsCount;
    }

    /**
     * @param resource $thumb
     * @param resource $orig
     * @param MavikThumbImageInfo $origInfo
     * @param MavikThumbImageInfo $thumbInfo
     * @param int $colorsCount
     * @param int $x
     * @param int $y
     * @param int $widht
     * @param int $height
     * @throws Exception
     */
    private function createThumbnailFile($thumb, $orig, MavikThumbImageInfo $origInfo, MavikThumbImageInfo $thumbInfo, $colorsCount, $x, $y, $widht, $height)
    {
        imagecopyresampled($thumb, $orig, 0, 0, $x, $y, $thumbInfo->realWidth, $thumbInfo->realHeight, $widht, $height);
        if ($colorsCount) {
            imagetruecolortopalette($thumb, false, $colorsCount);
        }
        ob_start();
        switch ($origInfo->type)
        {
            case 'image/jpeg':
                    $result = imagejpeg($thumb, null, $this->quality);
                    break;
            case 'image/png':
                    $result = imagepng($thumb, null, 9);
                    break;
            case 'image/gif':
                    $result = imagegif($thumb, null);
        }
        JFile::write($thumbInfo->path, ob_get_contents());
        ob_end_clean();

        if(!$result) {
            throw new Exception(JText::sprintf('Can\'t create thumbnail for', $origInfo->path), MavikThumbGenerator::ERROR_FILE_CREATION);
        }
    }
}