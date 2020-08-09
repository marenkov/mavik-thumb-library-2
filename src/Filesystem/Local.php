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

use Mavik\Thumbnails\Exception\FileSystemException;

/**
 * Operations with file system
 */
class Local implements FileSystem {
    
    public function exists(string $path): bool {
        return file_exists($path);
    }
    
    public function isDirectory(string $path): bool {
        return is_dir($path);
    }
        
    public function isFile(string $path): bool {
        return is_file($path);
    }
    
    public function makeDirectory(string $path, int $mode) {
         if (!mkdir($path, $mode, true)) {
             throw new FileSystemException(sprintf('Can\'t create directory %s with mode %o', $path, $mode));
         }
    }

    public function write(string $path, string $content, int $mode = null) {
        /**
         * @todo Set mode
         */
        file_put_contents($path, $content);
    }

}
