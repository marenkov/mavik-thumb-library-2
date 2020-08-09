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
 *  Information about image
 */
class ImageInfo {

    /** @var string */
    var $url = null;

    /** @var string */
    var $path = null;

    /** @var int */
    var $width = null;

    /** @var int */
    var $height = null;

    /** @var int */
    var $size = null;

    var $type = null;

    var $local = null;

    /**
     * Real width. Only for thumbnail.
     * 
     * @var int 
     */
    var $realWidth = null;

    /**
     * Real height. Only for thumbnail.
     * 
     * @var int 
     */    
    var $realHeight = null;
}
