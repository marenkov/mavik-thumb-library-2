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
 * Adapter for Graphic Library
 */
abstract class GraphicLibrary
{
    /** @var array */
    protected $params = [];

    /**
     * @param array $params
     * @throws GraphicLibraryException
     */
    public function __construct(array $params = []) {
        $this->params = $params;
        $this->checkRequirements();
    }

    /**
     * @param ThumbInfo $info
     * @param int $x
     * @param int $y
     * @param int $widht
     * @param int $height
     */
    public abstract function createThumbnail(ThumbInfo $info, $x, $y, $widht, $height);

    /**
     * @throws GraphicLibraryException
     */
    protected abstract function checkRequirements();
}
