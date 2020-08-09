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
 * Operations with file system
 */
interface FileSystem {
    public function exists(string $path): bool;
    public function isDirectory(string $path): bool;
    public function isFile(string $path): bool;
    public function makeDirectory(string $path, int $mode = null);
    public function write(string $path, string $content, int $mode = null);
}
